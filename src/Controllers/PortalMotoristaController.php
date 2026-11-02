<?php

use Services\PortalMotoristaService;

class PortalMotoristaController
{
    private PortalMotoristaService $service;

    public function __construct()
    {
        $pdo = Database::conectar();
        $repository = new PortalMotoristaRepository($pdo);
        $this->service = new PortalMotoristaService($repository);
    }

    private function getUsuarioLogado(): array
    {
        if (!isset($_SESSION['usuario'])) {
            header('Location: /login');
            exit;
        }
        return $_SESSION['usuario'];
    }

    private function checarMotoristaSeguro(): array
    {
        $user = $this->getUsuarioLogado();

        try {
            return $this->service->verificarMotorista($user);
        } catch (\RuntimeException $e) {
            if ($e->getCode() === 403) {
                header('HTTP/1.1 403 Forbidden');
                echo $e->getMessage();
                exit;
            }
            throw $e;
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /home');
            exit;
        }
    }

    public function index(): void
    {
        $motorista = $this->checarMotoristaSeguro();
        $viagens = $this->service->listarViagensAtivas((int) $motorista['id']);

        view('portal_motorista.index', [
            'viagens' => $viagens
        ]);
    }

    public function historico(): void
    {
        $motorista = $this->checarMotoristaSeguro();
        
        $search = $_GET['search'] ?? null;
        $dataInicio = $_GET['data_inicio'] ?? null;
        $dataFim = $_GET['data_fim'] ?? null;
        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        $resultado = $this->service->listarHistorico((int) $motorista['id'], $search, $dataInicio, $dataFim, $pagina);

        view('portal_motorista.historico', [
            'historico' => $resultado
        ]);
    }

    public function show(): void
    {
        $motorista = $this->checarMotoristaSeguro();
        $user = $this->getUsuarioLogado();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        
        try {
            $viagem = $this->service->buscarDetalhesViagem($id, $motorista, $user);

            view('portal_motorista.show', [
                'viagem' => $viagem
            ]);

        } catch (\RuntimeException $e) {
            if ($e->getCode() === 403) {
                header('HTTP/1.1 403 Forbidden');
                echo $e->getMessage();
                exit;
            }
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /portal-motorista');
            exit;
        }
    }

    public function iniciar(): void
    {
        $motorista = $this->checarMotoristaSeguro();
        $user = $this->getUsuarioLogado();
        $id = isset($_POST['id']) || isset($_GET['id']) ? (int) ($_POST['id'] ?? $_GET['id']) : 0;

        try {
            $this->service->iniciarViagem($id, $motorista, $user);
            $_SESSION['success'] = 'Viagem iniciada com sucesso!';
        } catch (\RuntimeException $e) {
            if ($e->getCode() === 403) {
                header('HTTP/1.1 403 Forbidden');
                echo $e->getMessage();
                exit;
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao iniciar a viagem.';
        }

        header('Location: /portal-motorista/show?id=' . $id);
        exit;
    }

    public function finalizar(): void
    {
        $motorista = $this->checarMotoristaSeguro();
        $user = $this->getUsuarioLogado();
        $id = isset($_POST['id']) || isset($_GET['id']) ? (int) ($_POST['id'] ?? $_GET['id']) : 0;

        try {
            $this->service->finalizarViagem($id, $motorista, $user);
            $_SESSION['success'] = 'Viagem finalizada com sucesso!';
        } catch (\RuntimeException $e) {
            if ($e->getCode() === 403) {
                header('HTTP/1.1 403 Forbidden');
                echo $e->getMessage();
                exit;
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao finalizar a viagem.';
        }

        header('Location: /portal-motorista/show?id=' . $id);
        exit;
    }

    public function imprimir(): void
    {
        $motorista = $this->checarMotoristaSeguro();
        $user = $this->getUsuarioLogado();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        try {
            $viagem = $this->service->buscarDetalhesViagem($id, $motorista, $user);

            view('portal_motorista.imprimir', [
                'viagem' => $viagem
            ]);

        } catch (\RuntimeException $e) {
            if ($e->getCode() === 403) {
                header('HTTP/1.1 403 Forbidden');
                echo $e->getMessage();
                exit;
            }
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /portal-motorista');
            exit;
        }
    }
}