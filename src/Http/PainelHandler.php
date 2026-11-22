<?php

declare(strict_types=1);

namespace App\Http;

use App\Config;
use App\Payment\PaymentSanitizer;
use App\Repository\ClienteRepository;
use App\Security\AdminAuth;
use App\Security\Csrf;
use App\Security\RateLimiter;
use App\Security\SessionManager;
use App\Service\DataRetentionService;
use App\Support\CpfMask;
use App\View\PainelView;

final class PainelHandler
{
    public function __construct(
        private readonly AdminAuth $auth,
        private readonly ClienteRepository $repository,
        private readonly PainelView $view,
        private readonly RateLimiter $rateLimiter,
    ) {
    }

    public function handle(): void
    {
        Config::load();

        if (isset($_GET['action']) && $_GET['action'] === 'logout') {
            $this->auth->logout();
            header('Location: painel.php');
            exit;
        }

        if ($this->auth->isLoggedIn() && isset($_GET['action']) && $_GET['action'] === 'export') {
            $this->exportCsv();

            return;
        }

        if ($this->auth->isLoggedIn() && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['delete_id'])) {
            $this->handleDelete();

            return;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['senha'])) {
            $this->handleLogin();

            return;
        }

        if (!$this->auth->isLoggedIn()) {
            echo $this->view->renderLogin(null, Csrf::token());

            return;
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = Config::getInt('PAINEL_PAGE_SIZE', 15);

        $this->repository->ensureSchema();
        $purged = DataRetentionService::createDefault()->purgeIfDue();
        $total = $this->repository->countAll();
        $rows = $this->repository->findPage($page, $perPage);
        $rows = array_map(function (array $row): array {
            $row['cartao_mascarado'] = PaymentSanitizer::maskPan(
                '000000000000' . ($row['cartao_ultimos4'] ?? '')
            );
            $row['cpf_pessoal_mascarado'] = CpfMask::mask((string) ($row['cpf_pessoal'] ?? ''));
            $row['cpf_cartao_mascarado'] = CpfMask::mask((string) ($row['cpf_cartao'] ?? ''));

            return $row;
        }, $rows);

        echo $this->view->renderDashboard($rows, $total, $page, $perPage, Csrf::token(), $purged);
    }

    private function handleLogin(): void
    {
        $ip = RateLimiter::clientIp();
        if (!$this->rateLimiter->allow('painel-login:' . $ip, 5, 900)) {
            echo $this->view->renderLogin('Muitas tentativas. Aguarde 15 minutos.', Csrf::token());

            return;
        }

        if (!Csrf::validate(isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null)) {
            echo $this->view->renderLogin('Sessão expirada. Recarregue a página.', Csrf::token());

            return;
        }

        if ($this->auth->verifyPassword((string) $_POST['senha'])) {
            SessionManager::regenerate();
            $this->auth->login();
            header('Location: painel.php');
            exit;
        }

        echo $this->view->renderLogin('Senha incorreta!', Csrf::token());
    }

    private function handleDelete(): void
    {
        if (!Csrf::validate(isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null)) {
            http_response_code(403);
            echo 'Token CSRF inválido.';

            return;
        }

        $id = (int) $_POST['delete_id'];
        if ($id > 0) {
            $this->repository->deleteById($id);
        }

        header('Location: painel.php');
        exit;
    }

    private function exportCsv(): void
    {
        $this->repository->ensureSchema();
        $rows = $this->repository->findAll();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="cadastros-cdt.csv"');

        $out = fopen('php://output', 'w');
        if ($out === false) {
            return;
        }

        fputcsv($out, ['id', 'nome', 'email', 'telefone', 'cpf_mascarado', 'tipo_pagamento', 'cartao_mascarado', 'validade', 'criado_em'], ';');
        foreach ($rows as $row) {
            fputcsv($out, [
                $row['id'],
                $row['nome'],
                $row['email'],
                $row['telefone'],
                CpfMask::mask((string) ($row['cpf_pessoal'] ?? '')),
                $row['tipo_pagamento'],
                PaymentSanitizer::maskPan('000000000000' . ($row['cartao_ultimos4'] ?? '')),
                $row['validade'],
                $row['criado_em'] ?? '',
            ], ';');
        }

        fclose($out);
    }

    public static function createDefault(): self
    {
        return new self(new AdminAuth(), new ClienteRepository(), new PainelView(), new RateLimiter());
    }
}