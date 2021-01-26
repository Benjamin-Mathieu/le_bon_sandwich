<?php

require_once  __DIR__ . '/../src/vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

echo "catalogue service index";

$db_mongo = new \MongoDB\Client("mongodb://api.catalogue.local");
echo "connected to mongo<br>";

$app = new \Slim\App();



$app->run();
