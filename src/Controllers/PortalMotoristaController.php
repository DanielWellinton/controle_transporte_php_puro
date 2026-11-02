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

    /**
     * Obtém os dados do usuário autenticado (já validado pelo Middleware).
     */
    private function getUsuarioAtual(): array
    {
        return [
            'id' => $_SESSION['usuario_id'] ?? 0,
            'is_admin' => in_array('Administrador', $_SESSION['user_roles'] ?? []),
            'role' => (in_array('Motorista', $_SESSION['user_roles'] ?? [])) ? 'motorista' : ''
        ];
    }

    public function index(): void
    {
        $user = $this->getUsuarioAtual();

        try {
            $motorista = $this->service->verificarMotorista($user);
            $viagens = $this->service->listarViagensAtivas((int) $motorista['id']);

            view('portal_motorista.index', [
                'viagens' => $viagens
            ]);
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /dashboard');
            exit;
        }
    }

    public function historico(): void
    {
        $user = $this->getUsuarioAtual();

        try {
            $motorista = $this->service->verificarMotorista($user);
            
            $search = $_GET['search'] ?? null;
            $dataInicio = $_GET['data_inicio'] ?? null;
            $dataFim = $_GET['data_fim'] ?? null;
            $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;

            $resultado = $this->service->listarHistorico((int) $motorista['id'], $search, $dataInicio, $dataFim, $pagina);

            view('portal_motorista.historico', [
                'historico' => $resultado
            ]);
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /dashboard');
            exit;
        }
    }

    public function show(): void
    {
        $user = $this->getUsuarioAtual();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        
        try {
            $motorista = $this->service->verificarMotorista($user);
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
        $user = $this->getUsuarioAtual();
        $id = isset($_POST['id']) || isset($_GET['id']) ? (int) ($_POST['id'] ?? $_GET['id']) : 0;

        try {
            $motorista = $this->service->verificarMotorista($user);
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
        $user = $this->getUsuarioAtual();
        $id = isset($_POST['id']) || isset($_GET['id']) ? (int) ($_POST['id'] ?? $_GET['id']) : 0;

        try {
            $motorista = $this->service->verificarMotorista($user);
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
        $user = $this->getUsuarioAtual();
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        try {
            $motorista = $this->service->verificarMotorista($user);
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