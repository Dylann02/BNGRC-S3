<?php

use app\controllers\ApiExampleController;
use app\middlewares\SecurityHeadersMiddleware;
use flight\Engine;
use flight\net\Router;
use app\middlewares\SecurityHeadersMiddleware;

/** @var Router $router */
/** @var Engine $app */

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
		$app->render('besoins');
	});
}, [ SecurityHeadersMiddleware::class ]);