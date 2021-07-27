<?php
require_once './models/Pedido.php';
require_once './models/Mesa.php';
require_once './models/Empleado.php';
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';

use \App\Models\Pedido;
use \App\Models\Mesa;
use \App\Models\Empleado;
use \App\Models\Producto;

class PedidoController implements IApiUsable
{
	public function TraerUno($request, $response, $args)
    {
        $codigo = $args['codigo'];
    
        $objeto = Pedido::where("codigo", $codigo)->first();
    
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

	public function TraerPendientes($request, $response, $args)
    {
        $id_sector = $request->getAttribute('id_sector');

        $pendientes = Pedido::where("id_estado", 1)->get();
    
        $productosSector = Producto::where("id_sector", $id_sector)->get();

        $lista = array();

        foreach ($pendientes as $pedido)
            foreach ($productosSector as $producto)
                if ($pedido->id_producto == $producto->id)
                    $lista[] = $pedido;

        $payload = json_encode($lista);
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $atributo = $request->getAttribute('id_mozo');
        
        if (isset($parametros['id_producto']) && isset($atributo) &&
            isset($parametros['codigo_mesa']) && isset($parametros['cantidad']) )
        {            
            $mozo = Empleado::where("id", $atributo)->first();
            $mesa = Mesa::where("codigo", $parametros['codigo_mesa'])->first();

            if (empty($mozo))
            {
                $payload = json_encode(array("mensaje" => "Mozo no encontrado."));
            }
            else if (empty($mesa))
            {
                $payload = json_encode(array("mensaje" => "Mesa no encontrada."));
            }
            else
            {
                $objeto = new Pedido();

                $used_symbols = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcefghijklmnopqrstuvwxyz';
 
                $codigo = substr(str_shuffle($used_symbols), 0, 5);
        
                $objeto->codigo = $codigo;
                $objeto->id_producto = $parametros['id_producto'];
                $objeto->codigo_mesa = $parametros['codigo_mesa'];
                $objeto->cantidad = $parametros['cantidad'];
                $objeto->id_estado = 1; // Empieza "pendiente"

                // FOTO OPCIONAL

                $mesa->id_estado = 1; // "con cliente esperando pedido"
                
                $mozo->operaciones++;

                try {
                    $objeto->save();
                    $mesa->save();
                    $mozo->save();
        
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
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function Preparar($request, $response, $args)
    {
        $codigo = $args['codigo'];    
        $objeto = Pedido::where("codigo", $codigo)->first();
    
        if ($objeto == null)
        {
            $payload = json_encode(array("mensaje" => "Error: No existe el pedido."));
        }
        else if ($objeto->id_estado != 1)
        {
            $payload = json_encode(array("mensaje" => "Error: No esta pendiente."));
        }
        else
        {
            $id_producto = $objeto->id_producto;
            $producto = Producto::where("id", $id_producto)->first();

            if ($producto == null)
            {
                $payload = json_encode(array("mensaje" => "Error: No existe el producto."));
            }
            else
            {
                $id_sector = $request->getAttribute('id_sector');

                if ($producto->id_sector != $id_sector)
                {
                    $payload = json_encode(array("mensaje" => "Error: Sector incorrecto."));
                }
                else
                {
                    $id_empleado = $request->getAttribute('id_empleado');
                    $empleado = Empleado::where("id", $id_empleado)->first();

                    if ($empleado == null)
                    {
                        $payload = json_encode(array("mensaje" => "Error: No existe el empleado."));
                    }
                    else
                    {
                        $parametros = $request->getParsedBody();
    
                        if (empty($parametros['tiempo_estimado']))
                        {
                            $payload = json_encode(array("mensaje" => "Error: Falta el tiempo estimado de preparacion."));
                        }
                        else
                        {
                            $objeto->hora_inicio = date('Y-m-d H:i:s');
                
                            $objeto->hora_estimada = date('Y-m-d H:i:s', time() + $parametros['tiempo_estimado']);
                
                            $objeto->id_estado += 1;

                            $objeto->id_empleado = $id_empleado;

                            $empleado->operaciones++;
                
                            try {
                                $objeto->save();
                                $empleado->save();
                    
                                $payload = json_encode(array("mensaje" => "Modificacion exitosa."));
                            }
                            catch (Exception $e)
                            {
                                $payload = json_encode($e);
                            }
                        }
                    }                    
                }
            }
        }        

        // Respuesta
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function Servir($request, $response, $args)
    {
        $codigo = $args['codigo'];
    
        $objeto = Pedido::where("codigo", $codigo)->first();

        if ($objeto == null)
        {
            $payload = json_encode(array("mensaje" => "Error: No existe."));
        }
        else if ($objeto->id_estado != 2)
        {
            $payload = json_encode(array("mensaje" => "Error: No esta en preparacion."));
        }
        else
        {            
            $id_empleado = $request->getAttribute('id_empleado');

            if ($objeto->id_empleado != $id_empleado)
            {
                $payload = json_encode(array("mensaje" => "Error: No coincide el empleado."));
            }
            else
            {
                $empleado = Empleado::where("id", $id_empleado)->first();

                $objeto->id_estado += 1;
                $empleado->operaciones++;
    
                try {
                    $objeto->save();
                    $empleado->save();
        
                    $payload = json_encode(array("mensaje" => "Modificacion exitosa."));
                }
                catch (Exception $e)
                {
                    $payload = json_encode($e);
                }
            }
        }        

        // Respuesta
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function Entregar($request, $response, $args)
    {
        $codigo = $args['codigo'];
    
        $objeto = Pedido::where("codigo", $codigo)->first();

        if ($objeto == null)
        {
            $payload = json_encode(array("mensaje" => "Error: No existe."));
        }
        else if ($objeto->id_estado != 3)
        {
            $payload = json_encode(array("mensaje" => "Error: No esta listo para servir."));
        }
        else
        {
            $atributo = $request->getAttribute('id_mozo');            
            $mozo = Empleado::where("id", $atributo)->first();

            if ($mozo == null)
            {
                $payload = json_encode(array("mensaje" => "Error: No existe el mozo."));
            }
            else
            {
                $objeto->id_estado += 1;
                $objeto->hora_entrega = date('Y-m-d H:i:s');
                
                $mesa = Mesa::where("codigo", $objeto->codigo_mesa)->first();
                $mesa->id_estado = 2;

                $mozo->operaciones++;
    
                try {
                    $objeto->save();
                    $mesa->save();
                    $mozo->save();
        
                    $payload = json_encode(array("mensaje" => "Modificacion exitosa."));
                }
                catch (Exception $e)
                {
                    $payload = json_encode($e);
                }
            }
        }

        // Respuesta
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
}