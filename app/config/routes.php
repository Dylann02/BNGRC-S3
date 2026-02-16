<?php

use app\controllers\VillesController;
use flight\Engine;
use flight\net\Router;
use app\middlewares\SecurityHeadersMiddleware;

/** @var Router $router */
/** @var Engine $app */

$router->group('', function(Router $router) use ($app) {


    $router->get('/', function () use ($app){
        $app->render('home');
    });

    $router->get('/home', function () use ($app){
        $app->render('home');
    });

    
    $router->get('/dons', function () use ($app){
        $app->render('dons');
    });

    $router->get('/dispatch', function () use ($app){
        $app->render('dispatch');
    });


    $router->get('/villes', function () use ($app){

        $controller = new VillesController($app);
        $villes = $controller->getVilles();
        $regions = $controller->getRegions();

        $app->render('villes', [
            'villes' => $villes,
            'regions' => $regions
        ]);
    });


    $router->post('/villes', function () use ($app){
        $controller = new VillesController($app);
        $controller->addVille();
        Flight::redirect('/villes');
    });

    
    $router->get('/delete-ville/@id', function($id) use ($app){
        $controller = new VillesController($app);
        $controller->deleteVille($id);
        Flight::redirect('/villes');
    });

}, [ SecurityHeadersMiddleware::class ]);