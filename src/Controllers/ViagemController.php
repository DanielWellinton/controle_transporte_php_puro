<?php

use Services\ViagemService;

class ViagemController
{
    private ViagemService $service;

    public function __construct()
    {
        $pdo = Database::conectar();
        $repository = new ViagemRepository($pdo);
        $this->service = new ViagemService($repository);
    }

    public function index(): void
    {
        $search = $_GET['search'] ?? null;
        $dataInicio = $_GET['data_inicio'] ?? null;
        $dataFim = $_GET['data_fim'] ?? null;
        $status = $_GET['status'] ?? null;
        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        $resultado = $this->service->listarPaginado($search, $dataInicio, $dataFim, $status, $pagina, 10);

        view('viagems.index', [
            'viagens' => $resultado
        ]);
    }

    public function create(): void
    {
        $formData = $this->service->obterDadosFormulario();

        view('viagems.create', [
            'rotas' => $formData['rotas'],
            'motoristas' => $formData['motoristas'],
            'veiculos' => $formData['veiculos'],
            'viagem' => null
        ]);
    }

    public function store(): void
    {
        try {
            $this->service->cadastrar($_POST);

            $_SESSION['success'] = 'Viagem agendada com sucesso!';
            header('Location: /viagens');
            exit;

        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /viagens/create');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao agendar viagem.';
            header('Location: /viagens/create');
            exit;
        }
    }

    public function show(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $viagem = $this->service->buscarPorId($id);

        if (!$viagem) {
            $_SESSION['error'] = 'Viagem não encontrada.';
            header('Location: /viagens');
            exit;
        }

        view('viagems.show', [
            'viagem' => $viagem
        ]);
    }

    public function edit(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $viagem = $this->service->buscarPorId($id);

        if (!$viagem) {
            $_SESSION['error'] = 'Viagem não encontrada.';
            header('Location: /viagens');
            exit;
        }

        $formData = $this->service->obterDadosFormulario();

        view('viagems.edit', [
            'viagem' => $viagem,
            'rotas' => $formData['rotas'],
            'motoristas' => $formData['motoristas'],
            'veiculos' => $formData['veiculos']
        ]);
    }

    public function update(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        try {
            $this->service->atualizar($id, $_POST);

            $_SESSION['success'] = 'Viagem atualizada com sucesso!';
            header('Location: /viagens');
            exit;

        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /viagens/edit?id=' . $id);
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar viagem.';
            header('Location: /viagens/edit?id=' . $id);
            exit;
        }
    }

    public function destroy(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        try {
            $this->service->excluir($id);
            $_SESSION['success'] = 'Viagem excluída com sucesso!';
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir viagem.';
        }

        header('Location: /viagens');
        exit;
    }

    public function duplicate(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        try {
            $viagem = $this->service->duplicar($id);
            $formData = $this->service->obterDadosFormulario();

            view('viagems.create', [
                'viagem' => $viagem,
                'rotas' => $formData['rotas'],
                'motoristas' => $formData['motoristas'],
                'veiculos' => $formData['veiculos']
            ]);

        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /viagens');
            exit;
        }
    }
}