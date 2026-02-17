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
     * Lance le dispatch automatique :
     * - Parcourt les dons par ordre de date_don (FIFO)
     * - Pour chaque don, cherche les ville_besoin correspondants (même id_besoin)
     *   triés par date_saisie (priorité au besoin le plus ancien)
     * - Attribue la quantité disponible en respectant le besoin restant de chaque ville
     */
    public function lancerDispatch(): array {
        $this->db->beginTransaction();
        try {
            $resultats = [];

            // 1. Récupérer tous les dons non encore totalement dispatchés, par ordre chronologique
            $dons = $this->getDonsNonDispatches();

            foreach ($dons as $don) {
                $quantiteRestante = $don['quantite'] - $don['quantite_deja_dispatchee'];
                if ($quantiteRestante <= 0) continue;

                // 2. Chercher les besoins des villes correspondant à ce besoin (même id_besoin)
                $villeBesoins = $this->getVilleBesoinsNonSatisfaits($don['id_besoin']);

                foreach ($villeBesoins as $vb) {
                    if ($quantiteRestante <= 0) break;

                    $besoinRestant = $vb['quantite'] - $vb['quantite_deja_recue'];
                    if ($besoinRestant <= 0) continue;

                    // Attribution = min(quantité dispo du don, besoin restant de la ville)
                    $attribution = min($quantiteRestante, $besoinRestant);

                    // 3. Insérer dans la table dispatch
                    $this->insererDispatch($don['id'], $vb['id_ville_besoin'], $attribution);

                    // 4. Déduire la quantité du don (transaction)
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

            $this->db->commit();
            return $resultats;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
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
     * Récupère les besoins des villes non satisfaits pour un besoin donné
     * Triés par date_saisie (priorité au plus ancien)
     */
    private function getVilleBesoinsNonSatisfaits(int $idBesoin): array {
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
                ORDER BY vb.date_saisie ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_besoin', $idBesoin, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Insère une attribution dans la table dispatch
     */
    private function insererDispatch(int $idDon, int $idVilleBesoin, int $quantite): bool {
        $sql = "INSERT INTO dispatch (id_don, id_ville_besoin, quantite_attribuee) VALUES (:id_don, :id_ville_besoin, :quantite)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_don', $idDon, PDO::PARAM_INT);
        $stmt->bindValue(':id_ville_besoin', $idVilleBesoin, PDO::PARAM_INT);
        $stmt->bindValue(':quantite', $quantite, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Déduit la quantité d'un don après dispatch
     */
    private function deduireQuantiteDon(int $idDon, int $quantite): bool {
        $sql = "UPDATE don SET quantite = quantite - :qte WHERE id = :id AND quantite >= :qte2";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':qte', $quantite, PDO::PARAM_INT);
        $stmt->bindValue(':id', $idDon, PDO::PARAM_INT);
        $stmt->bindValue(':qte2', $quantite, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Réinitialise le dispatch : restaure les quantités des dons puis supprime les attributions
     */
    public function resetDispatch(): bool {
        $this->db->beginTransaction();
        try {
            // Restaurer les quantités des dons depuis les dispatches
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

            // Supprimer les dispatches
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

    /**
     * Récupère l'historique complet des dispatches
     */
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

    /**
     * Récupère le résumé par ville
     */
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

    /**
     * Total général dispatché
     */
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
