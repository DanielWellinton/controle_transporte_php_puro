<?php

class PortalMotoristaController
{
    private PDO $pdo;
    private PortalMotoristaRepository $repository;

    public function __construct()
    {
        $this->pdo = Database::conectar();
        $this->repository = new PortalMotoristaRepository($this->pdo);
    }

    private function getUsuarioLogado(): array
    {
        // Simulação do usuário autenticado na sessão PHP puro
        if (!isset($_SESSION['usuario'])) {
            header('Location: /login');
            exit;
        }
        return $_SESSION['usuario'];
    }

    private function verificarMotorista(): array
    {
        $user = $this->getUsuarioLogado();

        // Verifica papel de motorista (ajuste conforme a regra de perfis da sua sessão)
        if (($user['role'] ?? '') !== 'motorista' && !($user['is_admin'] ?? false)) {
            header('HTTP/1.1 403 Forbidden');
            echo "Usuário não possui o papel de motorista.";
            exit;
        }

        $motorista = $this->repository->findMotoristaByUserId((int) $user['id']);

        if (!$motorista) {
            $_SESSION['error'] = 'Seu cadastro de motorista ainda não foi concluído.';
            header('Location: /home');
            exit;
        }

        return $motorista;
    }

    private function autorizarMotorista(array $motorista, array $viagem): void
    {
        $user = $this->getUsuarioLogado();
        $isAdmin = $user['is_admin'] ?? false;

        if ($viagem['motorista_id'] != $motorista['id'] && !$isAdmin) {
            header('HTTP/1.1 403 Forbidden');
            echo "Você não tem permissão para gerenciar esta viagem.";
            exit;
        }
    }

    public function index(): void
    {
        $motorista = $this->verificarMotorista();
        $viagens = $this->repository->getViagensAtivasByMotorista((int) $motorista['id']);

        view('portal_motorista.index', [
            'viagens' => $viagens
        ]);
    }

    public function historico(): void
    {
        $motorista = $this->verificarMotorista();
        
        $search = $_GET['search'] ?? null;
        $dataInicio = $_GET['data_inicio'] ?? null;
        $dataFim = $_GET['data_fim'] ?? null;
        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        $resultado = $this->repository->paginateHistorico((int) $motorista['id'], 10, $pagina, $search, $dataInicio, $dataFim);

        view('portal_motorista.historico', [
            'historico' => $resultado
        ]);
    }

    public function show(): void
    {
        $motorista = $this->verificarMotorista();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        
        $viagem = $this->repository->findViagemDetalhes($id);

        if (!$viagem) {
            $_SESSION['error'] = 'Viagem não encontrada.';
            header('Location: /portal-motorista');
            exit;
        }

        $this->autorizarMotorista($motorista, $viagem);

        view('portal_motorista.show', [
            'viagem' => $viagem
        ]);
    }

    public function iniciar(): void
    {
        $motorista = $this->verificarMotorista();
        $id = isset($_POST['id']) || isset($_GET['id']) ? (int) ($_POST['id'] ?? $_GET['id']) : 0;

        $viagem = $this->repository->findViagemDetalhes($id);
        if ($viagem) {
            $this->autorizarMotorista($motorista, $viagem);
            $this->repository->iniciarViagem($id);
            $_SESSION['success'] = 'Viagem iniciada com sucesso!';
        }

        header('Location: /portal-motorista/show?id=' . $id);
        exit;
    }

    public function finalizar(): void
    {
        $motorista = $this->verificarMotorista();
        $id = isset($_POST['id']) || isset($_GET['id']) ? (int) ($_POST['id'] ?? $_GET['id']) : 0;

        $viagem = $this->repository->findViagemDetalhes($id);
        if ($viagem) {
            $this->autorizarMotorista($motorista, $viagem);
            $this->repository->finalizarViagem($id);
            $_SESSION['success'] = 'Viagem finalizada com sucesso!';
        }

        header('Location: /portal-motorista/show?id=' . $id);
        exit;
    }

    public function imprimir(): void
    {
        $motorista = $this->verificarMotorista();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        $viagem = $this->repository->findViagemDetalhes($id);

        if (!$viagem) {
            $_SESSION['error'] = 'Viagem não encontrada.';
            header('Location: /portal-motorista');
            exit;
        }

        $this->autorizarMotorista($motorista, $viagem);

        view('portal_motorista.imprimir', [
            'viagem' => $viagem
        ]);
    }
}