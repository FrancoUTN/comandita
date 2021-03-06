<?php
require_once './models/Mesa.php';
require_once './models/Factura.php';
require_once './models/Encuesta.php';
require_once './interfaces/IApiUsable.php';

use \App\Models\Mesa;
use \App\Models\Factura as Factura;
use \App\Models\Encuesta as Encuesta;

class MesaController implements IApiUsable
{
	public function TraerUno($request, $response, $args)
    {
        $id = $args['id'];
    
        $objeto = Mesa::find($id);
    
        $payload = json_encode($objeto);
    
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
        
        if (isset($parametros['codigo']) && isset($parametros['id_estado']))
        {
            $objeto = new Mesa();
    
            $objeto->codigo = $parametros['codigo'];
            $objeto->id_estado = $parametros['id_estado'];
            $objeto->usos = 0; // Empieza sin usos
    

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
            
            if (isset($parametros['id_estado']))
                $objeto->id_estado = $parametros['id_estado'];

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

	public function Pagar($request, $response, $args)
    {
        $codigo = $args['codigo'];

        $objeto = Mesa::where("codigo", $codigo)->first();

        if ($objeto == null)
        {
            $payload = json_encode(array("mensaje" => "Error: No existe."));
        }
        else if ($objeto->id_estado != 2)
        {
            $payload = json_encode(array("mensaje" => "Error: No esta listo para pagar."));
        }
        else
        {
            $objeto->id_estado = 3;

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

	public function Cerrar($request, $response, $args)
    {
        $codigo = $args['codigo'];

        $objeto = Mesa::where("codigo", $codigo)->first();

        if ($objeto == null)
        {
            $payload = json_encode(array("mensaje" => "Error: No existe."));
        }
        else if ($objeto->id_estado != 3)
        {
            $payload = json_encode(array("mensaje" => "Error: Aun no ha pagado."));
        }
        else
        {
            $objeto->id_estado = 4;
            $objeto->usos++;
            $objeto->foto = NULL;

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

    public function MasUsada($request, $response, $args)
    {
        $max = Mesa::max('usos');

        $data = Mesa::select("codigo")->where("usos", $max)->get();
    
        $payload = json_encode($data);
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function MenosUsada($request, $response, $args)
    {
        $min = Mesa::min('usos');

        $data = Mesa::select("codigo")->where("usos", $min)->get();
    
        $payload = json_encode($data);
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function MasFacturada($request, $response, $args)
    {
        $lista = Factura::selectRaw("codigo_mesa, SUM(importe) as suma")->groupBy("codigo_mesa")->get();

        $max = 0;

        foreach ($lista as $mesa)
        {
            if ($mesa->suma > $max)
            {
                $max = $mesa->suma;
                $objeto = $mesa;
            }
        }

        $payload = json_encode($objeto);
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function MenosFacturada($request, $response, $args)
    {
        $lista = Factura::selectRaw("codigo_mesa, SUM(importe) as suma")->groupBy("codigo_mesa")->get();

        $min = 999999;

        foreach ($lista as $mesa)
        {
            if ($mesa->suma < $min)
            {
                $min = $mesa->suma;
                $objeto = $mesa;
            }
        }

        $payload = json_encode($objeto);
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function MayorFactura($request, $response, $args)
    {
        $max = Factura::max('importe');

        $lista = Factura::select("codigo_mesa")->where("importe", $max)->get();
        
        $payload = json_encode($lista);
        
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function MenorFactura($request, $response, $args)
    {
        $min = Factura::min('importe');
        
        $lista = Factura::select("codigo_mesa")->where("importe", $min)->get();
        
        $payload = json_encode($lista);
        
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }
    
	public function VerMejoresComentarios($request, $response, $args)
    {
        if (empty($args['codigo']))
        {
            $lista = "No existe la mesa.";
        }
        else
        {
            $lista = Encuesta::select("experiencia", "puntos_mesa")->where("codigo_mesa", $args['codigo'])
                                                    ->orderby("puntos_mesa", "DESC")->get();
        }
        
        $payload = json_encode($lista);
        
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function VerPeoresComentarios($request, $response, $args)
    {
        if (empty($args['codigo']))
        {
            $lista = "No existe la mesa.";
        }
        else
        {
            $lista = Encuesta::select("experiencia", "puntos_mesa")->where("codigo_mesa", $args['codigo'])
                                                    ->orderby("puntos_mesa")->get();
        }
        
        $payload = json_encode($lista);
        
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }
    
	public function VerFacturacionEntreFechas($request, $response, $args)
    {
        $from = $args['fecha1'];
        $to = $args['fecha2'];

        $lista = Factura::selectRaw("codigo_mesa, SUM(importe) as suma")
                        ->whereBetween('fechaAlta', [$from, $to])
                        ->groupBy("codigo_mesa")->get();
        
        $payload = json_encode($lista);
        
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }
}