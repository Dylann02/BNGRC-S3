<?php
namespace app\controllers;

use app\models\AchatModel;

class AchatController {
    private $app;
    private $model;

    public function __construct($app) {
        $this->app = $app;
        $this->model = new AchatModel($app);
    }

    /**
     * Page achats : besoins achetables + historique
     */
    public function index(): array {
        return [
            'argent' => $this->model->getArgentDisponible(),
            'besoins' => $this->model->getBesoinsAchetables(),
            'historique' => $this->model->getHistoriqueAchats(),
        ];
    }

    /**
     * Effectue un achat
     */
    public function acheter(int $idVilleBesoin, int $quantite): array {
        return $this->model->acheter($idVilleBesoin, $quantite);
    }

    /**
     * Réinitialise les achats
     */
    public function reset(): bool {
        return $this->model->resetAchats();
    }
}
