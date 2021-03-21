<?php

namespace lbs\command\controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Ramsey\Uuid\Uuid;
use lbs\command\models\Command;
use GuzzleHttp\Client as Client;
use lbs\command\models\Item;

class CommandeController
{
    private $c;

    public function __construct($c)
    {
        $this->c = $c;
    }

    public function createCommand(Request $rq, Response $res)
    {
        $command_data = $rq->getParsedBody(); // récupération des données de la commande

        // Création nouvelle commande
        $cmd = new Command();
        $cmd->id = Uuid::uuid4();
        $body = json_decode($rq->getBody());
        $cmd->nom = $body->nom;
        $cmd->mail = $body->mail;
        // Ajout de la date et l'heure dans le body
        $date = date_create_from_format('d-m-Y', $body->livraison->date);
        $heure = date_create_from_format("H:i", $body->livraison->heure);
        $cmd->livraison = $date->format("Y-m-d") . " " . $heure->format("H:i:s");
        $cmd->status = 1;
        $cmd->token = bin2hex(random_bytes(32));
        $cmd->montant = 0;

        $catalogue = new Client([
            // Base URL : pour ensuite transmettre des requêtes relatives
            'base_uri' => 'http://api.catalogue.local',
        ]);

        // Si la commande contient des items alors on parcourt les items dans l'array items
        if (isset($command_data["items"])) {
            foreach ($command_data["items"] as $item) {
                $uri = $item["uri"];
                $q = $item["q"];

                // Requête sur l'api catalogue pour récupérer les données de chaque sandwichs
                $resp = $catalogue->request("GET", $uri);
                $item_catalogue = json_decode($resp->getBody());

                // Création de l'item
                $_item = new Item();
                $_item->uri = $uri;

                // ajoute le libelle et le tarif du sandwich en récupérant les données dans $item_catalogue
                $_item->libelle = $item_catalogue->sandwich->nom;
                $_item->tarif = $item_catalogue->sandwich->prix;

                $_item->quantite = $q;
                $_item->command_id = $cmd->id; // assignation de l'id de la commande aux items ajoutées dans la bdd
                $montant = 0;
                $_item->save();
                $montant += $item_catalogue->sandwich->prix * $item_catalogue->sandwich->prix;
            }
        }
        $cmd->montant = $montant;

        try {
            $cmd->save();
        } catch (\Exception $e) {
            $res = $res->withStatus(500)
                ->withHeader('Content-Type', 'application/json; charset=utf-8');
            $res->getBody()->write(json_encode($e->getmessage()));
            return $res;
        }

        $json = array(
            "commande" => [
                "nom" => $cmd->nom,
                "mail" => $cmd->mail,
                "livraison" => [
                    "date" => explode(" ", $cmd->livraison)[0],
                    "heure" => explode(" ", $cmd->livraison)[1]
                ]
            ],
            "id" => $cmd->id,
            "token" => $cmd->token,
            "montant" => $cmd->montant,
            "items" => $command_data["items"]
        );

        $res = $res->withStatus(201)
            ->withHeader('Content-Type', 'application/json; charset=utf-8');
        $res->getBody()->write(json_encode($json));
        return $res;
    }

    public function getCommand(Request $rq, Response $res, array $args): Response
    {
        $id_sandwich = $args["id"]; // récupération de l'arg id mis dans l'url

        $cmd = Command::where("id", "=", $id_sandwich)->firstOrFail(); // Récupération de la commande

        $json_cmd = array(
            "commande" => [
                "nom" => $cmd->nom,
                "mail" => $cmd->mail,
                "livraison" => [
                    "date" => explode(" ", $cmd->livraison)[0],
                    "heure" => explode(" ", $cmd->livraison)[1]
                ]
            ],
            "id" => $cmd->id,
            "token" => $cmd->token,
            "montant" => $cmd->montant
        );

        $res = $res->withStatus(200)
            ->withHeader("Content-Type", "application/json; charset=utf-8");
        $res->getBody()->write(json_encode($json_cmd));
        return $res;
    }

    public function test(Request $rq, Response $res): Response
    {
        $client = new Client([
            // Base URL : pour ensuite transmettre des requêtes relatives
            'base_uri' => 'http://api.catalogue.local',
            // options par défaut pour les requêtes
            'timeout' => 2.0,
        ]);

        $resp = $client->request("GET", "/sandwichs");
        $code = $resp->getStatusCode(200);
        $type = $resp->getHeader("Content-Type; charset=utf-8");
        $body = $resp->getBody();
        return $resp;
    }
}
