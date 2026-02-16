<?php
namespace app\models;


class DonsModel {
    private $app;
    private $db;

    public function __construct($app) {
        $this->app = $app;
        $this->db = $app->db();
    }


    public function createDon($id_besoin, $quantite, $donateur) {
        $sql = "INSERT INTO don (id_besoin, quantite, donateur) VALUES (:id_besoin, :quantite, :donateur)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_besoin', $id_besoin, \PDO::PARAM_INT);
        $stmt->bindValue(':quantite', $quantite, \PDO::PARAM_INT);
        $stmt->bindValue(':donateur', $donateur);
        return $stmt->execute();
    }


    public function getAllDons() {
        $sql = "SELECT 
            b.nom_besoin, 
            t.nom_type_besoin, 
            SUM(d.quantite) AS quantite_totale,
            SUM(d.quantite * b.prix_unitaire) AS prix_total,
            MAX(d.date_don) AS date_don,
            d.donateur
        FROM don d
        JOIN besoin b ON d.id_besoin = b.id_besoin
        JOIN type_besoin t ON b.id_type_besoin = t.id_type_besoin
        GROUP BY b.nom_besoin, t.nom_type_besoin, d.donateur
        ORDER BY date_don DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    public function getDonById($id) {
        $sql = "SELECT * FROM don WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }


    public function getAllTypeBesoin(){
        $sql= "SELECT * FROM type_besoin";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);  
    }


    public function getAllBesoin(){
        $sql= "SELECT * FROM besoin";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC); 
    }


    public function updateDon($id, $date, $donateur, $type, $designation, $prixUnitaire, $quantite, $statut) {
        $sql = "UPDATE don SET date = :date, donateur = :donateur, type = :type, designation = :designation, prix_unitaire = :prix_unitaire, quantite = :quantite, statut = :statut WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':donateur', $donateur);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':designation', $designation);
        $stmt->bindParam(':prix_unitaire', $prixUnitaire);
        $stmt->bindParam(':quantite', $quantite);
        $stmt->bindParam(':statut', $statut);
        return $stmt->execute();
    }


    public function deleteDon($id) {
        $sql = "DELETE FROM don WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        return $stmt->execute();
    }
}

?>