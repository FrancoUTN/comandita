<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

// reference the Dompdf namespace
use Dompdf\Dompdf;

class Generadora
{
    public function GenerarPdf(Request $request, RequestHandler $handler)
    {
        $response = $handler->handle($request);

        $body = $response->getBody();

        $json = json_decode($body, true);

        $response = new Response();

        $html = self::ListarVector($json);

        // instantiate and use the dompdf class
        $dompdf = new Dompdf();
        
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'landscape');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream();

        return $response;
    }
    
    public static function ListarVector($array)
    {
        $tabla = "
            <style>
                table, td, th{
                    font-size: 1vw;
                    border: 1px solid black;
                    border-collapse: collapse;
                    padding: 1.2vw;
                    text-align: center;
                }
            </style>
    
            <table>
                <thead>
                    <tr>";
    
        $claves = array_keys($array[0]);
    
        foreach ($claves as $clave)
        {
            $tabla .= "<th>$clave</th>";
        }
    
        $tabla .= "
                    </tr>
                </thead>
                <tbody>";
    
        foreach ($array as $index)
        {
            $tabla .= "<tr>";
    
            foreach ($index as $valor)
                $tabla .= "<td>" . $valor . "</td>";
    
            $tabla .= "</tr>";
        }
    
        $tabla .= "
                </tbody>
            </table>";
    
        return $tabla;
    }
}
