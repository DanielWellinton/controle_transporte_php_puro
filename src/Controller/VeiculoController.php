<?php

class VeiculoController
{
    private PDO $pdo;
    private VeiculoRepository $veiculoRepository;

    public function __construct()
    {
        $this->pdo = Database::conectar();
        $this->veiculoRepository = new VeiculoRepository($this->pdo);
    }

    public function index(): void
    {
        $search = $_GET['search'] ?? null;
        $status = $_GET['status'] ?? null;
        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        $resultado = $this->veiculoRepository->paginate(10, $pagina, $search, $status);

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
        $descricao = $_POST['descricao'] ?? '';
        $placa = $_POST['placa'] ?? '';
        $numeroPassageiros = $_POST['numero_passageiros'] ?? 0;
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        if (empty($descricao) || empty($placa)) {
            $_SESSION['error'] = 'Preencha todos os campos obrigatórios.';
            header('Location: /veiculos/create');
            exit;
        }

        $dados = [
            'descricao' => $descricao,
            'placa' => $placa,
            'numero_passageiros' => $numeroPassageiros,
            'ativo' => $ativo
        ];

        $this->veiculoRepository->create($dados);

        $_SESSION['success'] = 'Veículo cadastrado com sucesso.';
        header('Location: /veiculos');
        exit;
    }

    public function show(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $veiculo = $this->veiculoRepository->findById($id);

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
        $veiculo = $this->veiculoRepository->findById($id);

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
        $numeroPassageiros = $_POST['numero_passageiros'] ?? 0;
        $descricao = $_POST['descricao'] ?? '';
        $placa = $_POST['placa'] ?? '';
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        if ($id <= 0 || empty($descricao) || empty($placa)) {
            $_SESSION['error'] = 'Dados inválidos para atualização.';
            header('Location: /veiculos/edit?id=' . $id);
            exit;
        }

        $dados = [
            'descricao' => $descricao,
            'placa' => $placa,
            'numero_passageiros' => $numeroPassageiros,
            'ativo' => $ativo
        ];

        $this->veiculoRepository->update($id, $dados);

        $_SESSION['success'] = 'Veículo atualizado com sucesso.';
        header('Location: /veiculos');
        exit;
    }

    public function destroy(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id > 0) {
            $this->veiculoRepository->delete($id);
            $_SESSION['success'] = 'Veículo removido com sucesso.';
        }

        header('Location: /veiculos');
        exit;
    }

    public function autocomplete(): void
    {
        header('Content-Type: application/json');

        $term = $_GET['q'] ?? '';
        $currentVeiculoId = isset($_GET['current_id']) ? (int) $_GET['current_id'] : null;

        if (empty($term)) {
            echo json_encode([]);
            exit;
        }

        $veiculos = $this->veiculoRepository->autocomplete($term, $currentVeiculoId);
        echo json_encode($veiculos);
        exit;
    }
}