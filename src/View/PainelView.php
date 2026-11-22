<?php

declare(strict_types=1);

namespace App\View;

final class PainelView
{
    public function renderLogin(?string $error, string $csrfToken = ''): string
    {
        $errorHtml = $error !== null
            ? '<p class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-700" role="alert">'
                . htmlspecialchars($error, ENT_QUOTES, 'UTF-8')
                . '</p>'
            : '';

        $csrfField = $csrfToken !== ''
            ? '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') . '">'
            : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Painel CDT</title>
    <link rel="icon" href="imagens/LOGOTIPO.svg" type="image/svg+xml">
    <link rel="stylesheet" href="CSS/app.css">
</head>
<body class="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-100 via-white to-brand-50 p-4">
    <main class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/60">
        <div class="mb-6 text-center">
            <img src="imagens/LOGOMARCA.svg" alt="CDT Jacarepaguá" class="mx-auto mb-6 h-14 w-auto max-w-[240px]">
            <h1 class="text-2xl font-bold text-slate-900">Painel administrativo</h1>
            <p class="mt-2 text-sm text-slate-500">Acesso restrito aos cadastros recebidos</p>
        </div>
        {$errorHtml}
        <form method="POST" class="space-y-4">
            {$csrfField}
            <div class="space-y-1.5">
                <label for="senha">Senha de acesso</label>
                <input type="password" id="senha" name="senha" placeholder="••••••••" required autofocus autocomplete="current-password">
            </div>
            <button type="submit" class="btn-primary">Entrar</button>
        </form>
        <p class="mt-6 text-center text-xs text-slate-400">
            <a href="index.html" class="font-medium text-brand-600 hover:text-brand-700">Voltar ao cadastro</a>
        </p>
    </main>
</body>
</html>
HTML;
    }

    /** @param list<array<string, mixed>> $rows */
    public function renderDashboard(
        array $rows,
        int $total = 0,
        int $page = 1,
        int $perPage = 15,
        string $csrfToken = '',
        int $purgedRetention = 0,
    ): string {
        $total = $total > 0 ? $total : count($rows);
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $totalPages = (int) max(1, (int) ceil($total / $perPage));

        $bodyRows = '';
        $colspan = 11;

        if ($rows === []) {
            $bodyRows = '<tr><td colspan="' . $colspan . '" class="px-4 py-12 text-center text-sm text-slate-500">Nenhum cadastro encontrado.</td></tr>';
        } else {
            foreach ($rows as $row) {
                $tipo = htmlspecialchars((string) ($row['tipo_pagamento'] ?? ''), ENT_QUOTES, 'UTF-8');
                $badgeClass = $tipo === 'credito' ? 'badge-credito' : 'badge-debito';
                $id = (int) ($row['id'] ?? 0);
                $cpfPessoal = htmlspecialchars(
                    (string) ($row['cpf_pessoal_mascarado'] ?? $row['cpf_pessoal'] ?? ''),
                    ENT_QUOTES,
                    'UTF-8',
                );
                $cpfCartao = htmlspecialchars(
                    (string) ($row['cpf_cartao_mascarado'] ?? $row['cpf_cartao'] ?? ''),
                    ENT_QUOTES,
                    'UTF-8',
                );

                $deleteForm = '';
                if ($csrfToken !== '' && $id > 0) {
                    $csrfEsc = htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8');
                    $deleteForm = <<<FORM
<form method="POST" class="inline" onsubmit="return confirm('Excluir cadastro #{$id}? Esta ação é irreversível (LGPD).');">
    <input type="hidden" name="csrf_token" value="{$csrfEsc}">
    <input type="hidden" name="delete_id" value="{$id}">
    <button type="submit" class="rounded-md px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50">Excluir</button>
</form>
FORM;
                }

                $bodyRows .= '<tr class="border-t border-slate-100 transition hover:bg-slate-50/80">';
                $bodyRows .= '<td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-slate-900">' . $id . '</td>';
                $bodyRows .= '<td class="whitespace-nowrap px-4 py-3 text-sm font-semibold text-slate-900">' . htmlspecialchars((string) ($row['nome'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
                $bodyRows .= '<td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">' . htmlspecialchars((string) ($row['telefone'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
                $bodyRows .= '<td class="whitespace-nowrap px-4 py-3 font-mono text-sm text-slate-600">' . $cpfPessoal . '</td>';
                $bodyRows .= '<td class="whitespace-nowrap px-4 py-3 text-sm"><span class="' . $badgeClass . '">' . $tipo . '</span></td>';
                $bodyRows .= '<td class="whitespace-nowrap px-4 py-3 font-mono text-sm font-medium text-slate-800">' . htmlspecialchars((string) ($row['cartao_mascarado'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
                $bodyRows .= '<td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">' . htmlspecialchars((string) ($row['validade'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
                $bodyRows .= '<td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">' . htmlspecialchars((string) ($row['nome_cartao'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
                $bodyRows .= '<td class="whitespace-nowrap px-4 py-3 font-mono text-sm text-slate-600">' . $cpfCartao . '</td>';
                $bodyRows .= '<td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">' . htmlspecialchars((string) ($row['email'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
                $bodyRows .= '<td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">' . $deleteForm . '</td>';
                $bodyRows .= '</tr>';
            }
        }

        $retentionHtml = '';
        if ($purgedRetention > 0) {
            $n = (int) $purgedRetention;
            $retentionHtml = '<p class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="status">'
                . 'Retenção LGPD: ' . $n . ' cadastro(s) antigo(s) removido(s) automaticamente neste acesso.</p>';
        }

        $pagination = '';
        if ($totalPages > 1) {
            $pagination .= '<nav class="mt-4 flex flex-wrap items-center justify-between gap-2 text-sm" aria-label="Paginação">';
            $pagination .= '<p class="text-slate-500">Página ' . $page . ' de ' . $totalPages . '</p>';
            $pagination .= '<div class="flex gap-2">';
            if ($page > 1) {
                $pagination .= '<a class="rounded-lg border border-slate-200 px-3 py-1.5 font-medium text-slate-700 hover:bg-slate-50" href="painel.php?page=' . ($page - 1) . '">Anterior</a>';
            }
            if ($page < $totalPages) {
                $pagination .= '<a class="rounded-lg border border-slate-200 px-3 py-1.5 font-medium text-slate-700 hover:bg-slate-50" href="painel.php?page=' . ($page + 1) . '">Próxima</a>';
            }
            $pagination .= '</div></nav>';
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastros | Painel CDT</title>
    <link rel="icon" href="imagens/LOGOTIPO.svg" type="image/svg+xml">
    <link rel="stylesheet" href="CSS/app.css">
</head>
<body class="min-h-screen bg-slate-50">
    <header class="sticky top-0 z-10 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-600">CDT Jacarepaguá</p>
                <h1 class="text-xl font-bold text-slate-900">Cadastros recebidos</h1>
                <p class="text-sm text-slate-500">Total: <span class="font-semibold text-slate-800">{$total}</span> registro(s)</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="painel.php?action=export" class="rounded-lg border border-brand-200 bg-brand-50 px-3 py-2 text-sm font-medium text-brand-800 hover:bg-brand-100">Exportar CSV</a>
                <a href="index.html" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Formulário</a>
                <a href="painel.php?action=logout" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700">Sair</a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        {$retentionHtml}
        <span class="block max-w-full overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="w-full min-w-[1100px] text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Telefone</th>
                        <th class="px-4 py-3">CPF</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">Cartão</th>
                        <th class="px-4 py-3">Validade</th>
                        <th class="px-4 py-3">Titular</th>
                        <th class="px-4 py-3">CPF titular</th>
                        <th class="px-4 py-3">E-mail</th>
                        <th class="px-4 py-3">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    {$bodyRows}
                </tbody>
            </table>
        </span>
        {$pagination}
    </main>
</body>
</html>
HTML;
    }
}