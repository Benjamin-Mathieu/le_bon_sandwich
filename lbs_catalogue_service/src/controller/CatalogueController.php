<?php

namespace lbs\catalogue\controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class CatalogueController
{
    private $c;

    public function __construct($c)
    {
        $this->c = $c;
    }

    public function getSandwichs(Request $rq, Response $resp)
    {
        // Connexion à la DB
        $connection = new \MongoDB\Client("mongodb://dbcat");
        // Sélectionne la base de donnée à utiliser
        $db_catalogue = $connection->catalogue;
        // Sélectionne la collection de sandwichs
        $sandwiches = $db_catalogue->sandwiches->find();

        $collection = array(
            "type" => "collection",
            "count" => "",
            "date" => "",
            "sandwichs" => []
        );

        $collection['date'] = date("Y/m/d");
        $count = 0;

        foreach ($sandwiches as $sandwich) {
            $s = array(
                "sandwich" => [
                    "ref" => $sandwich->ref,
                    "nom" => $sandwich->nom,
                    "type_pain" => $sandwich->type_pain,
                    "prix" => $sandwich->prix
                ],
                "links" => ["self" => ["href" => "/sandwichs/$sandwich->ref"]]
            );
            $count++;
            array_push($collection['sandwichs'], $s);
        }
        $collection['count'] = $count;

        $resp = $resp->withHeader('Content-Type', 'application/json');
        $resp->getBody()->write(json_encode($collection));
        return $resp;
    }
}
