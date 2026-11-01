<?php

class PontoDeParadaController
{
    private PDO $pdo;
    private PontoDeParadaRepository $repository;

    public function __construct()
    {
        $this->pdo = Database::conectar();
        $this->repository = new PontoDeParadaRepository($this->pdo);
    }

    public function index(): void
    {
        $search = $_GET['search'] ?? null;
        $status = $_GET['status'] ?? null;
        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        $resultado = $this->repository->paginate(10, $pagina, $search, $status);

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
        $descricao = trim($_POST['descricao'] ?? '');
        $latitude  = trim($_POST['latitude'] ?? '');
        $longitude = trim($_POST['longitude'] ?? '');
        $ativo     = isset($_POST['ativo']) ? 1 : 0;

        if (empty($descricao) || empty($latitude) || empty($longitude)) {
            $_SESSION['error'] = 'Preencha a descrição e selecione a localização no mapa.';
            header('Location: /ponto_de_paradas/create');
            exit;
        }

        $this->repository->create([
            'descricao' => $descricao,
            'latitude'  => $latitude,
            'longitude' => $longitude,
            'ativo'     => $ativo
        ]);

        $_SESSION['success'] = 'Ponto de parada cadastrado com sucesso.';
        header('Location: /ponto_de_paradas');
        exit;
    }

    public function show(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $pontoDeParada = $this->repository->findById($id);

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
        $pontoDeParada = $this->repository->findById($id);

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
        $id        = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $descricao = trim($_POST['descricao'] ?? '');
        $latitude  = trim($_POST['latitude'] ?? '');
        $longitude = trim($_POST['longitude'] ?? '');
        $ativo     = isset($_POST['ativo']) ? 1 : 0;

        if ($id <= 0 || empty($descricao) || empty($latitude) || empty($longitude)) {
            $_SESSION['error'] = 'Dados inválidos para atualização.';
            header('Location: /ponto_de_paradas/edit?id=' . $id);
            exit;
        }

        $this->repository->update($id, [
            'descricao' => $descricao,
            'latitude'  => $latitude,
            'longitude' => $longitude,
            'ativo'     => $ativo
        ]);

        $_SESSION['success'] = 'Ponto de parada atualizado com sucesso.';
        header('Location: /ponto_de_paradas');
        exit;
    }

    public function destroy(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id > 0) {
            $this->repository->delete($id);
            $_SESSION['success'] = 'Ponto de parada removido com sucesso.';
        }

        header('Location: /ponto_de_paradas');
        exit;
    }

    public function autocomplete(): void
    {
        header('Content-Type: application/json');

        $term = trim($_GET['q'] ?? '');
        $rotaId = isset($_GET['rota_id']) ? (int) $_GET['rota_id'] : null;

        if (empty($term)) {
            echo json_encode([]);
            exit;
        }

        $pontos = $this->repository->autocomplete($term, $rotaId);
        echo json_encode($pontos);
        exit;
    }
}