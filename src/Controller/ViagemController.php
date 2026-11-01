<?php

class ViagemController
{
    private PDO $pdo;
    private ViagemRepository $repository;

    public function __construct()
    {
        $this->pdo = Database::conectar();
        $this->repository = new ViagemRepository($this->pdo);
    }

    public function index(): void
    {
        $search = $_GET['search'] ?? null;
        $dataInicio = $_GET['data_inicio'] ?? null;
        $dataFim = $_GET['data_fim'] ?? null;
        $status = $_GET['status'] ?? null;
        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        $resultado = $this->repository->paginate(10, $pagina, $search, $dataInicio, $dataFim, $status);

        view('viagems.index', [
            'viagens' => $resultado
        ]);
    }

    public function create(): void
    {
        view('viagems.create', [
            'rotas' => $this->repository->getRotasAtivas(),
            'motoristas' => $this->repository->getMotoristasAtivos(),
            'veiculos' => $this->repository->getVeiculosAtivos(),
            'viagem' => null
        ]);
    }

    public function store(): void
    {
        $rotaId = isset($_POST['rota_id']) ? (int) $_POST['rota_id'] : 0;
        $motoristaId = isset($_POST['motorista_id']) ? (int) $_POST['motorista_id'] : 0;
        $veiculoId = isset($_POST['veiculo_id']) ? (int) $_POST['veiculo_id'] : 0;
        $dataSaida = $_POST['data_hora_saida'] ?? '';
        $dataChegada = $_POST['data_hora_chegada'] ?? null;
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        if ($rotaId <= 0 || $motoristaId <= 0 || $veiculoId <= 0 || empty($dataSaida)) {
            $_SESSION['error'] = 'Preencha todos os campos obrigatórios da viagem.';
            header('Location: /viagens/create');
            exit;
        }

        $this->repository->create([
            'rota_id' => $rotaId,
            'motorista_id' => $motoristaId,
            'veiculo_id' => $veiculoId,
            'data_hora_saida' => $dataSaida,
            'data_hora_chegada' => $dataChegada,
            'ativo' => $ativo
        ]);

        $_SESSION['success'] = 'Viagem agendada com sucesso!';
        header('Location: /viagens');
        exit;
    }

    public function show(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $viagem = $this->repository->findById($id);

        if (!$viagem) {
            $_SESSION['error'] = 'Viagem não encontrada.';
            header('Location: /viagens');
            exit;
        }

        // Se precisares de pontos de parada da rota ou passageiros no futuro, podes carregar aqui.
        view('viagems.show', [
            'viagem' => $viagem
        ]);
    }

    public function edit(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $viagem = $this->repository->findById($id);

        if (!$viagem) {
            $_SESSION['error'] = 'Viagem não encontrada.';
            header('Location: /viagens');
            exit;
        }

        view('viagems.edit', [
            'viagem' => $viagem,
            'rotas' => $this->repository->getRotasAtivas(),
            'motoristas' => $this->repository->getMotoristasAtivos(),
            'veiculos' => $this->repository->getVeiculosAtivos()
        ]);
    }

    public function update(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $rotaId = isset($_POST['rota_id']) ? (int) $_POST['rota_id'] : 0;
        $motoristaId = isset($_POST['motorista_id']) ? (int) $_POST['motorista_id'] : 0;
        $veiculoId = isset($_POST['veiculo_id']) ? (int) $_POST['veiculo_id'] : 0;
        $dataSaida = $_POST['data_hora_saida'] ?? '';
        $dataChegada = $_POST['data_hora_chegada'] ?? null;
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        if ($id <= 0 || $rotaId <= 0 || $motoristaId <= 0 || $veiculoId <= 0 || empty($dataSaida)) {
            $_SESSION['error'] = 'Dados inválidos para atualização.';
            header('Location: /viagens/edit?id=' . $id);
            exit;
        }

        $this->repository->update($id, [
            'rota_id' => $rotaId,
            'motorista_id' => $motoristaId,
            'veiculo_id' => $veiculoId,
            'data_hora_saida' => $dataSaida,
            'data_hora_chegada' => $dataChegada,
            'ativo' => $ativo
        ]);

        $_SESSION['success'] = 'Viagem atualizada com sucesso!';
        header('Location: /viagens');
        exit;
    }

    public function destroy(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id > 0) {
            $this->repository->delete($id);
            $_SESSION['success'] = 'Viagem excluída com sucesso!';
        }

        header('Location: /viagens');
        exit;
    }

    public function duplicate(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $viagem = $this->repository->findById($id);

        if (!$viagem) {
            $_SESSION['error'] = 'Viagem não encontrada para duplicação.';
            header('Location: /viagens');
            exit;
        }

        $viagem['data_hora_saida'] = '';
        $viagem['data_hora_chegada'] = '';
        $viagem['codigo_qr'] = null;

        view('viagems.create', [
            'viagem' => $viagem,
            'rotas' => $this->repository->getRotasAtivas(),
            'motoristas' => $this->repository->getMotoristasAtivos(),
            'veiculos' => $this->repository->getVeiculosAtivos()
        ]);
    }
}