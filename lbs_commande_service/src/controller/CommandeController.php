<?php

namespace lbs\command\controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Ramsey\Uuid\Uuid;
use lbs\command\models\Command;
use GuzzleHttp\Client as Guzzle;

class CommandeController
{
    private $c;

    public function __construct($c)
    {
        $this->c = $c;
    }

    public function createCommand(Request $rq, Response $res)
    {
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

        try {
            $cmd->save();
        } catch (\Exception $e) {
            $res = $res->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
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
            "montant" => $cmd->montant
        );
        $res = $res->withStatus(201)
            ->withHeader('Content-Type', 'application/json');
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
            ->withHeader("Content-Type", "application/json");
        $res->getBody()->write(json_encode($json_cmd));
        return $res;
    }
}
