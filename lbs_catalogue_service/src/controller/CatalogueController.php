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

            // Récupération des paramètres page et size
            $params = $rq->getQueryParams();
            $current_page = $params["page"];
            $size = $params["size"];

            $url_sandwich = $this->c->router->pathFor("sandwichs", []); //Générateur d'url: /sandwichs
            $count = $db_catalogue->sandwiches->count(); // Compte le nombre total de sandwichs de la collection sandwiches dans mongo
            $last_page = intdiv($count, $size) + 1;

            // Condition si numéro de page supérieur à la dernière page alors retourner la dernière page
            if ($current_page > $last_page) $current_page = $last_page;
            // Condition si numéro de page inférieur à 1 alors retourne la première page
            if ($current_page < 1) $current_page = 1;


            // Récupération des sandwiches pour la pagination
            $sandwiches = $db_catalogue->sandwiches->find(
                [],
                [
                    'limit' => 0 + $size,
                    'skip' => ($current_page - 1) * $size
                ]
            );

            // ********* JSON ***********
            $collection = array(
                "type" => "collection",
                "count" => $count,
                "date" => date("Y/m/d"),
                "links" => [
                    "next" => ["href" => "$url_sandwich?page=" . ($current_page + 1) . "&size=$size"],
                    "prev" => ["href" => "$url_sandwich?page=" . ($current_page - 1) . "&size=$size"],
                    "last" => ["href" => "$url_sandwich?page=" . $last_page . "&size=$size"],
                    "first" => ["href" => "$url_sandwich?page=1&size=" . $size]
                ],
                "sandwichs" => []
            );

            $count = 0;
            // ********* JSON SANDWICHES ***********
            foreach ($sandwiches as $sandwich) {
                $json_sandwich = array(
                    "sandwich" => [
                        "ref" => $sandwich->ref,
                        "nom" => $sandwich->nom,
                        "type_pain" => $sandwich->type_pain,
                        "prix" => $sandwich->prix
                    ],
                    "links" => ["self" => ["href" => "/sandwichs/$sandwich->ref"]]
                );
                $count++;
                array_push($collection['sandwichs'], $json_sandwich);
            }

            $resp = $resp->withHeader('Content-Type', 'application/json');
            $resp->getBody()->write(json_encode($collection));
            return $resp;
        }

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
