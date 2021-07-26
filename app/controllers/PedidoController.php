<?php
require_once './models/Pedido.php';
require_once './models/Mesa.php';
require_once './interfaces/IApiUsable.php';

use \App\Models\Pedido;
use \App\Models\Mesa;

class PedidoController implements IApiUsable
{
	public function TraerUno($request, $response, $args)
    {
        $codigo = $args['codigo'];
    
        $objeto = Pedido::where("codigo", $codigo)->first();;
    
        $payload = json_encode($objeto);
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }

	// public function TraerPorID($request, $response, $args)
    // {
    //     $id = $args['id'];
    
    //     $objeto = Pedido::find($id);
    
    //     $payload = json_encode($objeto);
    
    //     $response->getBody()->write($payload);
    
    //     return $response->withHeader('Content-Type', 'application/json');
    // }

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
        
        if (isset($parametros['id_producto']) &&
            isset($parametros['codigo_mesa']) && isset($parametros['cantidad']) )
        {
            $mesa = Mesa::where("codigo", $parametros['codigo_mesa'])->first();

            if (empty($mesa))
            {
                $payload = json_encode(array("mensaje" => "Mesa no encontrada."));
            }
            else
            {
                $objeto = new Pedido();

                $used_symbols = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcefghijklmnopqrstuvwxyz';
 
                $codigo = substr(str_shuffle($used_symbols), 0, 5);
        
                // $objeto->codigo = $parametros['codigo'];
                $objeto->codigo = $codigo;
                $objeto->id_producto = $parametros['id_producto'];
                $objeto->codigo_mesa = $parametros['codigo_mesa'];
                $objeto->cantidad = $parametros['cantidad'];
                $objeto->id_estado = 1; // Empieza "pendiente"

                // FOTO OPCIONAL

                $mesa->id_estado = 1; // "con cliente esperando pedido"

                try {
                    $objeto->save();
                    $mesa->save();
        
                    $data = array("mensaje" => "Alta exitosa.", "codigo" => $codigo);

                    $payload = json_encode($data);
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
        $codigo = $args['codigo'];
    
        $objeto = Pedido::where("codigo", $codigo)->first();;
    
        $objeto->delete();
    
        $payload = json_encode(array("mensaje" => "Borrado exitoso."));
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function ModificarUno($request, $response, $args)
    {
        $codigo = $args['codigo'];
    
        $objeto = Pedido::where("codigo", $codigo)->first();;

        if ($objeto == null)
        {
            $payload = json_encode(array("mensaje" => "Error: No existe."));
        }
        else
        {
            $parametros = $request->getParsedBody();

            if (isset($parametros['id_estado']))
            {
                $id_estado = $parametros['id_estado'];

                $objeto->id_estado = $id_estado;

                if ($id_estado == 2)
                {
                    $objeto->hora_inicio = date('Y-m-d H:i:s');

                    if (isset($parametros['tiempo_estimado']))
                    {
                        $objeto->hora_estimada = date('Y-m-d H:i:s', time() + $parametros['tiempo_estimado']);
                    }
                    else
                    {
                        $objeto->hora_estimada = date('Y-m-d H:i:s', time() + 30);
                    }
                }
            }

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