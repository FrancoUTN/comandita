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
    
    // Nuevos:
    public function VerificarMozo (Request $request, RequestHandler $handler)
    {
        $response = new Response();

        $header = $request->getHeaderLine('Authorization');

        if (empty($header))
        {
            $payload = json_encode(array("mensaje" => "ERROR: Sin token."));
        }
        else
        {
            $token = trim(explode("Bearer", $header)[1]);
    
            try {
                $data = AutentificadorJWT::ObtenerData($token);

                if ($data->id_sector == "5")
                {
                    $request = $request->withAttribute("id_mozo", $data->id);

                    return $handler->handle($request);
                }
                else
                {
                    $payload = json_encode(array("mensaje" => "ERROR: No es un mozo."));
                }
            }
            catch (Exception $e) {
                $payload = json_encode(array("mensaje" => $e->getMessage()));
            }           
        }

        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
    }

    public function VerificarEmpleado (Request $request, RequestHandler $handler)
    {
        $response = new Response();

        $header = $request->getHeaderLine('Authorization');

        if (empty($header))
        {
            $payload = json_encode(array("mensaje" => "ERROR: Sin token."));
        }
        else
        {
            $token = trim(explode("Bearer", $header)[1]);
    
            try {
                $data = AutentificadorJWT::ObtenerData($token);

                $id_sector = $data->id_sector;

                if ($id_sector >= 1 && $id_sector <= 4)
                {
                    $request = $request->withAttribute("id_empleado", $data->id);
                    $request = $request->withAttribute("id_sector", $id_sector);

                    return $handler->handle($request);
                }
                else
                {
                    $payload = json_encode(array("mensaje" => "ERROR: ES un mozo."));
                }
            }
            catch (Exception $e) {
                $payload = json_encode(array("mensaje" => $e->getMessage()));
            }           
        }

        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
    }
}