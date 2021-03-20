<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException ;
use Firebase\JWT\BeforeValidException;

class JwtToken {

    public function checkJWTClient(Request $rq, Response $rs, callable $next) : Response {

        if(!$rq->hasHeader('Authorization') {
            return Writer::json_error($rs, 401, 'No auhtorization header present');
        })
    
        $token = null;
    
        try {
            $token = $this->decode($rq->getHeader('Authorization')[0]);
        } catch (\UnexpectedValueException $e) {
            return Writer::json_error($rs, 401, 'invalid auth token');
        } catch (\DomainException $e) {
            return Writer::json_error($rs, 401, 'invalid auth token');
        };
    
        $route_carte_id = $rq->getAttribute('route')->getArgument('id');
        $token_carte_id = $token->cid;
    
        if ($route_carte_id != $token_carte_id) {
            return Writer::json_error($rs, 401, 'Invalidation authorization', $this->$c['router']->pathFor('auth', ['id' => $route_carte_id]));
        }
    
        $rq = $rq->withAttribute('validated_carte_id', $token_carte_id);
    
        $rs = $next($rq, $rs);
    
        return $rs;
    
    }

}