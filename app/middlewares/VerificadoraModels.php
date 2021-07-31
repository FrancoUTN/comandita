<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

require_once './controllers/ClienteController.php';
use \App\Models\Cliente;

class VerificadoraModels
{
    public function VerificarClienteParaEncuesta (Request $request, RequestHandler $handler)
    {
        $response = new Response();
        $flag = true;

        $id_usuario = $request->getAttribute('id_usuario');
        
        $objeto = Cliente::find($id_usuario);

        if (empty($objeto))
        {
            $retorno = array("mensaje" => "Error: No existe.");
        }
        else
        {
            $request = $request->withAttribute("codigo_mesa", $objeto->codigo_mesa);

            $response = $handler->handle($request);

            $flag = false;
        }

        if ($flag)
        {
            $response->getBody()->write(json_encode($retorno));
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

}