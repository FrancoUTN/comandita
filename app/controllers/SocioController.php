<?php
require_once './models/Socio.php';
require_once './interfaces/IApiUsable.php';

use \App\Models\Socio;

class SocioController implements IApiUsable
{
	public function TraerUno($request, $response, $args)
    {
        $id = $args['id'];
    
        $objeto = Socio::find($id);
    
        $payload = json_encode($objeto);
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function TraerTodos($request, $response, $args)
    {
        $lista = Socio::all();
    
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

        // Creación
        $objeto = new Socio();

        $objeto->nombre = $nombre;
        $objeto->clave = $clave;

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
    
        $objeto = Socio::find($id);
    
        $objeto->delete();
    
        $payload = json_encode(array("mensaje" => "Borrado exitoso."));
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function ModificarUno($request, $response, $args)
    {
        $id = $args['id'];

        $objeto = Socio::find($id);

        if ($objeto == null)
        {
            $payload = json_encode(array("mensaje" => "Error: No existe."));
        }
        else
        {
            $parametros = $request->getParsedBody();
        
            if (isset($parametros['nombre']))
                $objeto->nombre = $parametros['nombre'];
            
            if (isset($parametros['clave']))
                $objeto->clave = $parametros['clave'];

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

	public function Login($request, $response, $args)
    {
        $parsedBody = $request->getParsedBody();

        if (isset($parsedBody["nombre"]) && isset($parsedBody["clave"]))
        {
            $nombre = $parsedBody["nombre"];
            $clave = $parsedBody["clave"];
          
            $objeto = Socio::where("nombre", $nombre)->where("clave", $clave)->first();

            if ($objeto == null)
            {
                $data = array("mensaje" => "ERROR: Usuario o clave incorrectos.");
                
                $status = 403;
            }
            else
            {
                $data = array("id" => $objeto->id, "perfil" => "socio");

                $status = 200;
            }
        }
        else
        {
            $data = array("mensaje" => "ERROR: Ingrese nombre y clave.");

            $status = 403;
        }
    
        $payload = json_encode($data);
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus($status);
    }
}