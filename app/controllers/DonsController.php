<?php
namespace app\controllers;

use app\models\DonsModel;

class DonsController {
    private $app;
    private $model;

    public function __construct($app) {
        $this->app = $app;
        $this->model = new DonsModel($app);
    }


    public function create($id_besoin, $quantite, $donateur) {
        return $this->model->createDon($id_besoin, $quantite, $donateur);
    }


    public function index() {
        return $this->model->getAllDons();
    }


    public function show($id) {
        return $this->model->getDonById($id);
    }

    public function getAllTypeBesoin(){
        return $this->model->getAllTypeBesoin();
    }

    public function getAllBesoin(){
        return $this->model->getAllBesoin();
    }


    public function update($id, $date, $donateur, $type, $designation, $prixUnitaire, $quantite, $statut) {
        return $this->model->updateDon($id, $date, $donateur, $type, $designation, $prixUnitaire, $quantite, $statut);
    }


    public function delete($id) {
        return $this->model->deleteDon($id);
    }
}

?>
