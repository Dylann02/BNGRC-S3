<?php

namespace app\controllers;

use app\models\VilleModel;
use flight\Engine;

class VillesController {

    protected Engine $app;

    public function __construct($app) {
        $this->app = $app;
    }

    public function getVilles() {
        $model = new VilleModel($this->app);
        return $model->getVilles();
    }

    public function getRegions() {
        $model = new VilleModel($this->app);
        return $model->getRegions();
    }

    public function addVille() {
    $data = $this->app->request()->data; 
    $model = new VilleModel($this->app);
    $model->addVille($data);
}

    public function deleteVille($id) {
        $model = new VilleModel($this->app);
        $model->deleteVille($id);
    }
}