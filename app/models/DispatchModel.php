<?php
namespace app\models;

use PDO;

class DispatchModel {
    private $app;
    private $db;

    public function __construct($app) {
        $this->app = $app;
        $this->db = $app->db();
    }

    /**
     * Lance le dispatch selon la stratégie choisie
     * @param string $strategie 'chronologique' | 'besoin' | 'proportion'
     */
    public function lancerDispatch(string $strategie = 'besoin'): array {
        $this->db->beginTransaction();
        try {
            $resultats = [];
            $dons = $this->getDonsNonDispatches();

            if ($strategie === 'proportion') {
                $resultats = $this->dispatchProportionnel($dons);
            } else {
                $resultats = $this->dispatchSequentiel($dons, $strategie);
            }

            $this->db->commit();
            return $resultats;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Dispatch séquentiel (chronologique ou par besoin)
     */
    private function dispatchSequentiel(array $dons, string $strategie): array {
        $resultats = [];
        foreach ($dons as $don) {
            $quantiteRestante = $don['quantite'] - $don['quantite_deja_dispatchee'];
            if ($quantiteRestante <= 0) continue;

            $villeBesoins = $this->getVilleBesoinsNonSatisfaits($don['id_besoin'], $strategie);

            foreach ($villeBesoins as $vb) {
                if ($quantiteRestante <= 0) break;

                $besoinRestant = $vb['quantite'] - $vb['quantite_deja_recue'];
                if ($besoinRestant <= 0) continue;

                $attribution = min($quantiteRestante, $besoinRestant);
                $this->insererDispatch($don['id'], $vb['id_ville_besoin'], $attribution);
                $this->deduireQuantiteDon($don['id'], $attribution);
                $quantiteRestante -= $attribution;

                $resultats[] = [
                    'don_id' => $don['id'],
                    'donateur' => $don['donateur'],
                    'ville' => $vb['nom_ville'],
                    'besoin' => $vb['nom_besoin'],
                    'quantite' => $attribution
                ];
            }
        }
        return $resultats;
    }

    /**
     * Dispatch proportionnel :
     * 1. Coefficient = total dons / total besoins
     * 2. Pour chaque ville_besoin : coeff × besoin, arrondi à l'inférieur
     * 3. Si somme < total dons, arrondir au supérieur celui avec le plus grand décimal
     */
    private function dispatchProportionnel(array $dons): array {
        $resultats = [];

        // Regrouper les dons par id_besoin
        $donsByBesoin = [];
        foreach ($dons as $don) {
            $qte = $don['quantite'] - $don['quantite_deja_dispatchee'];
            if ($qte <= 0) continue;
            if (!isset($donsByBesoin[$don['id_besoin']])) {
                $donsByBesoin[$don['id_besoin']] = ['total' => 0, 'dons' => []];
            }
            $donsByBesoin[$don['id_besoin']]['total'] += $qte;
            $donsByBesoin[$don['id_besoin']]['dons'][] = array_merge($don, ['qte_dispo' => $qte]);
        }

        foreach ($donsByBesoin as $idBesoin => $group) {
            $totalDons = $group['total'];
            $villeBesoins = $this->getVilleBesoinsNonSatisfaits($idBesoin, 'besoin');
            if (empty($villeBesoins)) continue;

            // Besoins restants par ville
            $besoinsRestants = [];
            $totalBesoinRestant = 0;
            foreach ($villeBesoins as $vb) {
                $reste = $vb['quantite'] - $vb['quantite_deja_recue'];
                if ($reste > 0) {
                    $besoinsRestants[] = array_merge($vb, ['reste' => $reste]);
                    $totalBesoinRestant += $reste;
                }
            }
            if ($totalBesoinRestant <= 0) continue;

            // Coefficient = total dons / total besoins
            $coeff = $totalDons / $totalBesoinRestant;

            // Calcul des attributions : coeff × besoin, floor
            $attributions = [];
            foreach ($besoinsRestants as $i => $vb) {
                $exact = $coeff * $vb['reste'];
                $floored = (int) floor($exact);
                $decimal = $exact - $floored;
                // Ne pas dépasser le besoin restant
                $floored = min($floored, $vb['reste']);
                $attributions[] = [
                    'vb' => $vb,
                    'exact' => $exact,
                    'attribution' => $floored,
                    'decimal' => $decimal
                ];
            }

            // Si la somme des floor < totalDons, arrondir au supérieur celui avec le plus grand décimal
            $somme = array_sum(array_column($attributions, 'attribution'));
            $diff = $totalDons - $somme;
            if ($diff > 0) {
                // Trier par décimal décroissant
                $indices = range(0, count($attributions) - 1);
                usort($indices, function ($a, $b) use ($attributions) {
                    return $attributions[$b]['decimal'] <=> $attributions[$a]['decimal'];
                });
                foreach ($indices as $idx) {
                    if ($diff <= 0) break;
                    $maxAdd = $attributions[$idx]['vb']['reste'] - $attributions[$idx]['attribution'];
                    $add = min(1, $maxAdd);
                    if ($add > 0) {
                        $attributions[$idx]['attribution'] += $add;
                        $diff -= $add;
                    }
                }
            }

            // Appliquer les attributions en consommant les dons FIFO
            $donsDispos = $group['dons'];
            $donIdx = 0;
            $donRestant = $donsDispos[0]['qte_dispo'];

            foreach ($attributions as $attr) {
                $aDistribuer = $attr['attribution'];
                if ($aDistribuer <= 0) continue;

                $totalAttribue = 0;
                while ($aDistribuer > 0 && $donIdx < count($donsDispos)) {
                    $portion = min($aDistribuer, $donRestant);
                    if ($portion <= 0) break;

                    $this->insererDispatch($donsDispos[$donIdx]['id'], $attr['vb']['id_ville_besoin'], $portion);
                    $this->deduireQuantiteDon($donsDispos[$donIdx]['id'], $portion);

                    $aDistribuer -= $portion;
                    $donRestant -= $portion;
                    $totalAttribue += $portion;

                    if ($donRestant <= 0) {
                        $donIdx++;
                        if ($donIdx < count($donsDispos)) {
                            $donRestant = $donsDispos[$donIdx]['qte_dispo'];
                        }
                    }
                }

                if ($totalAttribue > 0) {
                    $resultats[] = [
                        'don_id' => $donsDispos[max(0, $donIdx - ($donRestant > 0 ? 0 : 1))]['id'] ?? $donsDispos[$donIdx - 1]['id'],
                        'donateur' => 'Proportionnel',
                        'ville' => $attr['vb']['nom_ville'],
                        'besoin' => $attr['vb']['nom_besoin'],
                        'quantite' => $totalAttribue
                    ];
                }
            }
        }
        return $resultats;
    }

    /**
     * Récupère les dons avec la quantité déjà dispatchée
     */
    private function getDonsNonDispatches(): array {
        $sql = "SELECT 
                    d.id, d.donateur, d.id_besoin, d.quantite, d.date_don,
                    0 AS quantite_deja_dispatchee
                FROM don d
                WHERE d.quantite > 0
                ORDER BY d.date_don ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    
    
     
    /**
     * Récupère les besoins des villes non satisfaits
     * @param string $strategie 'chronologique' = date_saisie ASC, 'besoin' = quantite ASC
     */
    private function getVilleBesoinsNonSatisfaits(int $idBesoin, string $strategie = 'besoin'): array {
        $orderBy = $strategie === 'chronologique' ? 'vb.date_saisie ASC' : 'vb.quantite ASC';
        $sql = "SELECT 
                    vb.id_ville_besoin, vb.id_ville, vb.id_besoin, vb.quantite, vb.date_saisie,
                    v.nom_ville,
                    b.nom_besoin,
                    COALESCE(disp.qte, 0) AS quantite_deja_recue
                FROM ville_besoin vb
                JOIN ville v ON vb.id_ville = v.id_ville
                JOIN besoin b ON vb.id_besoin = b.id_besoin
                LEFT JOIN (
                    SELECT id_ville_besoin, SUM(quantite_attribuee) AS qte FROM dispatch GROUP BY id_ville_besoin
                ) disp ON disp.id_ville_besoin = vb.id_ville_besoin
                WHERE vb.id_besoin = :id_besoin
                  AND vb.quantite > COALESCE(disp.qte, 0)
                ORDER BY $orderBy";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_besoin', $idBesoin, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

  
    private function insererDispatch(int $idDon, int $idVilleBesoin, int $quantite): bool {
        $sql = "INSERT INTO dispatch (id_don, id_ville_besoin, quantite_attribuee) VALUES (:id_don, :id_ville_besoin, :quantite)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_don', $idDon, PDO::PARAM_INT);
        $stmt->bindValue(':id_ville_besoin', $idVilleBesoin, PDO::PARAM_INT);
        $stmt->bindValue(':quantite', $quantite, PDO::PARAM_INT);
        return $stmt->execute();
    }

  
    private function deduireQuantiteDon(int $idDon, int $quantite): bool {
        $sql = "UPDATE don SET quantite = quantite - :qte WHERE id = :id AND quantite >= :qte2";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':qte', $quantite, PDO::PARAM_INT);
        $stmt->bindValue(':id', $idDon, PDO::PARAM_INT);
        $stmt->bindValue(':qte2', $quantite, PDO::PARAM_INT);
        return $stmt->execute();
    }

    
    public function resetDispatch(): bool {
        $this->db->beginTransaction();
        try {
         
            $sql = "SELECT id_don, SUM(quantite_attribuee) AS total_attribue FROM dispatch GROUP BY id_don";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $dispatches = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($dispatches as $d) {
                $sqlRestore = "UPDATE don SET quantite = quantite + :qte WHERE id = :id";
                $stmtRestore = $this->db->prepare($sqlRestore);
                $stmtRestore->bindValue(':qte', (int) $d['total_attribue'], PDO::PARAM_INT);
                $stmtRestore->bindValue(':id', (int) $d['id_don'], PDO::PARAM_INT);
                $stmtRestore->execute();
            }

           
            $sqlDelete = "DELETE FROM dispatch";
            $stmtDelete = $this->db->prepare($sqlDelete);
            $stmtDelete->execute();

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getHistorique(): array {
        $sql = "SELECT 
                    d.id AS id_don,
                    d.date_don,
                    d.donateur,
                    b.nom_besoin,
                    t.nom_type_besoin,
                    dp.quantite_attribuee,
                    dp.quantite_attribuee * b.prix_unitaire AS valeur,
                    v.nom_ville,
                    dp.date_dispatch
                FROM dispatch dp
                JOIN don d ON dp.id_don = d.id
                JOIN ville_besoin vb ON dp.id_ville_besoin = vb.id_ville_besoin
                JOIN besoin b ON d.id_besoin = b.id_besoin
                JOIN type_besoin t ON b.id_type_besoin = t.id_type_besoin
                JOIN ville v ON vb.id_ville = v.id_ville
                ORDER BY d.date_don ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public function getResumeParVille(): array {
        $sql = "SELECT 
                    v.id_ville,
                    v.nom_ville,
                    COUNT(dp.id_dispatch) AS nb_attributions,
                    COALESCE(SUM(dp.quantite_attribuee * b.prix_unitaire), 0) AS total_recu,
                    total_besoins.total AS total_besoins
                FROM ville v
                LEFT JOIN ville_besoin vb ON v.id_ville = vb.id_ville
                LEFT JOIN besoin b ON vb.id_besoin = b.id_besoin
                LEFT JOIN dispatch dp ON dp.id_ville_besoin = vb.id_ville_besoin
                LEFT JOIN (
                    SELECT vb2.id_ville, SUM(vb2.quantite * b2.prix_unitaire) AS total
                    FROM ville_besoin vb2
                    JOIN besoin b2 ON vb2.id_besoin = b2.id_besoin
                    GROUP BY vb2.id_ville
                ) total_besoins ON total_besoins.id_ville = v.id_ville
                GROUP BY v.id_ville, v.nom_ville, total_besoins.total
                ORDER BY v.nom_ville";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public function getTotalDispatche(): array {
        $sql = "SELECT 
                    COALESCE(SUM(dp.quantite_attribuee * b.prix_unitaire), 0) AS total_valeur,
                    COUNT(dp.id_dispatch) AS nb_attributions
                FROM dispatch dp
                JOIN don d ON dp.id_don = d.id
                JOIN besoin b ON d.id_besoin = b.id_besoin";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
