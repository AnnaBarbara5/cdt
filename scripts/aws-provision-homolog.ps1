# Provisiona stack de homologação (cost_saver_mode). Usa terraform.homolog.tfvars.
$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$TfDir = Join-Path $Root "infra\terraform"
$VarExample = Join-Path $TfDir "terraform.homolog.tfvars.example"
$VarFile = Join-Path $TfDir "terraform.homolog.tfvars"

if (-not (Test-Path $VarFile)) {
    Copy-Item $VarExample $VarFile
    Write-Host "Criado terraform.homolog.tfvars — edite admin_password_hash e execute de novo." -ForegroundColor Yellow
    exit 1
}

Push-Location $TfDir
try {
    terraform init
    terraform workspace select homolog 2>$null
    if ($LASTEXITCODE -ne 0) { terraform workspace new homolog }
    terraform plan -var-file=terraform.homolog.tfvars -out=tfplan-hml
    $ok = Read-Host "Aplicar homolog? (yes/no)"
    if ($ok -eq "yes") {
        terraform apply tfplan-hml
        terraform output
    }
} finally {
    Pop-Location
}