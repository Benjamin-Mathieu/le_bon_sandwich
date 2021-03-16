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

// *****************    ROUTES  *****************

$app->get("/sandwichs", CatalogueController::class . ':getSandwichs')->setName("sandwichs");

$app->get("/sandwichs/{ref}[/]", CatalogueController::class . ':getResource')->setName("resource");

$app->get("/categories/{id}/sandwichs[/]",CatalogueController::class . ':getSandwishsByCategorie')->setName("sandwichsByCategories");

$app->get("/categories/{id}[/]",CatalogueController::class . ':getCategorie')->setName("categories");

$app->run();
