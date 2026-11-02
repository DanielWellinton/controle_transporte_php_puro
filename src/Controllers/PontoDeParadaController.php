<?php

use Services\PontoDeParadaService;

class PontoDeParadaController
{
    private PontoDeParadaService $service;

    public function __construct()
    {
        $pdo = Database::conectar();
        $repository = new PontoDeParadaRepository($pdo);
        $this->service = new PontoDeParadaService($repository);
    }

    public function index(): void
    {
        $search = $_GET['search'] ?? null;
        $status = $_GET['status'] ?? null;
        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        $resultado = $this->service->listarPaginado($search, $status, $pagina, 10);

        view('ponto_de_paradas.index', [
            'pontoDeParada' => $resultado
        ]);
    }

    public function create(): void
    {
        view('ponto_de_paradas.create');
    }

    public function store(): void
    {
        try {
            $this->service->cadastrar($_POST);

            $_SESSION['success'] = 'Ponto de parada cadastrado com sucesso.';
            header('Location: /ponto_de_paradas');
            exit;

        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /ponto_de_paradas/create');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao cadastrar ponto de parada.';
            header('Location: /ponto_de_paradas/create');
            exit;
        }
    }

    public function show(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $pontoDeParada = $this->service->buscarPorId($id);

        if (!$pontoDeParada) {
            $_SESSION['error'] = 'Ponto de parada não encontrado.';
            header('Location: /ponto_de_paradas');
            exit;
        }

        view('ponto_de_paradas.show', [
            'pontoDeParada' => $pontoDeParada
        ]);
    }

    public function edit(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $pontoDeParada = $this->service->buscarPorId($id);

        if (!$pontoDeParada) {
            $_SESSION['error'] = 'Ponto de parada não encontrado.';
            header('Location: /ponto_de_paradas');
            exit;
        }

        view('ponto_de_paradas.edit', [
            'pontoDeParada' => $pontoDeParada
        ]);
    }

    public function update(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        try {
            $this->service->atualizar($id, $_POST);

            $_SESSION['success'] = 'Ponto de parada atualizado com sucesso.';
            header('Location: /ponto_de_paradas');
            exit;

        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /ponto_de_paradas/edit?id=' . $id);
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar ponto de parada.';
            header('Location: /ponto_de_paradas/edit?id=' . $id);
            exit;
        }
    }

    public function destroy(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        try {
            $this->service->excluir($id);
            $_SESSION['success'] = 'Ponto de parada removido com sucesso.';
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao remover ponto de parada.';
        }

        header('Location: /ponto_de_paradas');
        exit;
    }

    public function autocomplete(): void
    {
        header('Content-Type: application/json');

        $term = $_GET['q'] ?? '';
        $rotaId = isset($_GET['rota_id']) ? (int) $_GET['rota_id'] : null;

        $pontos = $this->service->autocomplete($term, $rotaId);
        echo json_encode($pontos);
        exit;
    }
}