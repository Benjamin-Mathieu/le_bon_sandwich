<?php

namespace lbs\command\middlewares;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use lbs\command\models\Command;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Token
{
    private $c; // le conteneur de dépendances de l'appli

    public function __construct(\Slim\Container $c)
    {
        $this->c = $c;
    }

    public function checkToken(Request $rq, Response $res, callable $next): Response
    {
        // récupération de l'id de la commande et le token
        $id = $rq->getAttribute("route")->getArgument("id");
        $token = $rq->getQueryParam("token", null);

        // Vérification si l'id et le token dans l'url correspondent à celles dans la bdd
        try {
            Command::where("id", "=", $id)
                ->where("token", "=", $token)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $res->getBody()->write("Token doesn't correspond");
            return $res;
        }
        return $next($rq, $res);
    }
}
