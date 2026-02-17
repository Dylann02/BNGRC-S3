<?php
namespace app\controllers;

use app\models\RecapModel;
use Flight;

class RecapController
{
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function index()
    {
        $recapModel = new RecapModel(Flight::db());
        $recap = $recapModel->getRecapBesoins();

        $this->app->render('recap', [
            'recap' => $recap
        ]);
    }

    public function getRecapJson()
    {
        $recapModel = new RecapModel(Flight::db());
        $recap = $recapModel->getRecapBesoins();

        Flight::json($recap);
    }
}