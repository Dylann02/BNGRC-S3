<?php
namespace app\controllers;

use app\models\DispatchModel;

class DispatchController {
    private $app;
    private $model;

    public function __construct($app) {
        $this->app = $app;
        $this->model = new DispatchModel($app);
    }

    public function index(): array {
        return [
            'historique' => $this->model->getHistorique(),
            'resume' => $this->model->getResumeParVille(),
            'total' => $this->model->getTotalDispatche()
        ];
    }

    public function lancer(string $strategie = 'besoin'): array {
        return $this->model->lancerDispatch($strategie);
    }


    public function reset(): bool {
        return $this->model->resetDispatch();
    }
}
