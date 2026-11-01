<?php

class UsuarioController
{
    private PDO $pdo;
    private UsuarioRepository $usuarioRepository;

    public function __construct()
    {
        $this->pdo = Database::conectar();
        $this->usuarioRepository = new UsuarioRepository($this->pdo);
    }

    public function index(): void
    {
        $search = $_GET['search'] ?? null;
        $papelId = $_GET['papel_id'] ?? null;
        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        $resultado = $this->usuarioRepository->paginate(10, $pagina, $search, $papelId);
        $papeis = $this->usuarioRepository->getAllPapeisAtivos();

        view('usuarios.index', [
            'usuarios' => $resultado,
            'papeis' => $papeis
        ]);
    }

    public function create(): void
    {
        $papeis = $this->usuarioRepository->getAllPapeisAtivos();
        view('usuarios.create', ['papeis' => $papeis]);
    }

    public function store(): void
    {
        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $senhaConfirmacao = $_POST['senha_confirmation'] ?? '';
        $telefone = $_POST['telefone'] ?? null;
        $dataNascimento = $_POST['data_nascimento'] ?? null;
        $papeis = $_POST['papeis'] ?? [];

        if (empty($nome) || empty($email) || empty($senha)) {
            $_SESSION['error'] = 'Preencha todos os campos obrigatórios.';
            header('Location: /usuarios/create');
            exit;
        }

        if ($senha !== $senhaConfirmacao) {
            $_SESSION['error'] = 'As senhas não coincidem.';
            header('Location: /usuarios/create');
            exit;
        }

        $dados = [
            'nome' => $nome,
            'email' => $email,
            'senha' => $senha,
            'telefone' => $telefone,
            'data_nascimento' => $dataNascimento
        ];

        $this->usuarioRepository->create($dados, $papeis);

        $_SESSION['success'] = 'Usuário cadastrado com sucesso!';
        header('Location: /usuarios');
        exit;
    }

    public function edit(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $usuario = $this->usuarioRepository->findById($id);

        if (!$usuario) {
            $_SESSION['error'] = 'Usuário não encontrado.';
            header('Location: /usuarios');
            exit;
        }

        $papeis = $this->usuarioRepository->getAllPapeisAtivos();
        $papeisUsuario = array_column($usuario['papeis'], 'id');

        view('usuarios.edit', [
            'usuario' => $usuario,
            'papeis' => $papeis,
            'papeisUsuario' => $papeisUsuario
        ]);
    }

    public function update(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $senhaConfirmacao = $_POST['senha_confirmation'] ?? '';
        $telefone = $_POST['telefone'] ?? null;
        $dataNascimento = $_POST['data_nascimento'] ?? null;
        $papeis = $_POST['papeis'] ?? [];

        if ($id <= 0 || empty($nome) || empty($email)) {
            $_SESSION['error'] = 'Dados inválidos para atualização.';
            header('Location: /usuarios/edit?id=' . $id);
            exit;
        }

        if (!empty($senha) && $senha !== $senhaConfirmacao) {
            $_SESSION['error'] = 'As senhas não coincidem.';
            header('Location: /usuarios/edit?id=' . $id);
            exit;
        }

        $dados = [
            'nome' => $nome,
            'email' => $email,
            'senha' => $senha,
            'telefone' => $telefone,
            'data_nascimento' => $dataNascimento
        ];

        $this->usuarioRepository->update($id, $dados, $papeis);

        $_SESSION['success'] = 'Usuário atualizado com sucesso!';
        header('Location: /usuarios');
        exit;
    }

    public function destroy(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $usuarioLogadoId = $_SESSION['usuario_id'] ?? 0;

        if ($id === $usuarioLogadoId) {
            $_SESSION['error'] = 'Você não pode excluir sua própria conta.';
            header('Location: /usuarios');
            exit;
        }

        if ($id > 0) {
            $this->usuarioRepository->delete($id);
            $_SESSION['success'] = 'Usuário removido com sucesso!';
        }

        header('Location: /usuarios');
        exit;
    }

    public function autocomplete(): void
    {
        header('Content-Type: application/json');

        $term = $_GET['q'] ?? '';
        $currentUsuarioId = isset($_GET['current_usuario_id']) ? (int) $_GET['current_usuario_id'] : null;

        if (empty($term)) {
            echo json_encode([]);
            exit;
        }

        $usuarios = $this->usuarioRepository->autocompleteMotoristas($term, $currentUsuarioId);
        echo json_encode($usuarios);
        exit;
    }
}