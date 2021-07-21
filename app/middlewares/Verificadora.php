<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class Verificadora
{
    public function CrearJWT (Request $request, RequestHandler $handler)
    {
        $response = $handler->handle($request);
    
        $estado = $response->getStatusCode();
    
        if ($estado >= 200 && $estado <= 299)
        {
            $body = $response->getBody();
    
            $datos = json_decode($body, TRUE);
            
            $token = AutentificadorJWT::CrearToken($datos);
    
            $payload = json_encode(array('jwt' => $token));
    
            $response = new Response();
    
            $response->getBody()->write($payload);
        }
    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function VerificarAdmin (Request $request, RequestHandler $handler)
    {
        $response = new Response();

        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        try {
            $tipo = AutentificadorJWT::ObtenerData($token)->tipo;
        }
        catch (Exception $e) {
            return $response->withStatus(400);
        }

        if ($tipo != "admin")
        {
            return $response->withStatus(403);
        }
        
        $response = $handler->handle($request);

        return $response;
    }
    
    public function VerificarRegistro (Request $request, RequestHandler $handler)
    {

        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
    
        try {
            AutentificadorJWT::verificarToken($token);
        }
        catch (Exception $e) {
            $payload = json_encode(array('error' => $e->getMessage()));

            $response = new Response();
            
            $response->getBody()->write($payload);

            return $response->withStatus(403);
        }

        $response = $handler->handle($request);

        return $response;
    }
}