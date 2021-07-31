<?php
require_once './models/Encuesta.php';
require_once './interfaces/IApiUsable.php';

use \App\Models\Encuesta as Encuesta;

class EncuestaController implements IApiUsable
{
	public function TraerUno($request, $response, $args)
    {
        $id = $args['id'];
    
        $objeto = Encuesta::find($id);
    
        $payload = json_encode($objeto);
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function TraerTodos($request, $response, $args)
    {
        $lista = Encuesta::all();
    
        $payload = json_encode($lista);
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $codigo_mesa = $request->getAttribute('codigo_mesa');
        
        if (isset($codigo_mesa) &&
            isset($parametros['puntos_mesa']) &&
            isset($parametros['puntos_restaurante']) &&
            isset($parametros['puntos_mozo']) &&
            isset($parametros['puntos_cocinero'])
            )
        {
            $objeto = new Encuesta();
    
            $objeto->codigo_mesa = $codigo_mesa;
            $objeto->puntos_mesa = $parametros['puntos_mesa'];
            $objeto->puntos_restaurante = $parametros['puntos_restaurante'];
            $objeto->puntos_mozo = $parametros['puntos_mozo'];
            $objeto->puntos_cocinero = $parametros['puntos_cocinero'];

            if (isset($parametros['experiencia']))
                $objeto->experiencia = $parametros['experiencia']; // Por defecto: NULL
    
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

        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

	public function BorrarUno($request, $response, $args)
    {
        $id = $args['id'];
    
        $objeto = Encuesta::find($id);
    
        $objeto->delete();
    
        $payload = json_encode(array("mensaje" => "Borrado exitoso."));
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function ModificarUno($request, $response, $args)
    {
        $id = $args['id'];

        $objeto = Encuesta::find($id);

        if ($objeto == null)
        {
            $payload = json_encode(array("mensaje" => "Error: No existe."));
        }
        else
        {
            $parametros = $request->getParsedBody();
        
            if (isset($parametros['codigo_mesa']))
                $objeto->codigo_mesa = $parametros['codigo_mesa'];
            
            if (isset($parametros['puntos_mesa']))
                $objeto->puntos_mesa = $parametros['puntos_mesa'];

            if (isset($parametros['puntos_restaurante']))
                $objeto->puntos_restaurante = $parametros['puntos_restaurante'];

            if (isset($parametros['puntos_mozo']))
                $objeto->puntos_mozo = $parametros['puntos_mozo'];

            if (isset($parametros['puntos_cocinero']))
                $objeto->puntos_cocinero = $parametros['puntos_cocinero'];

            if (isset($parametros['experiencia']))
                $objeto->experiencia = $parametros['experiencia'];

            try {
                $objeto->save();
    
                $payload = json_encode(array("mensaje" => "Modificacion exitosa."));
            }
            catch (Exception $e)
            {
                $payload = json_encode($e);
            }
        }

        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
}