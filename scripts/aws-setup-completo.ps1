# Fluxo guiado: homolog OU producao. Credenciais apenas via `aws configure` (nunca no chat).
param(
    [ValidateSet("homolog", "production")]
    [string]$Ambiente = "homolog",
    [string]$Region = "sa-east-1"
)

$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)

function Require-Command($name, $installHint) {
    if (-not (Get-Command $name -ErrorAction SilentlyContinue)) {
        Write-Host "Falta: $name. $installHint" -ForegroundColor Red
        exit 1
    }
}

Require-Command "aws" "Instale AWS CLI v2: https://aws.amazon.com/cli/"
Require-Command "terraform" "Instale Terraform: https://developer.hashicorp.com/terraform/install"
Require-Command "docker" "Instale Docker Desktop."

Write-Host "Verificando login AWS..." -ForegroundColor Cyan
$identity = aws sts get-caller-identity --output json 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "Execute antes (na sua maquina): aws configure   ou   aws configure sso" -ForegroundColor Yellow
    exit 1
}
Write-Host $identity

$TfDir = Join-Path $Root "infra\terraform"
$isHomolog = $Ambiente -eq "homolog"
$VarExample = if ($isHomolog) { "terraform.homolog.tfvars.example" } else { "terraform.tfvars.example" }
$VarFile = if ($isHomolog) { "terraform.homolog.tfvars" } else { "terraform.tfvars" }
$ExamplePath = Join-Path $TfDir $VarExample
$TargetPath = Join-Path $TfDir $VarFile

if (-not (Test-Path $TargetPath)) {
    Copy-Item $ExamplePath $TargetPath
    Write-Host ""
    Write-Host "Edite: $TargetPath" -ForegroundColor Yellow
    Write-Host "  - admin_password_hash (php -r password_hash...)" -ForegroundColor Yellow
    if (-not $isHomolog) {
        Write-Host "  - domain_name / route53_zone_id (opcional, HTTPS)" -ForegroundColor Yellow
    }
    Write-Host "Depois execute este script novamente." -ForegroundColor Yellow
    exit 0
}

$content = Get-Content $TargetPath -Raw
if ($content -match "COLE_AQUI") {
    Write-Host "Defina admin_password_hash em $TargetPath antes de continuar." -ForegroundColor Red
    exit 1
}

$ProjectName = if ($isHomolog) { "cdt-hml" } else { "cdt" }

Push-Location $TfDir
try {
    terraform init -input=false
    if ($isHomolog) {
        terraform workspace select homolog 2>$null
        if ($LASTEXITCODE -ne 0) { terraform workspace new homolog | Out-Null }
        terraform apply -var-file=terraform.homolog.tfvars -auto-approve
    } else {
        terraform apply -auto-approve
    }
} finally {
    Pop-Location
}

& (Join-Path $Root "scripts\aws-publish.ps1") -Region $Region -ProjectName $ProjectName

Write-Host ""
Write-Host "Deploy enviado. ALB (aguarde 2-5 min para healthy):" -ForegroundColor Green
aws elbv2 describe-load-balancers --names "$ProjectName-alb" --region $Region `
    --query "LoadBalancers[0].DNSName" --output text 2>$null

Write-Host ""
Write-Host "Schema MySQL: RDS e privado. Veja docs/aws-publicacao.md secao banco." -ForegroundColor Cyan
Write-Host "URL final: terraform output app_url (em infra\terraform)" -ForegroundColor Cyan