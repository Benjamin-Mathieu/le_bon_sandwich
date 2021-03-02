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

$app->get("/sandwichs", function (Request $rq, Response $resp): Response {
    $controller = new CatalogueController($this);

    if ($rq->getQueryParams("page", 0)) {
        $connection = new \MongoDB\Client("mongodb://dbcat");
        $db_catalogue = $connection->catalogue;


        $page = $rq->getQueryParams("page");
        $current_page = $page["page"];
        $next_page = $current_page + 1;
        $prev_page = $current_page - 1;

        if ($current_page == 1) {
            $sandwiches = $db_catalogue->sandwiches->find(
                [],
                [
                    'limit' => 10
                ]
            );
        } else if ($current_page == 2) {
            $sandwiches = $db_catalogue->sandwiches->find(
                [],
                [
                    'limit' => 10,
                    'skip' => ($current_page - 1) * 10
                ]
            );
        }

        echo "<h1>Liste des sandwichs:</h1>";
        foreach ($sandwiches as $sandwich) {
            echo $sandwich->nom . "<br>";
        }

        echo "<a href='/sandwichs?page=$prev_page'>Page précédente
        <a href='/sandwichs?page=$next_page'>Page suivante";

        $resp->getBody()->write("");
        return $resp;
    } else {
        return $controller->getSandwichs($rq, $resp);
    }
});

$app->run();
