<?php
require_once  __DIR__ . '/../src/vendor/autoload.php';

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use lbs\command\controller\CommandeController;

$config_slim = require_once('conf/Settings.php'); /* Récupération de la config de Slim */
$errors = require_once('conf/Errors.php'); /* Récupération des erreurs */

/* Création du conteneur pour utiliser la cfg dans le programme */
$container = new \Slim\Container(array_merge($config_slim, $errors));

// Connection à la BDD
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$app = new \Slim\App($container);

// Cors : voir doc (https://www.slimframework.com/docs/v3/cookbook/enable-cors.html)
$app->add(\lbs\command\middlewares\Cors::class . ':checkCors');

$app->options('/{routes:.+}', function (Request $rq, Response $rs) {
    return $rs;
});

// *****************    ROUTES  *****************
$app->post('/commandes', CommandeController::class . ':createCommand');

$app->get('/commandes/{id}', CommandeController::class . ':getCommand')
    ->add(lbs\command\middlewares\Token::class . ":checkToken");

$app->get("/test", CommandeController::class . ":test");

// Catch-all route to serve a 404 Not Found page if none of the routes match
// NOTE: make sure this route is defined last
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($req, $res) {
    $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
    return $handler($req, $res);
});

$app->run();
