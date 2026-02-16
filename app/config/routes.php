<?php


use app\controllers\BesoinController;
use app\middlewares\SecurityHeadersMiddleware;
use flight\Engine;
use flight\net\Router;

/** 
 * @var Router $router 
 * @var Engine $app
 */

// This wraps all routes in the group with the SecurityHeadersMiddleware
$router->group('', function(Router $router) use ($app) {
	$router->get('/' ,function () use ($app){
		$app->render('home');
	});
	$router->get('/home' ,function () use ($app){
		$app->render('home');
	});
	$router->get('/dons' ,function () use ($app){
		$app->render('dons');
	});
	$router->get('/dispatch' ,function () use ($app){
		$app->render('dispatch');
	});
	$router->get('/villes' ,function () use ($app){
		$app->render('villes');
	});
	$router->get('/besoins' ,function () use ($app){
		$besoin = new BesoinController();
		$data=$besoin->getAll();
		$ville=$besoin->getVille();
		$typeBesoin=$besoin->getTypeBesoin();
		$app->render('besoins' , [
			'data' => $data , 
			'ville' => $ville,
			'typeBesoin' => $typeBesoin
		]);
	});
	$router->get('/supprimerBesoin/@id' , function ($id) use ($app){
		$besoin = new BesoinController();
		$besoin->delete($id);
		$app->redirect('/besoins');
	});
	$router->post('/traitementForm' , function () use ($app){
		$besoin = new BesoinController();
		$besoin->create();
		$app->redirect('/besoins');
	});
}, [ SecurityHeadersMiddleware::class ]);