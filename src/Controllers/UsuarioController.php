<?php

use Services\UsuarioService;

class UsuarioController
{
    private UsuarioService $usuarioService;
    private UsuarioRepository $usuarioRepository;

    public function __construct()
    {
        $pdo = Database::conectar();
        $this->usuarioRepository = new UsuarioRepository($pdo);
        $this->usuarioService = new UsuarioService($this->usuarioRepository);
    }

    public function index(): void
    {
        $search = $_GET['search'] ?? null;
        $papelId = $_GET['papel_id'] ?? null;
        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        $resultado = $this->usuarioService->listarPaginado($search, $papelId, $pagina, 10);
        $papeis = $this->usuarioService->listarPapeisAtivos();

        view('usuarios.index', [
            'usuarios' => $resultado,
            'papeis' => $papeis
        ]);
    }

    public function create(): void
    {
        $papeis = $this->usuarioService->listarPapeisAtivos();
        view('usuarios.create', ['papeis' => $papeis]);
    }

    public function store(): void
    {
        try {
            $this->usuarioService->cadastrar($_POST, $_POST['papeis'] ?? []);
            $_SESSION['success'] = 'Usuário cadastrado com sucesso!';
            header('Location: /usuarios');
            exit;
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /usuarios/create');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao cadastrar usuário.';
            header('Location: /usuarios/create');
            exit;
        }
    }

    public function edit(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $usuario = $this->usuarioService->buscarPorId($id);

        if (!$usuario) {
            $_SESSION['error'] = 'Usuário não encontrado.';
            header('Location: /usuarios');
            exit;
        }

        $papeis = $this->usuarioService->listarPapeisAtivos();
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

        try {
            $this->usuarioService->atualizar($id, $_POST, $_POST['papeis'] ?? []);
            $_SESSION['success'] = 'Usuário atualizado com sucesso!';
            header('Location: /usuarios');
            exit;
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /usuarios/edit?id=' . $id);
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar usuário.';
            header('Location: /usuarios/edit?id=' . $id);
            exit;
        }
    }

    public function destroy(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $usuarioLogadoId = $_SESSION['usuario_id'] ?? 0;

        try {
            $this->usuarioService->excluir($id, $usuarioLogadoId);
            $_SESSION['success'] = 'Usuário removido com sucesso!';
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao remover usuário.';
        }

        header('Location: /usuarios');
        exit;
    }

    public function autocomplete(): void
    {
        header('Content-Type: application/json');

        $term = $_GET['q'] ?? '';
        $currentUsuarioId = isset($_GET['current_usuario_id']) ? (int) $_GET['current_usuario_id'] : null;

        $usuarios = $this->usuarioService->autocompleteMotoristas($term, $currentUsuarioId);
        echo json_encode($usuarios);
        exit;
    }
}