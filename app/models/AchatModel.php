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
     * Calcule l'argent disponible (somme des dons en argent restants)
     */
    public function getArgentDisponible(): array {
        // Total initial de tous les dons en argent (quantite * prix_unitaire, prix_unitaire=1 pour argent)
        $sql = "SELECT 
                    COALESCE(SUM(d.quantite * b.prix_unitaire), 0) AS argent_disponible
                FROM don d
                JOIN besoin b ON d.id_besoin = b.id_besoin
                JOIN type_besoin t ON b.id_type_besoin = t.id_type_besoin
                WHERE t.nom_type_besoin = 'Argent'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Total dépensé en achats
        $sql2 = "SELECT COALESCE(SUM(montant_total), 0) AS total_depense FROM achat";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->execute();
        $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);

        return [
            'argent_disponible' => (float) ($row['argent_disponible'] ?? 0),
            'total_depense' => (float) ($row2['total_depense'] ?? 0),
        ];
    }

    /**
     * Liste les besoins achetables (Nature, Materiaux, Sante, Logement — tout sauf Argent)
     * avec la quantité restante non satisfaite (besoins - dispatch - achats déjà faits)
     */
    public function getBesoinsAchetables(): array {
        $sql = "SELECT 
                    vb.id_ville_besoin,
                    v.nom_ville,
                    b.id_besoin,
                    b.nom_besoin,
                    t.nom_type_besoin,
                    b.prix_unitaire,
                    vb.quantite AS quantite_demandee,
                    COALESCE(disp.qte_dispatch, 0) AS quantite_dispatchee,
                    COALESCE(ach.qte_achat, 0) AS quantite_achetee,
                    vb.quantite - COALESCE(disp.qte_dispatch, 0) - COALESCE(ach.qte_achat, 0) AS quantite_restante,
                    (vb.quantite - COALESCE(disp.qte_dispatch, 0) - COALESCE(ach.qte_achat, 0)) * b.prix_unitaire AS cout_restant
                FROM ville_besoin vb
                JOIN ville v ON vb.id_ville = v.id_ville
                JOIN besoin b ON vb.id_besoin = b.id_besoin
                JOIN type_besoin t ON b.id_type_besoin = t.id_type_besoin
                LEFT JOIN (
                    SELECT dp.id_ville_besoin, SUM(dp.quantite_attribuee) AS qte_dispatch
                    FROM dispatch dp GROUP BY dp.id_ville_besoin
                ) disp ON disp.id_ville_besoin = vb.id_ville_besoin
                LEFT JOIN (
                    SELECT a.id_ville_besoin, SUM(a.quantite) AS qte_achat
                    FROM achat a GROUP BY a.id_ville_besoin
                ) ach ON ach.id_ville_besoin = vb.id_ville_besoin
                WHERE t.nom_type_besoin != 'Argent'
                ORDER BY v.nom_ville, b.nom_besoin";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Effectue un achat : déduit l'argent des dons en argent (FIFO) et enregistre l'achat
     */
    public function acheter(int $idVilleBesoin, int $quantite): array {
        // Vérifier le besoin
        $sql = "SELECT vb.*, b.prix_unitaire, b.nom_besoin, t.nom_type_besoin,
                    vb.quantite - COALESCE(disp.qte, 0) - COALESCE(ach.qte, 0) AS quantite_restante
                FROM ville_besoin vb
                JOIN besoin b ON vb.id_besoin = b.id_besoin
                JOIN type_besoin t ON b.id_type_besoin = t.id_type_besoin
                LEFT JOIN (SELECT id_ville_besoin, SUM(quantite_attribuee) AS qte FROM dispatch GROUP BY id_ville_besoin) disp ON disp.id_ville_besoin = vb.id_ville_besoin
                LEFT JOIN (SELECT id_ville_besoin, SUM(quantite) AS qte FROM achat GROUP BY id_ville_besoin) ach ON ach.id_ville_besoin = vb.id_ville_besoin
                WHERE vb.id_ville_besoin = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $idVilleBesoin, PDO::PARAM_INT);
        $stmt->execute();
        $besoin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$besoin) {
            return ['error' => 'Besoin introuvable.'];
        }
        if ($besoin['nom_type_besoin'] === 'Argent') {
            return ['error' => 'Impossible d\'acheter un besoin de type Argent.'];
        }
        if ($quantite > $besoin['quantite_restante']) {
            $quantite = (int) $besoin['quantite_restante'];
        }
        if ($quantite <= 0) {
            return ['error' => 'Quantité invalide ou besoin déjà satisfait.'];
        }

        // Appliquer la majoration des frais d'achat
        $fraisPourcent = $_SESSION['frais_achat_pourcent'] ?? ($this->app->get('config')['frais_achat_pourcent'] ?? 0);
        $prixMajore = $besoin['prix_unitaire'] * (1 + $fraisPourcent / 100);
        $montantTotal = $quantite * $prixMajore;

        // Vérifier l'argent disponible
        $argent = $this->getArgentDisponible();
        $argentDispo = $argent['argent_disponible'];

        if ($montantTotal > $argentDispo) {
            return ['error' => "Fonds insuffisants. Disponible: " . number_format($argentDispo, 0, ',', ' ') . " Ar, Requis: " . number_format($montantTotal, 0, ',', ' ') . " Ar."];
        }

        $this->db->beginTransaction();
        try {
            // 1. Déduire l'argent des dons en argent (FIFO par date_don)
            $this->deduireArgent($montantTotal);

            // 2. Insérer un don avec donateur "Achat" pour le besoin acheté
            $sqlDon = "INSERT INTO don (donateur, id_besoin, quantite) VALUES ('Achat', :id_besoin, :qte)";
            $stmtDon = $this->db->prepare($sqlDon);
            $stmtDon->bindValue(':id_besoin', $besoin['id_besoin'], PDO::PARAM_INT);
            $stmtDon->bindValue(':qte', $quantite, PDO::PARAM_INT);
            $stmtDon->execute();

            // 3. Enregistrer l'achat (avec prix majoré)
            $sqlInsert = "INSERT INTO achat (id_ville_besoin, quantite, prix_unitaire, montant_total) 
                          VALUES (:id_vb, :qte, :pu, :mt)";
            $stmtInsert = $this->db->prepare($sqlInsert);
            $stmtInsert->bindValue(':id_vb', $idVilleBesoin, PDO::PARAM_INT);
            $stmtInsert->bindValue(':qte', $quantite, PDO::PARAM_INT);
            $stmtInsert->bindValue(':pu', $prixMajore);
            $stmtInsert->bindValue(':mt', $montantTotal);
            $stmtInsert->execute();

            $this->db->commit();
            return ['success' => true, 'montant' => $montantTotal, 'quantite' => $quantite];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['error' => 'Erreur lors de l\'achat : ' . $e->getMessage()];
        }
    }

    /**
     * Déduit un montant des dons en argent (FIFO par date_don)
     * Diminue don.quantite pour chaque don en argent jusqu'à couvrir le montant
     */
    private function deduireArgent(float $montant): void {
        // Récupérer les dons en argent avec quantité restante > 0, FIFO
        $sql = "SELECT d.id, d.quantite, b.prix_unitaire
                FROM don d
                JOIN besoin b ON d.id_besoin = b.id_besoin
                JOIN type_besoin t ON b.id_type_besoin = t.id_type_besoin
                WHERE t.nom_type_besoin = 'Argent' AND d.quantite > 0
                ORDER BY d.date_don ASC, d.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $donsArgent = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $resteADeduire = $montant;

        foreach ($donsArgent as $don) {
            if ($resteADeduire <= 0) break;

            $valeurDon = $don['quantite'] * $don['prix_unitaire']; // prix_unitaire = 1 pour argent
            $deduction = min($resteADeduire, $valeurDon);
            $qteARetirer = (int) ceil($deduction / $don['prix_unitaire']);
            $qteARetirer = min($qteARetirer, $don['quantite']);

            $sqlUpdate = "UPDATE don SET quantite = quantite - :qte WHERE id = :id";
            $stmtUpdate = $this->db->prepare($sqlUpdate);
            $stmtUpdate->bindValue(':qte', $qteARetirer, PDO::PARAM_INT);
            $stmtUpdate->bindValue(':id', $don['id'], PDO::PARAM_INT);
            $stmtUpdate->execute();

            $resteADeduire -= $qteARetirer * $don['prix_unitaire'];
        }
    }

    /**
     * Historique des achats effectués
     */
    public function getHistoriqueAchats(): array {
        $sql = "SELECT 
                    a.id_achat,
                    v.nom_ville,
                    b.nom_besoin,
                    t.nom_type_besoin,
                    a.quantite,
                    a.prix_unitaire,
                    a.montant_total,
                    a.date_achat
                FROM achat a
                JOIN ville_besoin vb ON a.id_ville_besoin = vb.id_ville_besoin
                JOIN ville v ON vb.id_ville = v.id_ville
                JOIN besoin b ON vb.id_besoin = b.id_besoin
                JOIN type_besoin t ON b.id_type_besoin = t.id_type_besoin
                ORDER BY a.date_achat DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Réinitialise les achats et restaure l'argent dans les dons
     */
    public function resetAchats(): bool {
        $this->db->beginTransaction();
        try {
            // Restaurer l'argent : pour chaque achat, on rajoute la quantité en argent au premier don argent
            $sql = "SELECT SUM(montant_total) AS total FROM achat";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $total = (float) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

            if ($total > 0) {
                // Restaurer au premier don en argent
                $sqlDon = "SELECT d.id FROM don d
                           JOIN besoin b ON d.id_besoin = b.id_besoin
                           JOIN type_besoin t ON b.id_type_besoin = t.id_type_besoin
                           WHERE t.nom_type_besoin = 'Argent'
                           ORDER BY d.date_don ASC LIMIT 1";
                $stmtDon = $this->db->prepare($sqlDon);
                $stmtDon->execute();
                $don = $stmtDon->fetch(PDO::FETCH_ASSOC);

                if ($don) {
                    $sqlRestore = "UPDATE don SET quantite = quantite + :total WHERE id = :id";
                    $stmtRestore = $this->db->prepare($sqlRestore);
                    $stmtRestore->bindValue(':total', (int) $total, PDO::PARAM_INT);
                    $stmtRestore->bindValue(':id', $don['id'], PDO::PARAM_INT);
                    $stmtRestore->execute();
                }
            }

            // Supprimer les achats
            $sqlDelete = "DELETE FROM achat";
            $stmtDelete = $this->db->prepare($sqlDelete);
            $stmtDelete->execute();

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
