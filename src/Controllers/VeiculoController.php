<?php

use Services\VeiculoService;

class VeiculoController
{
    private VeiculoService $veiculoService;

    public function __construct()
    {
        $pdo = Database::conectar();
        $veiculoRepository = new VeiculoRepository($pdo);
        $this->veiculoService = new VeiculoService($veiculoRepository);
    }

    public function index(): void
    {
        $search = $_GET['search'] ?? null;
        $status = $_GET['status'] ?? null;
        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        $resultado = $this->veiculoService->listarPaginado($search, $status, $pagina, 10);

        view('veiculos.index', [
            'veiculos' => $resultado
        ]);
    }

    public function create(): void
    {
        view('veiculos.create');
    }

    public function store(): void
    {
        try {
            $this->veiculoService->cadastrar($_POST);

            $_SESSION['success'] = 'Veículo cadastrado com sucesso.';
            header('Location: /veiculos');
            exit;

        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /veiculos/create');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao cadastrar veículo.';
            header('Location: /veiculos/create');
            exit;
        }
    }

    public function show(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $veiculo = $this->veiculoService->buscarPorId($id);

        if (!$veiculo) {
            $_SESSION['error'] = 'Veículo não encontrado.';
            header('Location: /veiculos');
            exit;
        }

        view('veiculos.show', [
            'veiculo' => $veiculo
        ]);
    }

    public function edit(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $veiculo = $this->veiculoService->buscarPorId($id);

        if (!$veiculo) {
            $_SESSION['error'] = 'Veículo não encontrado.';
            header('Location: /veiculos');
            exit;
        }

        view('veiculos.edit', [
            'veiculo' => $veiculo
        ]);
    }

    public function update(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        try {
            $this->veiculoService->atualizar($id, $_POST);

            $_SESSION['success'] = 'Veículo atualizado com sucesso.';
            header('Location: /veiculos');
            exit;

        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /veiculos/edit?id=' . $id);
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar veículo.';
            header('Location: /veiculos/edit?id=' . $id);
            exit;
        }
    }

    public function destroy(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        try {
            $this->veiculoService->excluir($id);
            $_SESSION['success'] = 'Veículo removido com sucesso.';
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao remover veículo.';
        }

        header('Location: /veiculos');
        exit;
    }

    public function autocomplete(): void
    {
        header('Content-Type: application/json');

        $term = $_GET['q'] ?? '';
        $currentVeiculoId = isset($_GET['current_id']) ? (int) $_GET['current_id'] : null;

        $veiculos = $this->veiculoService->autocomplete($term, $currentVeiculoId);
        echo json_encode($veiculos);
        exit;
    }
}