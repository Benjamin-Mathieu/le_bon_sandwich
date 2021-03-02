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

        // ************* PAGINATION *************
        if ($rq->getQueryParams("page", "size")) {
            $connection = new \MongoDB\Client("mongodb://dbcat");
            $db_catalogue = $connection->catalogue;

            $params = $rq->getQueryParams("page");
            $current_page = $params["page"];
            $next_page = $current_page + 1;
            $prev_page = $current_page - 1;

            $size = $params["size"];

            $sandwiches = $db_catalogue->sandwiches->find(
                [],
                [
                    'limit' => 0 + $size,
                    'skip' => ($current_page - 1) * $size
                ]
            );

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

            $resp = $resp->withHeader('Content-Type', 'application/json, text/html');
            $resp->getBody()->write(json_encode($collection));
            $resp->getBody()->write("<div><a href='/sandwichs?page=$prev_page&size=$size'>Previous page</a>
            //     <a href='/sandwichs?page=$next_page&size=$size'>Next page</a></div>");
            return $resp;
        }

        // echo "<h1>Liste des sandwichs: (page : $current_page size : $size)</h1>";
        // foreach ($sandwiches as $sandwich) {
        //     echo $sandwich->nom . "<br>";
        // }

        // ************* PAR DEFAULT AFFICHAGE DES 10 PREMIERS SANDWICHS *************
        $sandwiches = $db_catalogue->sandwiches->find(
            [],
            [
                'limit' => 10
            ]
        );

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
