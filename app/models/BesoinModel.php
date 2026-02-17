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
        // Vérifier si ce besoin existe déjà (case-insensitive)
        $sqlCheck = "SELECT id_besoin FROM besoin WHERE LOWER(nom_besoin) = LOWER(?) AND id_type_besoin = ? LIMIT 1";
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->execute([$nom_besoin, $id_type_besoin]);
        $existant = $stmtCheck->fetch(\PDO::FETCH_ASSOC);

        if ($existant) {
            // Le besoin existe déjà, réutiliser son ID
            $id_besoin = $existant['id_besoin'];
        } else {
            // Créer un nouveau besoin
            $sql = "INSERT INTO besoin (nom_besoin, id_type_besoin, prix_unitaire) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nom_besoin, $id_type_besoin, $prix_unitaire]);
            $id_besoin = $this->db->lastInsertId();
        }

        // Créer l'association ville_besoin
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

    /**
     * Récupère les noms de besoin UNIQUES pour un type donné
     */
    public function getBesoinsParType($id_type_besoin)
    {
        $sql = "SELECT DISTINCT b.nom_besoin FROM besoin b 
                WHERE b.id_type_besoin = ? 
                ORDER BY b.nom_besoin ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_type_besoin]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

}

?>