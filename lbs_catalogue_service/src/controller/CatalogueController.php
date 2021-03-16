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

        $params = $rq->getQueryParams();

        // ************* PAGINATION *************
        if (isset($params['page']) && isset($params['size'])) {

            // Récupération des paramètres page et size
            $current_page = $params["page"];
            $size = $params["size"];

            $url_sandwich = $this->c->router->pathFor("sandwichs", []); //Générateur d'url: /sandwichs
            $count = $db_catalogue->sandwiches->count(); // Compte le nombre total de sandwichs de la collection sandwiches dans mongo
            $last_page = intdiv($count, $size) + 1;

            // Récupération des sandwiches pour la pagination
            if(isset($params['t'])){
                $pain = $params["t"];
                $sandwiches = $db_catalogue->sandwiches->find(
                    ['type_pain' => $pain],
                    [
                        'limit' => 0 + $size,
                        'skip' => ($current_page - 1) * $size,
                        
                        
                    ]
                );
            }else{
                $sandwiches = $db_catalogue->sandwiches->find(
                    [],
                    [
                        'limit' => 0 + $size,
                        'skip' => ($current_page - 1) * $size,
                    ]
                );
            }

            // Condition si numéro de page supérieur à la dernière page alors retourner la dernière page
            if ($current_page > $last_page) $current_page = $last_page;
            // Condition si numéro de page inférieur à 1 alors retourne la première page
            if ($current_page < 1) $current_page = 1;

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

            $resp = $resp->withHeader('Content-Type', 'application/json', 'text/html');
            $resp->getBody()->write(json_encode($collection));
            return $resp;
        }

        // ************* PAR DEFAULT AFFICHAGE DES 10 PREMIERS SANDWICHS *************
        
            if(isset($params['t'])){
                $pain = $params["t"];
                $sandwiches = $db_catalogue->sandwiches->find(
                    ['type_pain' => $pain],
                    [
                        'limit' => 10,
                    ]
                );
            }else{
                $sandwiches = $db_catalogue->sandwiches->find(
                    [],
                    [
                        'limit' => 10,
                    ]
                );
            }

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

    public function getCategorie(Request $rq, Response $resp, $args){

        // Connexion à la DB
        $connection = new \MongoDB\Client("mongodb://dbcat");
        // Sélectionne la base de donnée à utiliser
        $db_catalogue = $connection->catalogue;

        $categ_id = $args['id'];
        $id = intval($categ_id);
        $categ_array = array("id" => $id);
        $la_categ = $db_catalogue->categories->findOne($categ_array); // recherche une categorie selon l'id

        $resource = array(
            "type" => "resource",
            "date" => date("Y/m/d"),
            "categorie" => [
                "id" => $la_categ->id,
                "nom" => $la_categ->nom,
                "description" => $la_categ->description
            ],
            "links" => [
                "sandwichs" => ["href" => "/categories/${id}/sandwichs/" ],
                "self" => ["href" => "/categories/${id}/" ],
            ]

            );

        $resp = $resp->withHeader('Content-Type', 'application/json');
        $resp->getBody()->write(json_encode($resource));
        return $resp;

    }

    public function getSandwishsByCategorie(Request $rq, Response $resp, $args){

        // Connexion à la DB
        $connection = new \MongoDB\Client("mongodb://dbcat");
        // Sélectionne la base de donnée à utiliser
        $db_catalogue = $connection->catalogue;

        $categ_id = $args['id'];
        
        $categ_array = array("id" => intval($categ_id));
        $la_categ = $db_catalogue->categories->findOne($categ_array); // on récupère la categorie selon le paramètre de la route
        $categ_sand = array("categories" => $la_categ->nom);
        $sandwiches = $db_catalogue->sandwiches->find($categ_sand); // on récupère les sandwichs selon la req du dessus 

        $s1 = array(
            "type" => "collection",
            "count" => "",
            "date" => "",
            "sandwichs" => []
        );
        $s1['date'] = date("Y/m/d");
        $count = 0;

        foreach ($sandwiches as $sandwich) {
            $s = array(
                "sandwich" => [
                    "ref" => $sandwich->ref,
                    "nom" => $sandwich->nom,
                    "type_pain" => $sandwich->type_pain,
                    "prix" => $sandwich->prix
                ]
            );
            $count++;
            array_push($s1['sandwichs'], $s);
        }
        $s1['count'] = $count;

        $resp = $resp->withHeader('Content-Type', 'application/json');
        $resp->getBody()->write(json_encode($s1));
        return $resp;

    } 
    public function getResource(Request $rq, Response $resp, array $args): Response
    {

        // Connexion à la DB
        $connection = new \MongoDB\Client("mongodb://dbcat");
        // Sélectionne la base de donnée à utiliser
        $db_catalogue = $connection->catalogue;

        $ref_sandwich = $args['ref']; // Récupération de l'argument ref dans l'url
        $ref_array = array("ref" => $ref_sandwich);
        $url_resource = $this->c->router->pathFor("resource", ["ref" => $ref_sandwich]); // Génération de l'url /sandwichs/{ref}

        $resource = $db_catalogue->sandwiches->findOne($ref_array); // Récupération du sandwich dans la bdd par rapport à la valeur de sa référence

        // Création du JSON 
        $json = array(
            "type" => "resource",
            "links" => [
                "self" => ["href" => "$url_resource"],
                "categories" => ["href" => $url_resource . "categories"]
            ],
            "sandwich" => [
                "id" => $resource->_id,
                "ref" => $resource->ref,
                "nom" => $resource->nom,
                "description" => $resource->description,
                "type_pain" => $resource->type_pain,
                "categories" => []
            ]
        );

        // Récupération des catégories du sandwich
        foreach ($resource->categories as $categorie) {
            $categ_array = array("nom" => $categorie);
            $categ_sandwich =  $db_catalogue->categories->findOne($categ_array); // Récupère les informations d'une catégorie du sandwich

            $info_categorie = array("id" => $categ_sandwich->id, "nom" => $categ_sandwich->nom);
            array_push($json["sandwich"]["categories"], $info_categorie);
        }

        $resp = $resp->withHeader('Content-Type', 'application/json');
        $resp->getBody()->write(json_encode($json));
        return $resp;
    }   

    
}
