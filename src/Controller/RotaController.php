<?php

class RotaController
{
    private PDO $pdo;
    private RotaRepository $repository;

    public function __construct()
    {
        $this->pdo = Database::conectar();
        $this->repository = new RotaRepository($this->pdo);
    }

    public function index(): void
    {
        $search = $_GET['search'] ?? null;
        $status = $_GET['status'] ?? null;
        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        $resultado = $this->repository->paginate(10, $pagina, $search, $status);

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
        $descricao = trim($_POST['descricao'] ?? '');
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        if (empty($descricao)) {
            $_SESSION['error'] = 'A descrição da rota é obrigatória.';
            header('Location: /rotas/create');
            exit;
        }

        $novoId = $this->repository->create([
            'descricao' => $descricao,
            'ativo' => $ativo
        ]);

        $_SESSION['success'] = 'Rota cadastrada com sucesso! Você já pode vincular os pontos de parada.';
        header('Location: /rotas/edit?id=' . $novoId);
        exit;
    }

    public function show(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $rota = $this->repository->findById($id);

        if (!$rota) {
            $_SESSION['error'] = 'Rota não encontrada.';
            header('Location: /rotas');
            exit;
        }

        $rota['pontosDeParada'] = $this->repository->getPontosVinculados($id);

        view('rotas.show', [
            'rota' => $rota
        ]);
    }

    public function edit(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $rota = $this->repository->findById($id);

        if (!$rota) {
            $_SESSION['error'] = 'Rota não encontrada.';
            header('Location: /rotas');
            exit;
        }

        $rota['pontosDeParada'] = $this->repository->getPontosVinculados($id);
        $pontosDisponiveis = $this->repository->getPontosDisponiveis($id);

        view('rotas.edit', [
            'rota' => $rota,
            'pontosDisponiveis' => $pontosDisponiveis
        ]);
    }

    public function update(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $descricao = trim($_POST['descricao'] ?? '');
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        if ($id <= 0 || empty($descricao)) {
            $_SESSION['error'] = 'Dados inválidos para atualização.';
            header('Location: /rotas/edit?id=' . $id);
            exit;
        }

        $this->repository->update($id, [
            'descricao' => $descricao,
            'ativo' => $ativo
        ]);

        $_SESSION['success'] = 'Dados da rota atualizados com sucesso.';
        header('Location: /rotas/edit?id=' . $id);
        exit;
    }

    public function destroy(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id > 0) {
            $this->repository->delete($id);
            $_SESSION['success'] = 'Rota removida com sucesso.';
        }

        header('Location: /rotas');
        exit;
    }

    // --- Métodos da Pivot (Gerenciados no Edit) ---

    public function vincularPonto(): void
    {
        $rotaId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $pontoId = isset($_POST['ponto_de_parada_id']) ? (int) $_POST['ponto_de_parada_id'] : 0;

        if ($rotaId > 0 && $pontoId > 0) {
            $this->repository->vincularPonto($rotaId, $pontoId);
            $_SESSION['success'] = 'Ponto de parada vinculado com sucesso.';
        }

        header('Location: /rotas/edit?id=' . $rotaId);
        exit;
    }

    public function desvincularPonto(): void
    {
        $rotaId = isset($_GET['rota_id']) ? (int) $_GET['rota_id'] : 0;
        $pontoId = isset($_GET['ponto_id']) ? (int) $_GET['ponto_id'] : 0;

        if ($rotaId > 0 && $pontoId > 0) {
            $this->repository->desvincularPonto($rotaId, $pontoId);
            $_SESSION['success'] = 'Ponto de parada removido da rota.';
        }

        header('Location: /rotas/edit?id=' . $rotaId);
        exit;
    }

    public function atualizarPontos(): void
    {
        $rotaId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $pontos = $_POST['pontos'] ?? [];

        if ($rotaId > 0 && !empty($pontos)) {
            $this->repository->atualizarPontosPivot($rotaId, $pontos);
            $_SESSION['success'] = 'Ordem e status dos pontos atualizados com sucesso.';
        }

        header('Location: /rotas/edit?id=' . $rotaId);
        exit;
    }

    public function autocomplete(): void
    {
        header('Content-Type: application/json');

        $term = trim($_GET['q'] ?? '');
        $currentRotaId = isset($_GET['current_id']) ? (int) $_GET['current_id'] : null;

        if (empty($term)) {
            echo json_encode([]);
            exit;
        }

        $rotas = $this->repository->autocomplete($term, $currentRotaId);
        echo json_encode($rotas);
        exit;
    }
}