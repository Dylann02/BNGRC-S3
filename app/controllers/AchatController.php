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
     * Page achats : besoins restants + historique, filtrable par ville
     */
    public function index(?int $idVille = null): array {
        return [
            'argent' => $this->model->getArgentDisponible(),
            'besoins' => $this->model->getBesoinsRestants($idVille),
            'historique' => $this->model->getHistoriqueAchats($idVille),
            'villes' => $this->model->getVilles(),
            'filtre_ville' => $idVille
        ];
    }

    /**
     * Effectue un achat réel
     */
    public function acheter(int $idVilleBesoin, int $quantite): array {
        return $this->model->acheter($idVilleBesoin, $quantite, false);
    }

    /**
     * Page simulation : besoins + simulations en cours
     */
    public function simulation(?int $idVille = null): array {
        return [
            'argent' => $this->model->getArgentDisponible(),
            'besoins' => $this->model->getBesoinsRestants($idVille),
            'simulations' => $this->model->getSimulations(),
            'villes' => $this->model->getVilles(),
            'filtre_ville' => $idVille
        ];
    }

    /**
     * Simuler un achat (enregistre en mode simulation)
     */
    public function simuler(int $idVilleBesoin, int $quantite): array {
        return $this->model->acheter($idVilleBesoin, $quantite, true);
    }

    /**
     * Valider toutes les simulations
     */
    public function validerSimulations(): array {
        return $this->model->validerSimulations();
    }

    /**
     * Annuler toutes les simulations
     */
    public function annulerSimulations(): bool {
        return $this->model->supprimerSimulations();
    }

    /**
     * Page récapitulation
     */
    public function recap(): array {
        return [
            'recap' => $this->model->getRecapitulation(),
            'recap_villes' => $this->model->getRecapParVille()
        ];
    }

    /**
     * Récap JSON (pour Ajax)
     */
    public function recapJson(): array {
        return [
            'recap' => $this->model->getRecapitulation(),
            'recap_villes' => $this->model->getRecapParVille()
        ];
    }

    /**
     * Réinitialise les achats
     */
    public function reset(): bool {
        return $this->model->resetAchats();
    }
}
