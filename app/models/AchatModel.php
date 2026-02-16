<?php
namespace app\models;

use PDO;

class AchatModel {
    private $app;
    private $db;

    public function __construct($app) {
        $this->app = $app;
        $this->db = $app->db();
    }

    /**
     * Récupère le % de frais d'achat configurable
     */
    public function getFraisPourcent(): float {
        return (float) ($this->app->get('frais_achat_pourcent') ?? 0);
    }

    /**
     * Récupère le montant total des dons en argent et l'argent disponible
     * Ne compte que les achats validés (simulation=0) dans les dépenses
     */
    public function getArgentDisponible(): array {
        $sql = "SELECT COALESCE(SUM(d.quantite * b.prix_unitaire), 0) AS total_argent_dons
                FROM don d
                JOIN besoin b ON d.id_besoin = b.id_besoin
                JOIN type_besoin t ON b.id_type_besoin = t.id_type_besoin
                WHERE t.nom_type_besoin = 'Argent'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $totalDons = (float) ($stmt->fetch(PDO::FETCH_ASSOC)['total_argent_dons'] ?? 0);

        $sql2 = "SELECT COALESCE(SUM(a.quantite * a.prix_unitaire + a.montant_frais), 0) AS total_depense
                 FROM achat a WHERE a.simulation = 0";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->execute();
        $totalDepense = (float) ($stmt2->fetch(PDO::FETCH_ASSOC)['total_depense'] ?? 0);

        return [
            'total_argent_dons' => $totalDons,
            'total_depense' => $totalDepense,
            'argent_disponible' => $totalDons - $totalDepense,
            'frais_pourcent' => $this->getFraisPourcent()
        ];
    }

    /**
     * Vérifie si un besoin donné a encore des dons en nature non dispatchés
     */
    public function besoinEncoreDansDonsRestants(int $idBesoin): bool {
        $sql = "SELECT 
                    COALESCE(SUM(d.quantite), 0) AS total_donne,
                    COALESCE((SELECT SUM(dp.quantite_attribuee) FROM dispatch dp 
                              JOIN don d2 ON dp.id_don = d2.id WHERE d2.id_besoin = :id_besoin2), 0) AS total_dispatche
                FROM don d
                JOIN besoin b ON d.id_besoin = b.id_besoin
                JOIN type_besoin t ON b.id_type_besoin = t.id_type_besoin
                WHERE d.id_besoin = :id_besoin
                  AND t.nom_type_besoin != 'Argent'";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_besoin', $idBesoin, PDO::PARAM_INT);
        $stmt->bindValue(':id_besoin2', $idBesoin, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $restant = ($row['total_donne'] ?? 0) - ($row['total_dispatche'] ?? 0);
        return $restant > 0;
    }

    /**
     * Récupère les besoins restants (non satisfaits) achetables
     * Filtre uniquement Nature et Matériaux
     */
    public function getBesoinsRestants(?int $idVille = null): array {
        $sql = "SELECT 
                    vb.id_ville_besoin,
                    v.id_ville,
                    v.nom_ville,
                    b.id_besoin,
                    b.nom_besoin,
                    t.nom_type_besoin,
                    b.prix_unitaire,
                    vb.quantite AS quantite_demandee,
                    COALESCE(dispatch_recu.qte, 0) + COALESCE(achat_valide.qte, 0) AS quantite_satisfaite,
                    vb.quantite - COALESCE(dispatch_recu.qte, 0) - COALESCE(achat_valide.qte, 0) AS quantite_manquante,
                    (vb.quantite - COALESCE(dispatch_recu.qte, 0) - COALESCE(achat_valide.qte, 0)) * b.prix_unitaire AS cout_achat
                FROM ville_besoin vb
                JOIN ville v ON vb.id_ville = v.id_ville
                JOIN besoin b ON vb.id_besoin = b.id_besoin
                JOIN type_besoin t ON b.id_type_besoin = t.id_type_besoin
                LEFT JOIN (
                    SELECT dp.id_ville_besoin, SUM(dp.quantite_attribuee) AS qte
                    FROM dispatch dp GROUP BY dp.id_ville_besoin
                ) dispatch_recu ON dispatch_recu.id_ville_besoin = vb.id_ville_besoin
                LEFT JOIN (
                    SELECT a.id_ville_besoin, SUM(a.quantite) AS qte
                    FROM achat a WHERE a.simulation = 0 GROUP BY a.id_ville_besoin
                ) achat_valide ON achat_valide.id_ville_besoin = vb.id_ville_besoin
                WHERE t.nom_type_besoin IN ('Nature', 'Materiaux')
                  AND (vb.quantite - COALESCE(dispatch_recu.qte, 0) - COALESCE(achat_valide.qte, 0)) > 0";
        
        if ($idVille !== null) {
            $sql .= " AND v.id_ville = :id_ville";
        }
        $sql .= " ORDER BY v.nom_ville, cout_achat ASC";

        $stmt = $this->db->prepare($sql);
        if ($idVille !== null) {
            $stmt->bindValue(':id_ville', $idVille, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Effectue un achat (ou simulation) avec frais
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function acheter(int $idVilleBesoin, int $quantite, bool $simulation = false): array {
        $sql = "SELECT b.id_besoin, b.prix_unitaire, t.nom_type_besoin
                FROM ville_besoin vb
                JOIN besoin b ON vb.id_besoin = b.id_besoin
                JOIN type_besoin t ON b.id_type_besoin = t.id_type_besoin
                WHERE vb.id_ville_besoin = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $idVilleBesoin, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return ['success' => false, 'error' => 'Besoin introuvable.'];
        }

        if (!in_array($row['nom_type_besoin'], ['Nature', 'Materiaux'])) {
            return ['success' => false, 'error' => 'On ne peut acheter que des besoins de type Nature ou Matériaux.'];
        }

        // Vérifier si le besoin existe encore dans les dons restants
        if ($this->besoinEncoreDansDonsRestants($row['id_besoin'])) {
            return ['success' => false, 'error' => 'Ce besoin a encore des dons en nature non dispatchés. Effectuez d\'abord le dispatch avant d\'acheter.'];
        }

        // Calcul avec frais
        $fraisPourcent = $this->getFraisPourcent();
        $prixUnitaire = (float) $row['prix_unitaire'];
        $coutBase = $quantite * $prixUnitaire;
        $montantFrais = round($coutBase * ($fraisPourcent / 100), 2);
        $coutTotal = $coutBase + $montantFrais;

        if (!$simulation) {
            $argent = $this->getArgentDisponible();
            if ($coutTotal > $argent['argent_disponible']) {
                return ['success' => false, 'error' => "Fonds insuffisants. Coût: " . number_format($coutTotal, 0, ',', ' ') . " Ar (dont " . number_format($montantFrais, 0, ',', ' ') . " Ar de frais). Disponible: " . number_format($argent['argent_disponible'], 0, ',', ' ') . " Ar."];
            }
        }

        $sql2 = "INSERT INTO achat (id_ville_besoin, quantite, prix_unitaire, frais_pourcent, montant_frais, simulation)
                 VALUES (:id_vb, :qte, :pu, :fp, :mf, :sim)";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->bindValue(':id_vb', $idVilleBesoin, PDO::PARAM_INT);
        $stmt2->bindValue(':qte', $quantite, PDO::PARAM_INT);
        $stmt2->bindValue(':pu', $prixUnitaire);
        $stmt2->bindValue(':fp', $fraisPourcent);
        $stmt2->bindValue(':mf', $montantFrais);
        $stmt2->bindValue(':sim', $simulation ? 1 : 0, PDO::PARAM_INT);
        $stmt2->execute();

        return ['success' => true, 'error' => null, 'cout_total' => $coutTotal, 'montant_frais' => $montantFrais];
    }

    /**
     * Valide toutes les simulations
     */
    public function validerSimulations(): array {
        $sql = "SELECT COALESCE(SUM(a.quantite * a.prix_unitaire + a.montant_frais), 0) AS total
                FROM achat a WHERE a.simulation = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $totalSim = (float) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        $argent = $this->getArgentDisponible();
        if ($totalSim > $argent['argent_disponible']) {
            return ['success' => false, 'error' => "Fonds insuffisants pour valider. Coût: " . number_format($totalSim, 0, ',', ' ') . " Ar. Disponible: " . number_format($argent['argent_disponible'], 0, ',', ' ') . " Ar."];
        }

        $sql2 = "UPDATE achat SET simulation = 0 WHERE simulation = 1";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->execute();

        return ['success' => true, 'error' => null, 'nb_valides' => $stmt2->rowCount()];
    }

    /**
     * Supprime toutes les simulations
     */
    public function supprimerSimulations(): bool {
        $sql = "DELETE FROM achat WHERE simulation = 1";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }

    /**
     * Historique des achats validés, filtrable par ville
     */
    public function getHistoriqueAchats(?int $idVille = null): array {
        $sql = "SELECT 
                    a.id_achat, v.id_ville, v.nom_ville,
                    b.nom_besoin, t.nom_type_besoin,
                    a.quantite, a.prix_unitaire,
                    a.quantite * a.prix_unitaire AS montant_base,
                    a.frais_pourcent, a.montant_frais,
                    a.quantite * a.prix_unitaire + a.montant_frais AS montant_total,
                    a.simulation, a.date_achat
                FROM achat a
                JOIN ville_besoin vb ON a.id_ville_besoin = vb.id_ville_besoin
                JOIN ville v ON vb.id_ville = v.id_ville
                JOIN besoin b ON vb.id_besoin = b.id_besoin
                JOIN type_besoin t ON b.id_type_besoin = t.id_type_besoin
                WHERE a.simulation = 0";
        if ($idVille !== null) {
            $sql .= " AND v.id_ville = :id_ville";
        }
        $sql .= " ORDER BY a.date_achat DESC";

        $stmt = $this->db->prepare($sql);
        if ($idVille !== null) {
            $stmt->bindValue(':id_ville', $idVille, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Simulations en cours
     */
    public function getSimulations(): array {
        $sql = "SELECT 
                    a.id_achat, v.nom_ville,
                    b.nom_besoin, t.nom_type_besoin,
                    a.quantite, a.prix_unitaire,
                    a.quantite * a.prix_unitaire AS montant_base,
                    a.frais_pourcent, a.montant_frais,
                    a.quantite * a.prix_unitaire + a.montant_frais AS montant_total,
                    a.date_achat
                FROM achat a
                JOIN ville_besoin vb ON a.id_ville_besoin = vb.id_ville_besoin
                JOIN ville v ON vb.id_ville = v.id_ville
                JOIN besoin b ON vb.id_besoin = b.id_besoin
                JOIN type_besoin t ON b.id_type_besoin = t.id_type_besoin
                WHERE a.simulation = 1
                ORDER BY a.date_achat DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récapitulation globale
     */
    public function getRecapitulation(): array {
        // Total besoins (Nature + Matériaux)
        $sql1 = "SELECT COALESCE(SUM(vb.quantite * b.prix_unitaire), 0) AS total
                 FROM ville_besoin vb
                 JOIN besoin b ON vb.id_besoin = b.id_besoin
                 JOIN type_besoin t ON b.id_type_besoin = t.id_type_besoin
                 WHERE t.nom_type_besoin IN ('Nature', 'Materiaux')";
        $stmt1 = $this->db->prepare($sql1);
        $stmt1->execute();
        $totalBesoins = (float) ($stmt1->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        // Satisfait via dispatch
        $sql2 = "SELECT COALESCE(SUM(dp.quantite_attribuee * b.prix_unitaire), 0) AS total
                 FROM dispatch dp
                 JOIN don d ON dp.id_don = d.id
                 JOIN besoin b ON d.id_besoin = b.id_besoin
                 JOIN type_besoin t ON b.id_type_besoin = t.id_type_besoin
                 WHERE t.nom_type_besoin IN ('Nature', 'Materiaux')";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->execute();
        $totalDispatch = (float) ($stmt2->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        // Satisfait via achats validés
        $sql3 = "SELECT COALESCE(SUM(a.quantite * a.prix_unitaire), 0) AS total
                 FROM achat a WHERE a.simulation = 0";
        $stmt3 = $this->db->prepare($sql3);
        $stmt3->execute();
        $totalAchats = (float) ($stmt3->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        // Total frais
        $sql4 = "SELECT COALESCE(SUM(a.montant_frais), 0) AS total
                 FROM achat a WHERE a.simulation = 0";
        $stmt4 = $this->db->prepare($sql4);
        $stmt4->execute();
        $totalFrais = (float) ($stmt4->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        $totalSatisfait = $totalDispatch + $totalAchats;
        $totalRestant = max(0, $totalBesoins - $totalSatisfait);
        $argent = $this->getArgentDisponible();

        return [
            'total_besoins_valeur' => $totalBesoins,
            'total_dispatch_valeur' => $totalDispatch,
            'total_achats_valeur' => $totalAchats,
            'total_satisfait_valeur' => $totalSatisfait,
            'total_restant_valeur' => $totalRestant,
            'total_frais' => $totalFrais,
            'couverture_pourcent' => $totalBesoins > 0 ? round(($totalSatisfait / $totalBesoins) * 100, 1) : 0,
            'dispatch_pourcent' => $totalBesoins > 0 ? round(($totalDispatch / $totalBesoins) * 100, 1) : 0,
            'achats_pourcent' => $totalBesoins > 0 ? round(($totalAchats / $totalBesoins) * 100, 1) : 0,
            'argent_total_dons' => $argent['total_argent_dons'] ?? 0,
            'argent_depense' => $argent['total_depense'] ?? 0,
            'argent_disponible' => $argent['argent_disponible'] ?? 0
        ];
    }

    /**
     * Récap par ville
     */
    public function getRecapParVille(): array {
        $sql = "SELECT 
                    v.id_ville, v.nom_ville,
                    COALESCE(r.nom_region, '-') AS nom_region,
                    COALESCE(bt.total, 0) AS total_besoins,
                    COALESCE(dt.total, 0) AS total_dispatch,
                    COALESCE(at2.total, 0) AS total_achats,
                    COALESCE(dt.total, 0) + COALESCE(at2.total, 0) AS total_satisfait,
                    GREATEST(0, COALESCE(bt.total, 0) - COALESCE(dt.total, 0) - COALESCE(at2.total, 0)) AS total_restant
                FROM ville v
                LEFT JOIN region r ON v.id_region = r.id_region
                LEFT JOIN (
                    SELECT vb.id_ville, SUM(vb.quantite * b.prix_unitaire) AS total
                    FROM ville_besoin vb JOIN besoin b ON vb.id_besoin = b.id_besoin
                    JOIN type_besoin t ON b.id_type_besoin = t.id_type_besoin
                    WHERE t.nom_type_besoin IN ('Nature', 'Materiaux')
                    GROUP BY vb.id_ville
                ) bt ON bt.id_ville = v.id_ville
                LEFT JOIN (
                    SELECT vb.id_ville, SUM(dp.quantite_attribuee * b.prix_unitaire) AS total
                    FROM dispatch dp JOIN ville_besoin vb ON dp.id_ville_besoin = vb.id_ville_besoin
                    JOIN besoin b ON vb.id_besoin = b.id_besoin
                    JOIN type_besoin t ON b.id_type_besoin = t.id_type_besoin
                    WHERE t.nom_type_besoin IN ('Nature', 'Materiaux')
                    GROUP BY vb.id_ville
                ) dt ON dt.id_ville = v.id_ville
                LEFT JOIN (
                    SELECT vb.id_ville, SUM(a.quantite * a.prix_unitaire) AS total
                    FROM achat a JOIN ville_besoin vb ON a.id_ville_besoin = vb.id_ville_besoin
                    WHERE a.simulation = 0 GROUP BY vb.id_ville
                ) at2 ON at2.id_ville = v.id_ville
                WHERE COALESCE(bt.total, 0) > 0
                ORDER BY v.nom_ville";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Liste des villes (pour le filtre)
     */
    public function getVilles(): array {
        $sql = "SELECT id_ville, nom_ville FROM ville ORDER BY nom_ville";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Réinitialise tous les achats
     */
    public function resetAchats(): bool {
        $sql = "DELETE FROM achat";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }
}
