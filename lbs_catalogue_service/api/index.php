<?php

require_once  __DIR__ . '/../src/vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

echo "<h2>catalogue service index</h2>";

// Connexion à la DB
$connection = new \MongoDB\Client("mongodb://dbcat");
// Sélectionne la base de donnée à utiliser
$db_catalogue = $connection->catalogue;
// Sélectionne la collection de sandwichs
$sandwiches = $db_catalogue->sandwiches->find();

foreach ($sandwiches as $sandwich) {
    print $sandwich->nom . ' ' . $sandwich->type_pain . '<br>';
}

$app = new \Slim\App();

$app->get("/sandwichs", function (Request $rq, Response $resp) {
});

$app->run();
