<?php
namespace app\controllers;
use app\models\BesoinModel;
use Flight;
class BesoinController
{
   public function create()
{
    $request = Flight::request();

   
    $villeId = $request->data['ville'];        
    $typeBesoin = $request->data['typeBesoin']; 
    $designation = $request->data['nom_besoin'];
    $prixUnitaire = $request->data['prixUnitaire'];
    $quantite = $request->data['quantite'];

    $besoinModel = new BesoinModel(Flight::db());
    $besoinModel->createBesoin(
        $designation,
        $typeBesoin,
        $prixUnitaire,
        $villeId,
        $quantite
    );
}
    public function getAll()
    {
        $besoin = new BesoinModel(Flight::db());
        return $besoin->findAll();
    }

    public function delete($id_besoin)
    {
        $besoin = new BesoinModel(Flight::db());
        return $besoin->deleteBesoin($id_besoin);
    }

    public function getVille()
    {
        $besoin = new BesoinModel(Flight::db());
        return $besoin->getVille();
    }

    public function getTypeBesoin()
    {
        $besoin = new BesoinModel(Flight::db());
        return $besoin->typeBesoin();
    }

    public function getBesoinsParType($id_type)
    {
        $besoin = new BesoinModel(Flight::db());
        header('Content-Type: application/json');
        echo json_encode($besoin->getBesoinsParType($id_type));
    }
}

?>