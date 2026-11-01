<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../src/Helpers/view.php';
require_once __DIR__ . '/../config/Database.php';

require_once __DIR__ . '/../src/Repository/MotoristaRepository.php';
require_once __DIR__ . '/../src/Controller/MotoristaController.php';

require_once __DIR__ . '/../src/Repository/UsuarioRepository.php';
require_once __DIR__ . '/../src/Controller/UsuarioController.php';

require_once __DIR__ . '/../src/Repository/VeiculoRepository.php';
require_once __DIR__ . '/../src/Controller/VeiculoController.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');
if ($uri === '') $uri = '/';

switch ($uri) {
    // --- ROTAS DE MOTORISTAS ---
    case '/motoristas':
        (new MotoristaController())->index();
        break;
    case '/motoristas/create':
        (new MotoristaController())->create();
        break;
    case '/motoristas/store':
        (new MotoristaController())->store();
        break;
    case '/motoristas/show':
        (new MotoristaController())->show();
        break;
    case '/motoristas/edit':
        (new MotoristaController())->edit();
        break;
    case '/motoristas/update':
        (new MotoristaController())->update();
        break;
    case '/motoristas/delete':
        (new MotoristaController())->destroy();
        break;
    case '/motoristas/autocomplete':
        (new MotoristaController())->autocomplete();
        break;

    // --- ROTAS DE USUÁRIOS ---
    case '/usuarios':
        (new UsuarioController())->index();
        break;
    case '/usuarios/create':
        (new UsuarioController())->create();
        break;
    case '/usuarios/store':
        (new UsuarioController())->store();
        break;
    case '/usuarios/edit':
        (new UsuarioController())->edit();
        break;
    case '/usuarios/update':
        (new UsuarioController())->update();
        break;
    case '/usuarios/delete':
        (new UsuarioController())->destroy();
        break;
    case '/usuarios/autocomplete':
        (new UsuarioController())->autocomplete();
        break;
    
    // --- ROTAS DE VEICULOS ---
    case '/veiculos':
        (new VeiculoController())->index();
        break;
    case '/veiculos/create':
        (new VeiculoController())->create();
        break;
    case '/veiculos/store':
        (new VeiculoController())->store();
        break;
    case '/veiculos/show':
        (new VeiculoController())->show();
        break;
    case '/veiculos/edit':
        (new VeiculoController())->edit();
        break;
    case '/veiculos/update':
        (new VeiculoController())->update();
        break;
    case '/veiculos/delete':
        (new VeiculoController())->destroy();
        break;
    case '/veiculos/autocomplete':
        (new VeiculoController())->autocomplete();
        break;
    default:
        http_response_code(404);
        echo "Página não encontrada (404)";
        break;
}