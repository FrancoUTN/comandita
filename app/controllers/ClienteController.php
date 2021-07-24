<?php
require_once './models/Cliente.php';
require_once './interfaces/IApiUsable.php';

use \App\Models\Cliente as Cliente;

class ClienteController implements IApiUsable
{
	public function TraerUno($request, $response, $args)
    {
        $id = $args['id'];
    
        $venta = Cliente::find($id);
    
        $payload = json_encode($venta);
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function TraerTodos($request, $response, $args)
    {
        $lista = Cliente::all();
    
        $payload = json_encode($lista);
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function CargarUno($request, $response, $args)
    {
        // Parámetros
        $parametros = $request->getParsedBody();
    
        $nombre = $parametros['nombre'];
        $clave = $parametros['clave'];
        $codigo_mesa = $parametros['codigo_mesa'];

        // Creación
        $objeto = new Cliente();

        $objeto->nombre = $nombre;
        $objeto->clave = $clave;
        $objeto->codigo_mesa = $codigo_mesa;

        try {
            $objeto->save();

            $payload = json_encode(array("mensaje" => "Alta exitosa."));
        }
        catch (Exception $e)
        {
            $payload = json_encode($e);
        }

        // Respuesta
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

	public function BorrarUno($request, $response, $args)
    {
        $id = $args['id'];
    
        $objeto = Cliente::find($id);
    
        $objeto->delete();
    
        $payload = json_encode(array("mensaje" => "Borrado exitoso."));
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function ModificarUno($request, $response, $args)
    {

    }
}