# Importa sql/schema.sql no RDS (requer túnel ou bastion). Uso local se RDS for público temporário.
param(
    [string]$Region = "sa-east-1",
    [string]$ProjectName = "cdt",
    [string]$DbHost = "",
    [string]$DbUser = "cdt",
    [string]$DbName = "cdt"
)

$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$Schema = Join-Path $Root "sql\schema.sql"

if (-not $DbHost) {
    $TfDir = Join-Path $Root "infra\terraform"
    Push-Location $TfDir
    $DbHost = terraform output -raw rds_endpoint 2>$null
    Pop-Location
}

if (-not $DbHost) {
    throw "Informe -DbHost ou aplique o Terraform antes."
}

$SecretArn = aws secretsmanager list-secrets --region $Region `
    --query "SecretList[?contains(Name, '${ProjectName}/production/app')].ARN | [0]" --output text

$SecretJson = aws secretsmanager get-secret-value --secret-id $SecretArn --region $Region --query SecretString --output text
$DbPass = ($SecretJson | ConvertFrom-Json).DB_PASSWORD

Write-Host "Aplicando schema em $DbHost ..."
$env:MYSQL_PWD = $DbPass
mysql -h $DbHost -u $DbUser $DbName < $Schema
Remove-Item Env:MYSQL_PWD
Write-Host "Schema aplicado."