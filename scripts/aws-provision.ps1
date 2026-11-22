#Provisiona infraestrutura AWS (Terraform). Requer: terraform, aws CLI autenticado.
$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$TfDir = Join-Path $Root "infra\terraform"

if (-not (Test-Path (Join-Path $TfDir "terraform.tfvars"))) {
    Write-Host "Copie terraform.tfvars.example para terraform.tfvars e defina admin_password_hash." -ForegroundColor Yellow
    exit 1
}

Push-Location $TfDir
try {
    terraform init
    terraform plan -out=tfplan
    $ok = Read-Host "Aplicar este plano? (yes/no)"
    if ($ok -eq "yes") {
        terraform apply tfplan
        terraform output
    }
} finally {
    Pop-Location
}