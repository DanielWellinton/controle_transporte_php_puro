<?php

class PassageiroController
{
    private PDO $pdo;
    private PassageiroRepository $repository;

    public function __construct()
    {
        $this->pdo = Database::conectar();
        $this->repository = new PassageiroRepository($this->pdo);
    }

    private function getUsuarioLogado(): array
    {
        if (!isset($_SESSION['usuario'])) {
            header('Location: /login');
            exit;
        }
        return $_SESSION['usuario'];
    }

    public function index(): void
    {
        $user = $this->getUsuarioLogado();
        $userId = (int) $user['id'];

        $search = $_GET['search'] ?? null;
        $dataInicio = $_GET['data_inicio'] ?? null;
        $dataFim = $_GET['data_fim'] ?? null;
        $inscricao = $_GET['inscricao'] ?? null;
        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        $resultado = $this->repository->paginateDashboard($userId, 9, $pagina, $search, $dataInicio, $dataFim, $inscricao);

        view('dashboard', [
            'viagens' => $resultado
        ]);
    }

    public function selecionarPontos(): void
    {
        $user = $this->getUsuarioLogado();
        $userId = (int) $user['id'];
        $viagemId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        $viagem = $this->repository->findViagemDetalhes($viagemId);

        if (!$viagem) {
            $_SESSION['error'] = 'Viagem não encontrada.';
            header('Location: /dashboard');
            exit;
        }

        $passageiro = $this->repository->findInscricaoPassageiro($userId, $viagemId);

        view('passageiros.selecionar-pontos', [
            'viagem' => $viagem,
            'passageiro' => $passageiro
        ]);
    }

    public function salvarPontos(): void
    {
        $user = $this->getUsuarioLogado();
        $userId = (int) $user['id'];
        $viagemId = isset($_POST['viagem_id']) ? (int) $_POST['viagem_id'] : 0;
        
        $pontoSaidaId = isset($_POST['ponto_de_parada_saida_id']) ? (int) $_POST['ponto_de_parada_saida_id'] : 0;
        $pontoChegadaId = isset($_POST['ponto_de_parada_chegada_id']) ? (int) $_POST['ponto_de_parada_chegada_id'] : 0;

        if (!$pontoSaidaId || !$pontoChegadaId) {
            $_SESSION['error'] = 'Selecione os pontos de embarque e desembarque.';
            header('Location: /passageiros/selecionar-pontos?id=' . $viagemId);
            exit;
        }

        $this->repository->salvarPontosInscricao($userId, $viagemId, $pontoSaidaId, $pontoChegadaId);

        $_SESSION['success'] = 'Sua vaga foi confirmada nesta viagem!';
        header('Location: /dashboard');
        exit;
    }

    public function cancelar(): void
    {
        $user = $this->getUsuarioLogado();
        $userId = (int) $user['id'];
        $viagemId = isset($_POST['id']) || isset($_GET['id']) ? (int) ($_POST['id'] ?? $_GET['id']) : 0;

        $this->repository->cancelarInscricao($userId, $viagemId);

        $_SESSION['success'] = 'Sua participação foi cancelada.';
        header('Location: /dashboard');
        exit;
    }

    public function historico(): void
    {
        $user = $this->getUsuarioLogado();
        $userId = (int) $user['id'];

        $search = $_GET['search'] ?? null;
        $dataInicio = $_GET['data_inicio'] ?? null;
        $dataFim = $_GET['data_fim'] ?? null;
        $statusEmbarque = $_GET['status_embarque'] ?? null;
        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        $resultado = $this->repository->paginateHistorico($userId, 10, $pagina, $search, $dataInicio, $dataFim, $statusEmbarque);

        // Recolhe todos os IDs de pontos de parada envolvidos para exibir descrições amigáveis
        $pontosIds = [];
        foreach ($resultado['data'] as $v) {
            foreach ($v['passageiros'] as $p) {
                if (!empty($p['pivot_ponto_de_parada_saida_id'])) $pontosIds[] = $p['pivot_ponto_de_parada_saida_id'];
                if (!empty($p['pivot_ponto_de_parada_chegada_id'])) $pontosIds[] = $p['pivot_ponto_de_parada_chegada_id'];
            }
        }
        $pontos = $this->repository->getDescricoesPontos(array_unique($pontosIds));

        view('passageiros.historico', [
            'viagens' => $resultado,
            'pontos' => $pontos
        ]);
    }
}