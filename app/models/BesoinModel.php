<?php
namespace app\models;

class BesoinModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function createBesoin($nom_besoin, $id_type_besoin, $prix_unitaire, $id_ville, $quantite)
    {
        $sql = "INSERT INTO besoin (nom_besoin, id_type_besoin, prix_unitaire) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nom_besoin, $id_type_besoin, $prix_unitaire]);

        $id_besoin = $this->db->lastInsertId();


        $sql2 = "INSERT INTO ville_besoin (id_ville, id_besoin, quantite) VALUES (?, ?, ?)";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->execute([$id_ville, $id_besoin, $quantite]);
    }
    public function deleteBesoin($id_besoin)
    {

        $sql2 = "DELETE FROM ville_besoin WHERE id_besoin = ?";
        $stmt2 = $this->db->prepare($sql2);

        $sql = "DELETE FROM besoin WHERE id_besoin = ?";
        $stmt = $this->db->prepare($sql);
        $stmt2->execute([$id_besoin]);
        $stmt->execute([$id_besoin]);

    }

    public function findAll()
    {
        $sql = "SELECT * FROM  v_affiche_besoin";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getVille()
    {
        $sql = "SELECT * FROM  ville";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function typeBesoin()
    {
        $sql = "SELECT * FROM type_besoin";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

}

?>