# Build da imagem, push no ECR e novo deploy no ECS Fargate.
param(
    [string]$Region = "sa-east-1",
    [ValidateSet("cdt", "cdt-hml")]
    [string]$ProjectName = "cdt"
)

$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)

$AccountId = (aws sts get-caller-identity --query Account --output text)
if (-not $AccountId) { throw "AWS CLI não autenticado. Use: aws configure" }

$EcrUrl = "${AccountId}.dkr.ecr.${Region}.amazonaws.com/${ProjectName}-app"

aws ecr get-login-password --region $Region | docker login --username AWS --password-stdin "${AccountId}.dkr.ecr.${Region}.amazonaws.com"

Push-Location $Root
try {
    docker build -t "${ProjectName}-app:latest" .
    docker tag "${ProjectName}-app:latest" "${EcrUrl}:latest"
    docker push "${EcrUrl}:latest"

    aws ecs update-service `
        --cluster "${ProjectName}-cluster" `
        --service "${ProjectName}-service" `
        --force-new-deployment `
        --region $Region `
        --no-cli-pager | Out-Null

    Write-Host "Deploy iniciado. URL:" -ForegroundColor Green
    aws elbv2 describe-load-balancers --names "${ProjectName}-alb" --region $Region `
        --query "LoadBalancers[0].DNSName" --output text
} finally {
    Pop-Location
}