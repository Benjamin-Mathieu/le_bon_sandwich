<?php

namespace lbs\command\controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Ramsey\Uuid\Uuid;
use lbs\command\models\Command;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Date;

// $client = new GuzzleHttp\Client([
//     // Base URL : pour ensuite transmettre des requêtes relatives
//     'base_url' => 'http://api.command.local:19043',
//     // options par défaut pour les requêtes
//     'timeout' => 2.0,
// ]);


class CommandeController
{
    private $c;

    public function __construct($c)
    {
        $this->c = $c;
        $table = $c->get('db')->table('commande');
    }

    public function createCommand(Request $rq, Response $res)
    {
        $commands = Command::all();


        $cmd = new Command();
        $cmd->id = Uuid::uuid4();
        $cmd->nom = "Benjamin";
        $cmd->mail = "ben@gmail.com";
        $cmd->livraison = \DateTime::createFromFormat("d-n-Y H:i", "2021-15-03 08:08:48");
        $cmd->status = 1;
        $cmd->token = bin2hex(random_bytes(32));
        $cmd->montant = 0;

        $cmd->save();

        // if (!is_null($commands)) {
        //     $res = $res->withStatus(200)
        //         ->withHeader('Content-Type', 'text/html');
        //     $res->getBody()->write($commands);
        //     return $res;
        // } else {
        //     $res = $res->withStatus(404)
        //         ->withHeader('Content-Type', 'text/html');
        //     $res->getBody()->write(json_encode("Commands Not Found"));
        //     return $res;
        // }
    }
}
