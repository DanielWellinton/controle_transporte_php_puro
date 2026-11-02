<?php

use Services\RotaService;

class RotaController
{
    private RotaService $service;

    public function __construct()
    {
        $pdo = Database::conectar();
        $repository = new RotaRepository($pdo);
        $this->service = new RotaService($repository);
    }

    public function index(): void
    {
        $search = $_GET['search'] ?? null;
        $status = $_GET['status'] ?? null;
        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        $resultado = $this->service->listarPaginado($search, $status, $pagina, 10);

        view('rotas.index', [
            'rotas' => $resultado
        ]);
    }

    public function create(): void
    {
        view('rotas.create');
    }

    public function store(): void
    {
        try {
            $novoId = $this->service->cadastrar($_POST);

            $_SESSION['success'] = 'Rota cadastrada com sucesso! Você já pode vincular os pontos de parada.';
            header('Location: /rotas/edit?id=' . $novoId);
            exit;

        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /rotas/create');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao cadastrar rota.';
            header('Location: /rotas/create');
            exit;
        }
    }

    public function show(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $rota = $this->service->buscarComDetalhes($id);

        if (!$rota) {
            $_SESSION['error'] = 'Rota não encontrada.';
            header('Location: /rotas');
            exit;
        }

        view('rotas.show', [
            'rota' => $rota
        ]);
    }

    public function edit(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $rota = $this->service->buscarParaEdicao($id);

        if (!$rota) {
            $_SESSION['error'] = 'Rota não encontrada.';
            header('Location: /rotas');
            exit;
        }

        view('rotas.edit', [
            'rota' => $rota,
            'pontosDisponiveis' => $rota['pontosDisponiveis']
        ]);
    }

    public function update(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        try {
            $this->service->atualizar($id, $_POST);

            $_SESSION['success'] = 'Dados da rota atualizados com sucesso.';
            header('Location: /rotas/edit?id=' . $id);
            exit;

        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /rotas/edit?id=' . $id);
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar dados da rota.';
            header('Location: /rotas/edit?id=' . $id);
            exit;
        }
    }

    public function destroy(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        try {
            $this->service->excluir($id);
            $_SESSION['success'] = 'Rota removida com sucesso.';
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao remover rota.';
        }

        header('Location: /rotas');
        exit;
    }

    // --- Métodos da Pivot (Gerenciados no Edit) ---

    public function vincularPonto(): void
    {
        $rotaId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $pontoId = isset($_POST['ponto_de_parada_id']) ? (int) $_POST['ponto_de_parada_id'] : 0;

        try {
            $this->service->vincularPonto($rotaId, $pontoId);
            $_SESSION['success'] = 'Ponto de parada vinculado com sucesso.';
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao vincular ponto de parada.';
        }

        header('Location: /rotas/edit?id=' . $rotaId);
        exit;
    }

    public function desvincularPonto(): void
    {
        $rotaId = isset($_GET['rota_id']) ? (int) $_GET['rota_id'] : 0;
        $pontoId = isset($_GET['ponto_id']) ? (int) $_GET['ponto_id'] : 0;

        try {
            $this->service->desvincularPonto($rotaId, $pontoId);
            $_SESSION['success'] = 'Ponto de parada removido da rota.';
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao desvincular ponto de parada.';
        }

        header('Location: /rotas/edit?id=' . $rotaId);
        exit;
    }

    public function atualizarPontos(): void
    {
        $rotaId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $pontos = $_POST['pontos'] ?? [];

        try {
            $this->service->atualizarPontosPivot($rotaId, $pontos);
            $_SESSION['success'] = 'Ordem e status dos pontos atualizados com sucesso.';
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar pontos da rota.';
        }

        header('Location: /rotas/edit?id=' . $rotaId);
        exit;
    }

    public function autocomplete(): void
    {
        header('Content-Type: application/json');

        $term = $_GET['q'] ?? '';
        $currentRotaId = isset($_GET['current_id']) ? (int) $_GET['current_id'] : null;

        $rotas = $this->service->autocomplete($term, $currentRotaId);
        echo json_encode($rotas);
        exit;
    }
}