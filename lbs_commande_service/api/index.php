<?php
require_once  __DIR__ . '/../src/vendor/autoload.php';

use lbs\command\controller\CommandeController;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$config_slim = require_once('conf/Settings.php'); /* Récupération de la config de Slim */
$errors = require_once('conf/Errors.php'); /* Récupération des erreurs */

/* Création du conteneur pour utiliser la cfg dans le programme */
$container = new \Slim\Container(array_merge($config_slim, $errors));

// Connection à la BDD
$container['db'] = function ($container) {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($container['settings']['db']);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};

$app = new \Slim\App($container);

// *****************    ROUTES  *****************
$app->post('/commandes', CommandeController::class . ':createCommand');

$app->get('/commandes/{id}', CommandeController::class . ':getCommand')
    ->setName("command");

$app->run();
