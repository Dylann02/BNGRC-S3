<?php

namespace app\models;

use PDO;

class VilleModel {

	protected $app;
	protected PDO $db;

	public function __construct($app) {

		$this->app = $app;
		$this->db = $app->db();
	}

	 public function getVilles() {
        $stmt = $this->db->query("
            SELECT v.id_ville, v.nom_ville, v.nb_sinistres, r.nom_region
            FROM ville v
            JOIN region r ON v.id_region = r.id_region
            ORDER BY v.id_ville DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔹 Ajouter ville
    public function addVille($data) {
        $stmt = $this->db->prepare("
            INSERT INTO ville (nom_ville, id_region, nb_sinistres)
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([
            $data['nomVille'],
            $data['region'],
            $data['nbSinistres']
        ]);
    }

    // 🔹 Supprimer ville
    public function deleteVille($id) {
        $stmt = $this->db->prepare("DELETE FROM ville WHERE id_ville = ?");
        return $stmt->execute([$id]);
    }

    // 🔹 Liste des régions
    public function getRegions() {
        $stmt = $this->db->query("SELECT * FROM region ORDER BY nom_region");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}