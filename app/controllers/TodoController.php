<?php
require_once './models/Mesa.php';

use \App\Models\Mesa;
// use \App\Models\Mesa as Mesa;

class TodoController
{
	public function TraerTodos($request, $response, $args)
    {
        // $lista = Mesa::join('estados_mesa', 'mesas.id_estado', '=', 'estados_mesa.id');
        $lista = Mesa::select("codigo", "estado")->join('estados_mesa', 'mesas.id_estado', '=', 'estados_mesa.id')->get();
        // $lista = Mesa::select("codigo", "estado")->join('estados_mesa', 'mesas.id_estado', '=', 'estados_mesa.id');
        // $lista = Mesa::get();
        // $lista = Mesa::select()->get();
    
        $payload = json_encode($lista);
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }
}