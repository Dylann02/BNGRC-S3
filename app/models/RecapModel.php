<?php
namespace app\models;

use PDO;

class RecapModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Récupère le récapitulatif des besoins :
     * - Montant total des besoins
     * - Montant des besoins satisfaits (via dispatch)
     * - Montant restant
     */
    public function getRecapBesoins(): array
    {
        // Montant total des besoins (quantite * prix_unitaire pour chaque ville_besoin)
        $sqlTotal = "SELECT COALESCE(SUM(vb.quantite * b.prix_unitaire), 0) AS montant_total
                     FROM ville_besoin vb
                     JOIN besoin b ON vb.id_besoin = b.id_besoin";
        
        $stmt = $this->db->prepare($sqlTotal);
        $stmt->execute();
        $montantTotal = (float) $stmt->fetch(PDO::FETCH_ASSOC)['montant_total'];

        // Montant satisfait (quantité attribuée via dispatch * prix_unitaire)
        $sqlSatisfait = "SELECT COALESCE(SUM(dp.quantite_attribuee * b.prix_unitaire), 0) AS montant_satisfait
                         FROM dispatch dp
                         JOIN ville_besoin vb ON dp.id_ville_besoin = vb.id_ville_besoin
                         JOIN besoin b ON vb.id_besoin = b.id_besoin";
        
        $stmt2 = $this->db->prepare($sqlSatisfait);
        $stmt2->execute();
        $montantSatisfait = (float) $stmt2->fetch(PDO::FETCH_ASSOC)['montant_satisfait'];

        // Montant restant
        $montantRestant = $montantTotal - $montantSatisfait;

        return [
            'montant_total' => $montantTotal,
            'montant_satisfait' => $montantSatisfait,
            'montant_restant' => $montantRestant,
            'pourcentage_satisfait' => $montantTotal > 0 ? round(($montantSatisfait / $montantTotal) * 100, 2) : 0
        ];
    }
}