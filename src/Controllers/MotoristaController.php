<?php

use Services\MotoristaService;

class MotoristaController
{
    private MotoristaService $motoristaService;

    public function __construct()
    {
        $pdo = Database::conectar();
        $motoristaRepository = new MotoristaRepository($pdo);
        $this->motoristaService = new MotoristaService($motoristaRepository);
    }

    public function index(): void
    {
        $search = $_GET['search'] ?? null;
        $status = $_GET['status'] ?? null;
        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        $resultado = $this->motoristaService->listarPaginado($search, $status, $pagina, 10);

        view('motoristas.index', ['motoristas' => $resultado]);
    }

    public function create(): void
    {
        view('motoristas.create');
    }

    public function store(): void
    {
        try {
            $this->motoristaService->cadastrar($_POST);

            $_SESSION['success'] = 'Motorista cadastrado com sucesso.';
            header('Location: /motoristas');
            exit;

        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /motoristas/create');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao cadastrar motorista.';
            header('Location: /motoristas/create');
            exit;
        }
    }

    public function show(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $motorista = $this->motoristaService->buscarPorId($id);

        if (!$motorista) {
            $_SESSION['error'] = 'Motorista não encontrado.';
            header('Location: /motoristas');
            exit;
        }

        view('motoristas.show', ['motorista' => $motorista]);
    }

    public function edit(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $motorista = $this->motoristaService->buscarPorId($id);

        if (!$motorista) {
            $_SESSION['error'] = 'Motorista não encontrado.';
            header('Location: /motoristas');
            exit;
        }

        view('motoristas.edit', ['motorista' => $motorista]);
    }

    public function update(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        try {
            $this->motoristaService->atualizar($id, $_POST);

            $_SESSION['success'] = 'Motorista atualizado com sucesso.';
            header('Location: /motoristas');
            exit;

        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /motoristas/edit?id=' . $id);
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar motorista.';
            header('Location: /motoristas/edit?id=' . $id);
            exit;
        }
    }

    public function destroy(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        try {
            $this->motoristaService->excluir($id);
            $_SESSION['success'] = 'Motorista removido com sucesso.';
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao remover motorista.';
        }

        header('Location: /motoristas');
        exit;
    }

    public function autocomplete(): void
    {
        header('Content-Type: application/json');

        $term = $_GET['q'] ?? '';
        $currentMotoristaId = isset($_GET['current_id']) ? (int) $_GET['current_id'] : null;

        $dados = $this->motoristaService->autocomplete($term, $currentMotoristaId);

        echo json_encode($dados);
        exit;
    }
}