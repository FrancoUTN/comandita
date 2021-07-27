<?php
require_once './models/Mesa.php';
require_once './models/Pedido.php';

use \App\Models\Mesa;
use \App\Models\Pedido;

class TodoController
{
	public function TraerTodos($request, $response, $args)
    {
        $lista = Mesa::select("codigo", "estado")->join('estados_mesa', 'mesas.id_estado', '=', 'estados_mesa.id')->get();
    
        $payload = json_encode($lista);
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }
    
	public function VerPedido($request, $response, $args)
    {        
        $codigo_mesa = $args['codigo_mesa'];
        $codigo_pedido = $args['codigo_pedido'];
    
        $mesa = Mesa::where("codigo", $codigo_mesa)->first();
        $pedido = Pedido::where("codigo", $codigo_pedido)->first();
        
        if (empty($mesa))
        {
            $payload = json_encode(array("mensaje" => "Mesa no encontrada."));
        }
        else if (empty($pedido))
        {
            $payload = json_encode(array("mensaje" => "Pedido no encontrado."));
        }
        else if ($pedido->codigo_mesa != $codigo_mesa)
        {
            $payload = json_encode(array("mensaje" => "Error: el pedido no coincide con la mesa."));
        }
        else
        {
            $hora_estimada = strtotime($pedido->hora_estimada);

            $resta = $hora_estimada - time();
            
            $payload = json_encode($resta);
        }

        // Respuesta
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
}