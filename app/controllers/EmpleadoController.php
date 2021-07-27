<?php
require_once './models/Empleado.php';
require_once './interfaces/IApiUsable.php';

use \App\Models\Empleado;

class EmpleadoController implements IApiUsable
{
	public function TraerUno($request, $response, $args)
    {
        $id = $args['id'];
    
        $objeto = Empleado::find($id);
    
        $payload = json_encode($objeto);
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function TraerTodos($request, $response, $args)
    {
        $lista = Empleado::all();
    
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
        $id_sector = $parametros['id_sector'];

        // Creación
        $objeto = new Empleado();

        $objeto->nombre = $nombre;
        $objeto->clave = $clave;
        $objeto->id_sector = $id_sector;
        $objeto->operaciones = 0; // Empieza sin operaciones

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
    
        $objeto = Empleado::find($id);
    
        $objeto->delete();
    
        $payload = json_encode(array("mensaje" => "Borrado exitoso."));
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function ModificarUno($request, $response, $args)
    {

    }
    
	public function Login($request, $response, $args)
    {
        $parsedBody = $request->getParsedBody();

        if (isset($parsedBody["nombre"]) && isset($parsedBody["clave"]))
        {
            $nombre = $parsedBody["nombre"];
            $clave = $parsedBody["clave"];
          
            $objeto = Empleado::where("nombre", $nombre)->where("clave", $clave)->first();

            if ($objeto == null)
            {
                $data = array("mensaje" => "ERROR: Usuario o clave incorrectos.");
                
                $status = 403;
            }
            else
            {
                $data = array("id" => $objeto->id, "id_sector" => $objeto->id_sector,);

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