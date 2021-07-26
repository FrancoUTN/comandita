<?php
require_once './models/Pedido.php';
require_once './models/Mesa.php';
require_once './interfaces/IApiUsable.php';

use \App\Models\Pedido;
use \App\Models\Mesa;
use Illuminate\Support\Facades\App;

class PedidoController implements IApiUsable
{
	public function TraerUno($request, $response, $args)
    {
        $id = $args['id'];
    
        $objeto = Pedido::find($id);
    
        $payload = json_encode($objeto);
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function TraerTodos($request, $response, $args)
    {
        $lista = Pedido::all();
    
        $payload = json_encode($lista);
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        
        if (isset($parametros['codigo']) && isset($parametros['id_producto']) &&
            isset($parametros['codigo_mesa']) && isset($parametros['cantidad']) &&
            isset($parametros['id_estado']) )
        {
            $mesa = Mesa::where("codigo", $parametros['codigo_mesa'])->first();

            if (empty($mesa))
            {
                $payload = json_encode(array("mensaje" => "Mesa no encontrada."));
            }
            else
            {

            
                    $mesa->id_estado = 1;
        
                    $mesa->save();





                $objeto = new Pedido();
        
                $objeto->codigo = $parametros['codigo'];
                $objeto->id_producto = $parametros['id_producto'];
                $objeto->codigo_mesa = $parametros['codigo_mesa'];
                $objeto->cantidad = $parametros['cantidad'];
                $objeto->id_estado = $parametros['id_estado'];


                // FOTO OPCIONAL

                try {
                    // $objeto->save();
        
                    $payload = json_encode(array("mensaje" => "Alta exitosa."));
                    $payload = json_encode($mesa);
                }
                catch (Exception $e)
                {
                    $payload = json_encode($e);
                }
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
    
        $objeto = Pedido::find($id);
    
        $objeto->delete();
    
        $payload = json_encode(array("mensaje" => "Borrado exitoso."));
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function ModificarUno($request, $response, $args)
    {
        $id = $args['id'];

        $objeto = Pedido::find($id);

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