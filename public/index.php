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

require_once __DIR__ . '/../src/Repository/PontoDeParadaRepository.php';
require_once __DIR__ . '/../src/Controller/PontoDeParadaController.php';

require_once __DIR__ . '/../src/Repository/RotaRepository.php';
require_once __DIR__ . '/../src/Controller/RotaController.php';

require_once __DIR__ . '/../src/Repository/ViagemRepository.php';
require_once __DIR__ . '/../src/Controller/ViagemController.php';

require_once __DIR__ . '/../src/Repository/PortalMotoristaRepository.php';
require_once __DIR__ . '/../src/Controller/PortalMotoristaController.php';

require_once __DIR__ . '/../src/Repository/PassageiroRepository.php';
require_once __DIR__ . '/../src/Controller/PassageiroController.php';

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

    // --- ROTAS DE PONTO DE PARADAS ---
    case '/ponto_de_paradas':
        (new PontoDeParadaController())->index();
        break;
    case '/ponto_de_paradas/create':
        (new PontoDeParadaController())->create();
        break;
    case '/ponto_de_paradas/store':
        (new PontoDeParadaController())->store();
        break;
    case '/ponto_de_paradas/show':
        (new PontoDeParadaController())->show();
        break;
    case '/ponto_de_paradas/edit':
        (new PontoDeParadaController())->edit();
        break;
    case '/ponto_de_paradas/update':
        (new PontoDeParadaController())->update();
        break;
    case '/ponto_de_paradas/delete':
        (new PontoDeParadaController())->destroy();
        break;
    case '/ponto_de_paradas/autocomplete':
        (new PontoDeParadaController())->autocomplete();
        break;

    // --- ROTAS DE ROTAS ---
    case '/rotas':
        (new RotaController())->index();
        break;
    case '/rotas/create':
        (new RotaController())->create();
        break;
    case '/rotas/store':
        (new RotaController())->store();
        break;
    case '/rotas/show':
        (new RotaController())->show();
        break;
    case '/rotas/edit':
        (new RotaController())->edit();
        break;
    case '/rotas/update':
        (new RotaController())->update();
        break;
    case '/rotas/delete':
        (new RotaController())->destroy();
        break;
    case '/rotas/vincular-ponto':
        (new RotaController())->vincularPonto();
        break;
    case '/rotas/desvincular-ponto':
        (new RotaController())->desvincularPonto();
        break;
    case '/rotas/atualizar-pontos':
        (new RotaController())->atualizarPontos();
        break;
    case '/rotas/autocomplete':
        (new RotaController())->autocomplete();
        break;
    
    // --- ROTAS DE VIAGENS ---
    case '/viagens':
        (new ViagemController())->index();
        break;
    case '/viagens/create':
        (new ViagemController())->create();
        break;
    case '/viagens/store':
        (new ViagemController())->store();
        break;
    case '/viagens/show':
        (new ViagemController())->show();
        break;
    case '/viagens/edit':
        (new ViagemController())->edit();
        break;
    case '/viagens/update':
        (new ViagemController())->update();
        break;
    case '/viagens/delete':
        (new ViagemController())->destroy();
        break;
    case '/viagens/duplicate':
        (new ViagemController())->duplicate();
        break;

    // --- ROTAS DO PORTAL MOTORISTA ---
    case '/portal-motorista':
        (new PortalMotoristaController())->index();
        break;
    case '/portal-motorista/historico':
        (new PortalMotoristaController())->historico();
        break;
    case '/portal-motorista/show':
        (new PortalMotoristaController())->show();
        break;
    case '/portal-motorista/iniciar':
        (new PortalMotoristaController())->iniciar();
        break;
    case '/portal-motorista/finalizar':
        (new PortalMotoristaController())->finalizar();
        break;
    case '/portal-motorista/imprimir':
        (new PortalMotoristaController())->imprimir();
        break;

    // --- ROTAS DE PASSAGEIROS ---
    case '/dashboard':
        (new PassageiroController())->index();
        break;
    case '/passageiros/selecionar-pontos':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new PassageiroController())->salvarPontos();
        } else {
            (new PassageiroController())->selecionarPontos();
        }
        break;
    case '/passageiros/cancelar':
        (new PassageiroController())->cancelar();
        break;
    case '/passageiros/historico':
        (new PassageiroController())->historico();
        break;

    default:
        http_response_code(404);
        echo "Página não encontrada (404)";
        break;
}