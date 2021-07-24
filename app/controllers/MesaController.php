<?php
require_once './models/Mesa.php';
require_once './interfaces/IApiUsable.php';

use \App\Models\Mesa as Mesa;

class MesaController implements IApiUsable
{
	public function TraerUno($request, $response, $args)
    {
        $id = $args['id'];
    
        $venta = Mesa::find($id);
    
        $payload = json_encode($venta);
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function TraerTodos($request, $response, $args)
    {
        $lista = Mesa::all();
    
        $payload = json_encode($lista);
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        
        if (isset($parametros['codigo']) &&
            isset($parametros['estado']) &&
            isset($parametros['usos']))
        {
            $objeto = new Mesa();
    
            $objeto->codigo = $parametros['codigo'];
            $objeto->estado = $parametros['estado'];
            $objeto->usos = $parametros['usos'];
    

            // FOTO OPCIONAL


            try {
                $objeto->save();
    
                $payload = json_encode(array("mensaje" => "Alta exitosa."));
            }
            catch (Exception $e)
            {
                $payload = json_encode($e);
            }
        }
        else
        {
            $payload = json_encode(array("mensaje" => "Datos insuficientes."));
        }

        // Respuesta
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

	public function BorrarUno($request, $response, $args)
    {
        $id = $args['id'];
    
        $objeto = Mesa::find($id);
    
        $objeto->delete();
    
        $payload = json_encode(array("mensaje" => "Borrado exitoso."));
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function ModificarUno($request, $response, $args)
    {
        $id = $args['id'];

        $objeto = Mesa::find($id);

        if ($objeto == null)
        {
            $payload = json_encode(array("mensaje" => "Error: No existe."));
        }
        else
        {
            $parametros = $request->getParsedBody();
        
            if (isset($parametros['codigo']))
                $objeto->codigo = $parametros['codigo'];
            
            if (isset($parametros['estado']))
                $objeto->estado = $parametros['estado'];

            if (isset($parametros['usos']))
                $objeto->usos = $parametros['usos'];

            if (isset($parametros['foto']))
                $objeto->foto = $parametros['foto']; // HACER BACKUP

            try {
                $objeto->save();
    
                $payload = json_encode(array("mensaje" => "Modificacion exitosa."));
            }
            catch (Exception $e)
            {
                $payload = json_encode($e);
            }
        }

        // Respuesta
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
}