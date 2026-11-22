param(
    [switch]$SkipPhp
)

$ErrorActionPreference = "Stop"
Set-Location (Split-Path -Parent $PSScriptRoot)

Write-Host "==> Testes JavaScript (Vitest + Playwright)"
npm ci
npm run build:css
npm run test:unit
npx playwright install chromium
npm run test:e2e

if (-not $SkipPhp) {
    if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
        Write-Warning "PHP não encontrado. Instale PHP 8.3+ e Composer, ou execute com -SkipPhp."
        exit 0
    }
    if (-not (Get-Command composer -ErrorAction SilentlyContinue)) {
        Write-Warning "Composer não encontrado. Baixe em https://getcomposer.org/download/"
        exit 0
    }
    Write-Host "==> Testes PHP (PHPUnit + cobertura)"
    composer install --no-interaction
    vendor/bin/phpunit --coverage-text
}