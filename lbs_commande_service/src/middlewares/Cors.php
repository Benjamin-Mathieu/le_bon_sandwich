<?php

namespace lbs\command\middlewares;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use lbs\command\models\Command;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Cors
{
    private $c; // le conteneur de dépendances de l'appli

    public function __construct(\Slim\Container $c)
    {
        $this->c = $c;
    }

    public function checkCors(Request $rq, Response $rs, callable $next): Response
    {
        if (!$rq->hasHeader('Origin')) {
            $json_error = array(
                "error" => 401,
                "message" => "missing Origin Header (cors)"
            );
            $rs = $rs->withStatus(401)
                ->withHeader("Content-Type", "application/json; charset=utf-8");
            $rs->getBody()->write(json_encode($json_error));
            return $rs;
        };

        $response = $next($rq, $rs);
        $response = $response->withHeader('Access-Control-Allow-Origin', $rq->getHeader('Origin'))
            ->withHeader('Access-Control-Allow-Methods', implode(', ', $this->c['settings']['cors']['methods']))
            ->withHeader('Access-Control-Allow-Headers', implode(', ', $this->c['settings']['cors']['allow.headers']))
            ->withHeader('Access-Control-Max-Age', $this->c['settings']['cors']['max.age']);


        if ($this->c['settings']['cors']['credentials']) {
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }
        return $response;
    }
}
