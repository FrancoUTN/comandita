<?php

date_default_timezone_set('America/Argentina/Buenos_Aires'); // fechaAlta correcta

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;
use Slim\Psr7\Response;
use Slim\Routing\RouteCollectorProxy;
use Illuminate\Database\Capsule\Manager as Capsule;

require __DIR__ . '/../vendor/autoload.php';

// Middlewares
require_once './middlewares/AutentificadorJWT.php';
require_once './middlewares/Verificadora.php';
require_once './middlewares/Generadora.php';

// Controllers
require_once './controllers/EmpleadoController.php';
require_once './controllers/SocioController.php';
require_once './controllers/ClienteController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/MesaController.php';
require_once './controllers/PedidoController.php';
// require_once './controllers/SectorController.php';
// require_once './controllers/FacturaController.php';
require_once './controllers/TodoController.php'; // Testing


// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// Eloquent
$container=$app->getContainer();

$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $_ENV['MYSQL_HOST'],
    'database'  => $_ENV['MYSQL_DB'], // Definir
    'username'  => $_ENV['MYSQL_USER'],
    'password'  => $_ENV['MYSQL_PASS'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

// Routes
$app->get('[/]', function (Request $request, Response $response) {    
    $response->getBody()->write("La Comanda");
    return $response;
});

$app->group('/empleados', function (RouteCollectorProxy $group) {

    $group->get('/{id}', \EmpleadoController::class . ':TraerUno')
        ->add(\Verificadora::class . ':VerificarSocio');

    $group->get('[/]', \EmpleadoController::class . ':TraerTodos')
        ->add(\Verificadora::class . ':VerificarSocio');

    $group->post('[/]', \EmpleadoController::class . ':CargarUno')
        ->add(\Verificadora::class . ':VerificarSocio');

    $group->delete('/{id}', \EmpleadoController::class . ':BorrarUno')
        ->add(\Verificadora::class . ':VerificarSocio');

    $group->put('/{id}', \EmpleadoController::class . ':ModificarUno')
        ->add(\Verificadora::class . ':VerificarSocio');

    $group->post('/login', \EmpleadoController::class . ':Login')
        ->add(\Verificadora::class . ':CrearJWT');
});

$app->group('/socios', function (RouteCollectorProxy $group) {

    $group->get('/{id}', \SocioController::class . ':TraerUno')
        ->add(\Verificadora::class . ':VerificarSocio');
        
    $group->get('[/]', \SocioController::class . ':TraerTodos')
        ->add(\Verificadora::class . ':VerificarSocio');

    $group->post('[/]', \SocioController::class . ':CargarUno')
        ->add(\Verificadora::class . ':VerificarSocio');

    $group->delete('/{id}', \SocioController::class . ':BorrarUno')
        ->add(\Verificadora::class . ':VerificarSocio');

    $group->put('/{id}', \SocioController::class . ':ModificarUno')
        ->add(\Verificadora::class . ':VerificarSocio');

    $group->post('/login', \SocioController::class . ':Login')
        ->add(\Verificadora::class . ':CrearJWT');
});

$app->group('/clientes', function (RouteCollectorProxy $group) {

    $group->get('/{id}', \ClienteController::class . ':TraerUno')
        ->add(\Verificadora::class . ':VerificarSocio');

    $group->get('[/]', \ClienteController::class . ':TraerTodos')
        ->add(\Verificadora::class . ':VerificarSocio');

    $group->post('[/]', \ClienteController::class . ':CargarUno');

    $group->delete('/{id}', \ClienteController::class . ':BorrarUno')
        ->add(\Verificadora::class . ':VerificarSocio');

    $group->put('/{id}', \ClienteController::class . ':ModificarUno')
        ->add(\Verificadora::class . ':VerificarSocio');

    $group->post('/login', \ClienteController::class . ':Login')
        ->add(\Verificadora::class . ':CrearJWT');
});

$app->group('/productos', function (RouteCollectorProxy $group) {

    $group->get('/id/{id}', \ProductoController::class . ':TraerUno');

    $group->get('[/]', \ProductoController::class . ':TraerTodos');

    $group->get('/pdf', \ProductoController::class . ':TraerTodos')
        ->add(\Generadora::class . ':GenerarPdf');

    $group->post('[/]', \ProductoController::class . ':CargarUno')
        ->add(\Verificadora::class . ':VerificarSocio');

    $group->delete('/{id}', \ProductoController::class . ':BorrarUno')
        ->add(\Verificadora::class . ':VerificarSocio');

    $group->put('/{id}', \ProductoController::class . ':ModificarUno')
        ->add(\Verificadora::class . ':VerificarSocio');
});

$app->group('/mesas', function (RouteCollectorProxy $group) {

    $group->get('/{id}', \MesaController::class . ':TraerUno')
        ->add(\Verificadora::class . ':VerificarSocio');

    $group->get('[/]', \MesaController::class . ':TraerTodos')
        ->add(\Verificadora::class . ':VerificarSocio');

    $group->post('[/]', \MesaController::class . ':CargarUno')
        ->add(\Verificadora::class . ':VerificarSocio');

    $group->delete('/{id}', \MesaController::class . ':BorrarUno')
        ->add(\Verificadora::class . ':VerificarSocio');

    $group->put('/{id}', \MesaController::class . ':ModificarUno')
        ->add(\Verificadora::class . ':VerificarSocio');

    $group->put('/pagar/{codigo}', \MesaController::class . ':Pagar')
        ->add(\Verificadora::class . ':VerificarMozo');

    $group->put('/cerrar/{codigo}', \MesaController::class . ':Cerrar')
        ->add(\Verificadora::class . ':VerificarSocio');
});

$app->group('/pedidos', function (RouteCollectorProxy $group) {

    $group->get('/codigo/{codigo}', \PedidoController::class . ':TraerUno')
        ->add(\Verificadora::class . ':VerificarSocio');
    
    $group->get('[/]', \PedidoController::class . ':TraerTodos')
        ->add(\Verificadora::class . ':VerificarSocio');

    $group->get('/pdf', \PedidoController::class . ':TraerTodos')
        ->add(\Generadora::class . ':GenerarPdf')
        ->add(\Verificadora::class . ':VerificarSocio');

    $group->get('/pendientes', \PedidoController::class . ':TraerPendientes')
        ->add(\Verificadora::class . ':VerificarEmpleado');

    $group->post('[/]', \PedidoController::class . ':CargarUno')
        ->add(\Verificadora::class . ':VerificarMozo');

    $group->delete('/{codigo}', \PedidoController::class . ':BorrarUno')
        ->add(\Verificadora::class . ':VerificarMozo');

    $group->put('/{codigo}', \PedidoController::class . ':ModificarUno')
        ->add(\Verificadora::class . ':VerificarMozo');

    $group->put('/preparar/{codigo}', \PedidoController::class . ':Preparar')
        ->add(\Verificadora::class . ':VerificarEmpleado');

    $group->put('/servir/{codigo}', \PedidoController::class . ':Servir')
        ->add(\Verificadora::class . ':VerificarEmpleado');

    $group->put('/entregar/{codigo}', \PedidoController::class . ':Entregar')
        ->add(\Verificadora::class . ':VerificarMozo');
});

$app->group('/todos', function (RouteCollectorProxy $group) {

    $group->get('[/]', \TodoController::class . ':TraerTodos')
        ->add(\Verificadora::class . ':VerificarSocio');

    $group->get('/verpedido/{codigo_mesa}/{codigo_pedido}', \TodoController::class . ':VerPedido');
        // ->add(\Verificadora::class . ':VerificarCliente');
});

// $app->group('/sectores', function (RouteCollectorProxy $group) {
//     $group->get('/{id}', \SectorController::class . ':TraerUno');
//     $group->get('[/]', \SectorController::class . ':TraerTodos');
//     $group->post('[/]', \SectorController::class . ':CargarUno');
//     $group->delete('/{id}', \SectorController::class . ':BorrarUno');
//     $group->put('/{id}', \SectorController::class . ':ModificarUno');
// });

// $app->group('/facturas', function (RouteCollectorProxy $group) {
//     $group->get('/{id}', \FacturaController::class . ':TraerUno');
//     $group->get('[/]', \FacturaController::class . ':TraerTodos');
//     $group->post('[/]', \FacturaController::class . ':CargarUno');
//     $group->delete('/{id}', \FacturaController::class . ':BorrarUno');
//     $group->put('/{id}', \FacturaController::class . ':ModificarUno');
// });


// Run app
$app->run();
