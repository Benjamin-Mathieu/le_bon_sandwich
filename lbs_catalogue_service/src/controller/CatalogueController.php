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
                ]
            );
            $count++;
            array_push($collection['sandwichs'], $s);
        }
        $collection['count'] = $count;
        $resp = $resp->withHeader('Content-Type', 'application/json');
        $resp->getBody()->write(json_encode($collection));
        return $resp;

    }

    public function getSandwichsFilter(Request $rq, Response $resp, $args){

        // Connexion à la DB
        $connection = new \MongoDB\Client("mongodb://dbcat");
        // Sélectionne la base de donnée à utiliser
        $db_catalogue = $connection->catalogue;

        if($rq->getQueryParams($default = null)){
            $params = $rq->getQueryParams();
            
            if(isset($params['t'])){
                $type_pain = $params['t'];
                $type_array = array('type_pain' => $type_pain);
                $sandwiches = $db_catalogue->sandwiches->find($type_array);
                
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
                        ]
                    );
                    $count++;
                    array_push($collection['sandwichs'], $s);
                }

                $collection['count'] = $count;

                if($count == 0){
                    $collection['sandwichs'] = "Il n'y a pas de sandwichs de ce type";
                    $resp = $resp->withHeader('Content-Type', 'application/json');
                    $resp->getBody()->write(json_encode($collection));
                    return $resp;

                }else{
                    $resp = $resp->withHeader('Content-Type', 'application/json');
                    $resp->getBody()->write(json_encode($collection));
                    return $resp;
                }
            }
        }
    }

    public function getSandwishsByCategorie(Request $rq, Response $resp, $args){
        // Connexion à la DB
        $connection = new \MongoDB\Client("mongodb://dbcat");
        // Sélectionne la base de donnée à utiliser
        $db_catalogue = $connection->catalogue;

        $categ_id = $args['id'];
        
        $categ_array = array("id" => intval($categ_id));
        $la_categ = $db_catalogue->categories->findOne($categ_array);
        $categ_sand = array("categories" => $la_categ->nom);
        $sandwiches = $db_catalogue->sandwiches->find($categ_sand);

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
}
