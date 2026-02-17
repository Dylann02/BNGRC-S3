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
		$villeController = new VillesController($app);
		$data = $besoin->getAll();
		$dons = $donController->index();
		$villes = $villeController->getVilles();
		$app->render('home',['dons'=>$dons,'besoin'=>$data,'ville'=>$villes]);
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
		$data = $controller->index();
		$config = $app->get('config');
		$fraisPourcent = $_SESSION['frais_achat_pourcent'] ?? ($config['frais_achat_pourcent'] ?? 0);
		$app->render('achats', [
			'argent' => $data['argent'],
			'besoins' => $data['besoins'],
			'historique' => $data['historique'],
			'frais_pourcent' => $fraisPourcent,
			'error' => $_GET['error'] ?? null,
			'success' => $_GET['success'] ?? null
		]);
	});
	$router->post('/achats/frais', function () use ($app) {
		$frais = max(0, (float) ($_POST['frais_pourcent'] ?? 0));
		$_SESSION['frais_achat_pourcent'] = $frais;
		$app->redirect('/achats?success=' . urlencode('Majoration mise à jour : ' . $frais . '%'));
	});
	$router->post('/achats/acheter', function () use ($app) {
		$controller = new AchatController($app);
		$idVilleBesoin = (int) ($_POST['id_ville_besoin'] ?? 0);
		$quantite = (int) ($_POST['quantite'] ?? 0);
		$result = $controller->acheter($idVilleBesoin, $quantite);
		if (isset($result['error'])) {
			$app->redirect('/achats?error=' . urlencode($result['error']));
		} else {
			$app->redirect('/achats?success=' . urlencode('Achat effectué ! Montant déduit : ' . number_format($result['montant'], 0, ',', ' ') . ' Ar'));
		}
	});
	$router->get('/achats/reset', function () use ($app) {
		$controller = new AchatController($app);
		$controller->reset();
		$app->redirect('/achats');
	});

}, [SecurityHeadersMiddleware::class]);