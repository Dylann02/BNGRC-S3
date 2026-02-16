<?php
use app\middlewares\SecurityHeadersMiddleware;
use app\controllers\BesoinController;
use app\controllers\DonsController;
use app\controllers\DispatchController;
use app\controllers\VillesController;
use app\controllers\AchatController;

use flight\Engine;
use flight\net\Router;

/** 
 * @var Router $router 
 * @var Engine $app
 */

// This wraps all routes in the group with the SecurityHeadersMiddleware
$router->group('', function (Router $router) use ($app) {
	$router->get('/', function () use ($app) {
		$app->redirect('/home');
	});
	$router->get('/home' ,function () use ($app){
		$donController = new DonsController($app);
		$besoin = new BesoinController();
		$data = $besoin->getAll();
		$dons = $donController->index();
		$app->render('home',['dons'=>$dons,'besoin'=>$data]);
	});
	$router->get('/dons', function () use ($app) {
		$donController = new DonsController($app);
		$dons = $donController->index();
		$type_besoin = $donController->getAllTypeBesoin();
		$besoins = $donController->getAllBesoin();
		$app->render('dons', [
			'dons' => $dons,
			'type_besoin' => $type_besoin,
			'besoins' => $besoins,
			'nonce' => $app->get('csp_nonce')
		]);
	});
	$router->post('/dons/create', function () use ($app) {
		$donController = new DonsController($app);
		$id_besoin = (int) ($_POST['id_besoin'] ?? 0);
		$quantite = (int) ($_POST['quantiteDon'] ?? 0);
		$donateur = trim($_POST['donateur'] ?? '');
		$donController->create($id_besoin, $quantite, $donateur);
		$app->redirect('/dons');
	});
	
	$router->get('/dispatch' ,function () use ($app){
		$dispatchController = new DispatchController($app);
		$data = $dispatchController->index();
		$app->render('dispatch', [
			'historique' => $data['historique'],
			'resume' => $data['resume'],
			'total' => $data['total']
		]);
	});
	$router->get('/dispatch/lancer' ,function () use ($app){
		$dispatchController = new DispatchController($app);
		$dispatchController->lancer();
		$app->redirect('/dispatch');
	});
	$router->get('/dispatch/reset' ,function () use ($app){
		$dispatchController = new DispatchController($app);
		$dispatchController->reset();
		$app->redirect('/dispatch');
	});

	$router->get('/villes', function () use ($app) {
		$controller = new VillesController($app);
		$villes = $controller->getVilles();
		$regions = $controller->getRegions();

		$app->render('villes', [
			'villes' => $villes,
			'regions' => $regions
		]);


	});

	$router->post('/villes', function () use ($app) {
		$controller = new VillesController($app);
		$controller->addVille();
		$app->redirect('/villes');
	});


	$router->get('/besoins', function () use ($app) {
		$besoin = new BesoinController();
		$data = $besoin->getAll();
		$ville = $besoin->getVille();
		$typeBesoin = $besoin->getTypeBesoin();
		$app->render('besoins', [
			'data' => $data,
			'ville' => $ville,
			'typeBesoin' => $typeBesoin
		]);
	});
	$router->get('/supprimerBesoin/@id', function ($id) use ($app) {
		$besoin = new BesoinController();
		$besoin->delete($id);
		$app->redirect('/besoins');
	});
	$router->post('/traitementForm', function () use ($app) {
		$besoin = new BesoinController();
		$besoin->create();
		$app->redirect('/besoins');
	});

	// --- Achats via dons en argent ---
	$router->get('/achats', function () use ($app) {
		$controller = new AchatController($app);
		$idVille = isset($_GET['ville']) && $_GET['ville'] !== '' ? (int) $_GET['ville'] : null;
		$data = $controller->index($idVille);
		$app->render('achats', [
			'argent' => $data['argent'],
			'besoins' => $data['besoins'],
			'historique' => $data['historique'],
			'villes' => $data['villes'],
			'filtre_ville' => $data['filtre_ville'],
			'error' => $_GET['error'] ?? null,
			'success' => $_GET['success'] ?? null
		]);
	});
	$router->post('/achats/acheter', function () use ($app) {
		$controller = new AchatController($app);
		$idVilleBesoin = (int) ($_POST['id_ville_besoin'] ?? 0);
		$quantite = (int) ($_POST['quantite'] ?? 0);
		$result = $controller->acheter($idVilleBesoin, $quantite);
		if (isset($result['error'])) {
			$app->redirect('/achats?error=' . urlencode($result['error']));
		} else {
			$app->redirect('/achats?success=' . urlencode('Achat effectué avec succès !'));
		}
	});
	$router->get('/achats/reset', function () use ($app) {
		$controller = new AchatController($app);
		$controller->reset();
		$app->redirect('/achats');
	});

	// --- Simulation d'achats ---
	$router->get('/simulation', function () use ($app) {
		$controller = new AchatController($app);
		$idVille = isset($_GET['ville']) && $_GET['ville'] !== '' ? (int) $_GET['ville'] : null;
		$data = $controller->simulation($idVille);
		$app->render('simulation', [
			'argent' => $data['argent'],
			'besoins' => $data['besoins'],
			'simulations' => $data['simulations'],
			'villes' => $data['villes'],
			'filtre_ville' => $data['filtre_ville'],
			'error' => $_GET['error'] ?? null,
			'success' => $_GET['success'] ?? null
		]);
	});
	$router->post('/simulation/simuler', function () use ($app) {
		$controller = new AchatController($app);
		$idVilleBesoin = (int) ($_POST['id_ville_besoin'] ?? 0);
		$quantite = (int) ($_POST['quantite'] ?? 0);
		$result = $controller->simuler($idVilleBesoin, $quantite);
		if (isset($result['error'])) {
			$app->redirect('/simulation?error=' . urlencode($result['error']));
		} else {
			$app->redirect('/simulation?success=' . urlencode('Simulation enregistrée !'));
		}
	});
	$router->get('/simulation/valider', function () use ($app) {
		$controller = new AchatController($app);
		$result = $controller->validerSimulations();
		if (isset($result['error'])) {
			$app->redirect('/simulation?error=' . urlencode($result['error']));
		} else {
			$app->redirect('/achats?success=' . urlencode($result['message'] ?? 'Simulations validées !'));
		}
	});
	$router->get('/simulation/annuler', function () use ($app) {
		$controller = new AchatController($app);
		$controller->annulerSimulations();
		$app->redirect('/simulation?success=' . urlencode('Simulations annulées.'));
	});

	// --- Récapitulation ---
	$router->get('/recap', function () use ($app) {
		$controller = new AchatController($app);
		$data = $controller->recap();
		$app->render('recap', [
			'recap' => $data['recap'],
			'recap_villes' => $data['recap_villes']
		]);
	});
	$router->get('/recap/json', function () use ($app) {
		$controller = new AchatController($app);
		$data = $controller->recapJson();
		$app->json($data);
	});
}, [SecurityHeadersMiddleware::class]);