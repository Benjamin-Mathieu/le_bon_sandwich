<?php

namespace lbs\fidelisation\controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \lbs\fidelisation\models\Carte_fidelite;
use Firebase\JWT\JWT;

class AuthController
{

    private $c;

    public function __construct($c)
    {
        $this->c = $c;
    }

    public function authentification(Request $rq, Response $resp, $args)
    {

        if (!$rq->hasHeader('Authorization')) {
            $resp = $resp->withHeader('WWW-authenticate', 'Basic realm="commande_api api"');
            return $resp->getBody()->write(json_encode(
                [
                    'type' => 'error',
                    'error' => 401,
                    'message' => "No Authorization header present",
                ]
            ));
        };

        $authstring = base64_decode(explode(" ", $rq->getHeader('Authorization')[0])[1]);
        list($user, $pass) = explode(':', $authstring);

        try {
            $carte = Carte_fidelite::select('id', 'nom_client', 'mail_client', 'passwd')->where('id', '=', $args['id'])->firstOrFail();

            if (!password_verify($pass, $carte->passwd)) {

                return $resp->getBody()->write(json_encode(
                    [
                        'type' => 'error',
                        'error' => 401,
                        'message' => "password check failed",
                    ]
                ));
            }
            unset($carte->passwd);
        } catch (ModelNotFoundException $e) {
            return $resp->getBody()->write(json_encode(
                [
                    'type' => 'error',
                    'error' => 401,
                    'message' => "Erreur authentification",
                ]
            ));
        }

        $secret = $this->c['settings']['secret'];

        $token = JWT::encode(
            [
                'iss' => "http://api.fidelisation.local/auth",
                'aud' => 'http://api.fidelisation.local',
                'iat' => time(),
                'exp' => time() + (12 * 30 * 24 * 3600),
                'cid' => $carte->id
            ],
            $secret,
            'HS512'
        );

        $resp = $resp
            ->withHeader('Content-Type', 'application/json; charset=utf-8');

        return $resp->getBody()->write(json_encode(
            array("token" => $token)
        ));
    }
}
