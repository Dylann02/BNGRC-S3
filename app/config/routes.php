<?php

use app\middlewares\SecurityHeadersMiddleware;
use app\controllers\BesoinController;
use app\controllers\DonsController;
use app\controllers\VillesController;
use flight\Engine;
use flight\net\Router;

/** 
 * @var Router $router 
 * @var Engine $app
 */

// This wraps all routes in the group with the SecurityHeadersMiddleware
$router->group('', function (Router $router) use ($app) {
	$router->get('/', function () use ($app) {
		$app->render('home');
	});
	$router->get('/home', function () use ($app) {
		$app->render('home');
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
	$router->get('/dispatch', function () use ($app) {
		$app->render('dispatch');
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
}, [SecurityHeadersMiddleware::class]);