<?php

use Services\PassageiroService;

class PassageiroController
{
    private PassageiroService $service;

    public function __construct()
    {
        $pdo = Database::conectar();
        $repository = new PassageiroRepository($pdo);
        $this->service = new PassageiroService($repository);
    }

    public function index(): void
    {
        $userId = $_SESSION['usuario_id'];

        $search = $_GET['search'] ?? null;
        $dataInicio = $_GET['data_inicio'] ?? null;
        $dataFim = $_GET['data_fim'] ?? null;
        $inscricao = $_GET['inscricao'] ?? null;
        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        $resultado = $this->service->listarDashboard($userId, $search, $dataInicio, $dataFim, $inscricao, $pagina);

        view('dashboard', [
            'viagens' => $resultado
        ]);
    }

    public function selecionarPontos(): void
    {
        $userId = $_SESSION['usuario_id'];
        $viagemId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        try {
            $dados = $this->service->buscarDetalhesViagem($viagemId, $userId);

            view('passageiros.selecionar-pontos', [
                'viagem' => $dados['viagem'],
                'passageiro' => $dados['passageiro']
            ]);

        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /dashboard');
            exit;
        }
    }

    public function salvarPontos(): void
    {
        $userId = $_SESSION['usuario_id'];
        $viagemId = isset($_POST['viagem_id']) ? (int) $_POST['viagem_id'] : 0;

        try {
            $this->service->salvarPontos($userId, $_POST);

            $_SESSION['success'] = 'Sua vaga foi confirmada nesta viagem!';
            header('Location: /dashboard');
            exit;

        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /passageiros/selecionar-pontos?id=' . $viagemId);
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao confirmar a vaga na viagem.';
            header('Location: /passageiros/selecionar-pontos?id=' . $viagemId);
            exit;
        }
    }

    public function cancelar(): void
    {
        $userId = $_SESSION['usuario_id'];
        $viagemId = isset($_POST['id']) || isset($_GET['id']) ? (int) ($_POST['id'] ?? $_GET['id']) : 0;

        try {
            $this->service->cancelarInscricao($userId, $viagemId);
            $_SESSION['success'] = 'Sua participação foi cancelada.';
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao cancelar a participação.';
        }

        header('Location: /dashboard');
        exit;
    }

    public function historico(): void
    {
        $userId = $_SESSION['usuario_id'];

        $search = $_GET['search'] ?? null;
        $dataInicio = $_GET['data_inicio'] ?? null;
        $dataFim = $_GET['data_fim'] ?? null;
        $statusEmbarque = $_GET['status_embarque'] ?? null;
        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        $dados = $this->service->listarHistorico($userId, $search, $dataInicio, $dataFim, $statusEmbarque, $pagina);

        view('passageiros.historico', [
            'viagens' => $dados['viagens'],
            'pontos' => $dados['pontos']
        ]);
    }
}