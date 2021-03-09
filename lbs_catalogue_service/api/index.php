<?php

use lbs\catalogue\controller\CatalogueController;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once  __DIR__ . '/../src/vendor/autoload.php';

$config_slim = require_once('conf/Settings.php'); /* Récupération de la config de Slim */
$errors = require_once('conf/Errors.php'); /* Récupération des erreurs */

/* Création du conteneur pour utiliser la cfg dans le programme */
$container = new \Slim\Container(array_merge($config_slim, $errors));

$app = new \Slim\App($container);

$app->get("/sandwichs", function (Request $rq, Response $resp, $args): Response {
    $controller = new CatalogueController($this);

    if($rq->getQueryParams()){
        return $controller->getSandwichsFilter($rq, $resp, $args);
    }else{
        return $controller->getSandwichs($rq, $resp);
    }
});

$app->get("/categories/{id}/sandwichs[/]", function (Request $rq, Response $resp, $args): Response {
    $controller = new CatalogueController($this);
    return $controller->getSandwishsByCategorie($rq, $resp, $args);
});

$app->get("/categories/{id}[/]", function (Request $rq, Response $resp, $args): Response {
    $controller = new CatalogueController($this);
    return $controller->getSandwishsByCategorie($rq, $resp, $args);
});

$app->get("/hello[/]", function (Request $rq, Response $resp, $args): Response {
    $controller = new CatalogueController($this);
    return $controller->getSandwichsFilter($rq, $resp, $args);
});

$app->run();
