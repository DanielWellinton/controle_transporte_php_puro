<?php

class MotoristaController
{
    private PDO $pdo;
    private MotoristaRepository $motoristaRepository;

    public function __construct()
    {
        $this->pdo = Database::conectar();
        $this->motoristaRepository = new MotoristaRepository($this->pdo);
    }

    public function index(): void
    {
        $search = $_GET['search'] ?? null;
        $status = $_GET['status'] ?? null;
        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        $resultado = $this->motoristaRepository->paginate(10, $pagina, $search, $status);

        view('motoristas.index', ['motoristas' => $resultado]);
    }

    public function create(): void
    {
        view('motoristas.create');
    }

    public function store(): void
    {
        $dados = [
            'usuario_id' => $_POST['usuario_id'] ?? null,
            'cnh' => $_POST['cnh'] ?? null,
            'data_validade_cnh' => $_POST['data_validade_cnh'] ?? null,
            'ativo' => isset($_POST['ativo']) ? 1 : 0
        ];

        if (empty($dados['usuario_id']) || empty($dados['cnh']) || empty($dados['data_validade_cnh'])) {
            $_SESSION['error'] = 'Por favor, preencha todos os campos obrigatórios.';
            header('Location: /motoristas/create');
            exit;
        }

        $this->motoristaRepository->create($dados);

        $_SESSION['success'] = 'Motorista cadastrado com sucesso.';
        header('Location: /motoristas');
        exit;
    }

    public function show(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $motorista = $this->motoristaRepository->findById($id);

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
        $motorista = $this->motoristaRepository->findById($id);

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
        
        $dados = [
            'usuario_id' => $_POST['usuario_id'] ?? null,
            'cnh' => $_POST['cnh'] ?? null,
            'data_validade_cnh' => $_POST['data_validade_cnh'] ?? null,
            'ativo' => isset($_POST['ativo']) ? 1 : 0
        ];

        if ($id <= 0 || empty($dados['usuario_id']) || empty($dados['cnh']) || empty($dados['data_validade_cnh'])) {
            $_SESSION['error'] = 'Dados inválidos para atualização.';
            header('Location: /motoristas/edit?id=' . $id);
            exit;
        }

        $this->motoristaRepository->update($id, $dados);

        $_SESSION['success'] = 'Motorista atualizado com sucesso.';
        header('Location: /motoristas');
        exit;
    }

    public function destroy(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id > 0) {
            $this->motoristaRepository->delete($id);
            $_SESSION['success'] = 'Motorista removido com sucesso.';
        } else {
            $_SESSION['error'] = 'ID de motorista inválido.';
        }

        header('Location: /motoristas');
        exit;
    }

    public function autocomplete(): void
    {
        header('Content-Type: application/json');

        $term = $_GET['q'] ?? '';
        $currentMotoristaId = isset($_GET['current_id']) ? (int) $_GET['current_id'] : null;

        if (empty($term)) {
            echo json_encode([]);
            exit;
        }

        $motoristas = $this->motoristaRepository->autocomplete($term, $currentMotoristaId);

        $dados = array_map(fn($m) => [
            'id' => $m['id'],
            'name' => $m['nome'] ?? "Motorista #{$m['id']}",
            'email' => $m['email'] ?? ''
        ], $motoristas);

        echo json_encode($dados);
        exit;
    }
}