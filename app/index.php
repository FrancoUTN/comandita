<?php

date_default_timezone_set('America/Argentina/Buenos_Aires'); // fechaAlta correcta

use App\Models\Empleado;
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
// require_once './middlewares/Generadora.php';

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
    $group->get('/{id}', \EmpleadoController::class . ':TraerUno');
    $group->get('[/]', \EmpleadoController::class . ':TraerTodos');
    $group->post('[/]', \EmpleadoController::class . ':CargarUno');
    $group->delete('/{id}', \EmpleadoController::class . ':BorrarUno');
    $group->put('/{id}', \EmpleadoController::class . ':ModificarUno');

    $group->post('/login', \EmpleadoController::class . ':Login')->add(\Verificadora::class . ':CrearJWT');
});

$app->group('/socios', function (RouteCollectorProxy $group) {
    $group->get('/{id}', \SocioController::class . ':TraerUno');
    $group->get('[/]', \SocioController::class . ':TraerTodos');
    $group->post('[/]', \SocioController::class . ':CargarUno');
    $group->delete('/{id}', \SocioController::class . ':BorrarUno');
    $group->put('/{id}', \SocioController::class . ':ModificarUno');
});

$app->group('/clientes', function (RouteCollectorProxy $group) {
    $group->get('/{id}', \ClienteController::class . ':TraerUno');
    $group->get('[/]', \ClienteController::class . ':TraerTodos');
    $group->post('[/]', \ClienteController::class . ':CargarUno');
    $group->delete('/{id}', \ClienteController::class . ':BorrarUno');
    $group->put('/{id}', \ClienteController::class . ':ModificarUno');
});

$app->group('/productos', function (RouteCollectorProxy $group) {
    $group->get('/{id}', \ProductoController::class . ':TraerUno');
    $group->get('[/]', \ProductoController::class . ':TraerTodos');
    $group->post('[/]', \ProductoController::class . ':CargarUno');
    $group->delete('/{id}', \ProductoController::class . ':BorrarUno');
    $group->put('/{id}', \ProductoController::class . ':ModificarUno');
});

$app->group('/mesas', function (RouteCollectorProxy $group) {
    $group->get('/{id}', \MesaController::class . ':TraerUno');
    $group->get('[/]', \MesaController::class . ':TraerTodos');
    $group->post('[/]', \MesaController::class . ':CargarUno');
    $group->delete('/{id}', \MesaController::class . ':BorrarUno');
    $group->put('/{id}', \MesaController::class . ':ModificarUno');
    $group->put('/pagar/{codigo}', \MesaController::class . ':Pagar');
    $group->put('/cerrar/{codigo}', \MesaController::class . ':Cerrar');
});

$app->group('/pedidos', function (RouteCollectorProxy $group) {
    $group->get('/{codigo}', \PedidoController::class . ':TraerUno');
    // $group->get('/id/{id}', \PedidoController::class . ':TraerPorID');
    $group->get('[/]', \PedidoController::class . ':TraerTodos');
    $group->post('[/]', \PedidoController::class . ':CargarUno')->add(\Verificadora::class . ':VerificarMozo');
    $group->delete('/{codigo}', \PedidoController::class . ':BorrarUno');
    $group->put('/{codigo}', \PedidoController::class . ':ModificarUno');
    $group->put('/preparar/{codigo}', \PedidoController::class . ':Preparar');
    $group->put('/servir/{codigo}', \PedidoController::class . ':Servir');
    $group->put('/entregar/{codigo}', \PedidoController::class . ':Entregar');
});

$app->group('/todos', function (RouteCollectorProxy $group) {
    $group->get('[/]', \TodoController::class . ':TraerTodos');
    $group->get('/verpedido/{codigo_mesa}/{codigo_pedido}', \TodoController::class . ':VerPedido');
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
