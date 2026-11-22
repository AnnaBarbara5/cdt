# EC2 AWS Academy: ZIP no S3 + Apache/PHP (sem Docker — mais leve no t2.micro).
param(
    [string]$Region = "us-east-1",
    [string]$KeyName = "vockey",
    [string]$InstanceType = "t2.micro",
    [switch]$Replace
)

$ErrorActionPreference = "Stop"
$Aws = "C:\Program Files\Amazon\AWSCLIV2\aws.exe"
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$instanceId = $null

& $Aws sts get-caller-identity | Out-Null
$account = (& $Aws sts get-caller-identity --query Account --output text).Trim()
$bucket = "cdt-lab-$account-$Region".ToLower()

$tfvars = Join-Path $Root "infra\terraform\terraform.homolog.tfvars"
if (-not (Test-Path $tfvars)) { throw "Crie terraform.homolog.tfvars primeiro." }
$hashLine = Get-Content $tfvars | Where-Object { $_ -match 'admin_password_hash' }
if ($hashLine -notmatch '= "(.+)"') { throw "admin_password_hash invalido em tfvars." }
$adminHash = $matches[1]

if (-not (Test-Path (Join-Path $Root "vendor\autoload.php"))) {
    Write-Host "Gerando vendor (composer dump-autoload)..." -ForegroundColor Cyan
    & 'C:\xampp\php\php.exe' (Join-Path $Root "composer.phar") dump-autoload -d $Root 2>$null
}

Write-Host "Bucket: $bucket" -ForegroundColor Cyan

& $Aws s3 mb "s3://$bucket" --region $Region 2>$null
if ($LASTEXITCODE -ne 0) {
    Write-Host "Bucket pode ja existir; continuando..." -ForegroundColor Yellow
}

$archivePath = Join-Path $env:TEMP "cdt-deploy.tar.gz"
if (Test-Path $archivePath) { Remove-Item $archivePath -Force }
$staging = Join-Path $env:TEMP "cdt-staging-$(Get-Random)"
if (Test-Path $staging) { Remove-Item $staging -Recurse -Force }
New-Item -ItemType Directory -Path $staging | Out-Null
robocopy $Root $staging /E /XD node_modules data .git .terraform test-results coverage build playwright-report infra\terraform\.terraform /NFL /NDL /NJH /NJS /nc /ns /np | Out-Null
tar -czf $archivePath -C $staging .
Remove-Item $staging -Recurse -Force

& $Aws s3 cp $archivePath "s3://$bucket/cdt-deploy.tar.gz" --region $Region
$presign = (& $Aws s3 presign "s3://$bucket/cdt-deploy.tar.gz" --region $Region --expires-in 7200).Trim()

$vpcId = (& $Aws ec2 describe-vpcs --region $Region --filters "Name=isDefault,Values=true" --query "Vpcs[0].VpcId" --output text).Trim()
$subnetId = ""
foreach ($az in @("us-east-1a", "us-east-1b", "us-east-1c", "us-east-1d", "us-east-1f")) {
    $candidate = (& $Aws ec2 describe-subnets --region $Region --filters "Name=vpc-id,Values=$vpcId" "Name=availability-zone,Values=$az" --query "Subnets[0].SubnetId" --output text).Trim()
    if ($candidate -and $candidate -ne "None") { $subnetId = $candidate; break }
}
if (-not $subnetId) { throw "Nenhuma subnet encontrada na VPC default." }
$amiId = (& $Aws ssm get-parameter --region $Region --name /aws/service/ami-amazon-linux-latest/al2023-ami-kernel-default-x86_64 --query Parameter.Value --output text).Trim()

$sgName = "cdt-lab-sg"
$sgId = (& $Aws ec2 describe-security-groups --region $Region --filters "Name=group-name,Values=$sgName" "Name=vpc-id,Values=$vpcId" --query "SecurityGroups[0].GroupId" --output text 2>$null)
if (-not $sgId -or $sgId -eq "None") {
    $sgId = (& $Aws ec2 create-security-group --region $Region --group-name $sgName --description "CDT lab HTTP SSH" --vpc-id $vpcId --query GroupId --output text).Trim()
    & $Aws ec2 authorize-security-group-ingress --region $Region --group-id $sgId --protocol tcp --port 22 --cidr 0.0.0.0/0 | Out-Null
    & $Aws ec2 authorize-security-group-ingress --region $Region --group-id $sgId --protocol tcp --port 80 --cidr 0.0.0.0/0 | Out-Null
}

$bashHash = $adminHash.Replace("'", "'\''")

$userDataTemplate = @'
#!/bin/bash
set -x
exec > /var/log/cdt-userdata.log 2>&1
dnf install -y httpd tar curl
systemctl enable --now httpd
dnf install -y php php-cli php-mbstring php-xml php-pdo php-json php-sqlite3 || dnf install -y php php-cli php-mbstring php-xml php-pdo
systemctl restart httpd
mkdir -p /var/www/html/data
curl -fsSL '__PRESIGN__' -o /tmp/cdt-deploy.tar.gz
rm -rf /var/www/html/*
tar -xzf /tmp/cdt-deploy.tar.gz -C /var/www/html
test -f /var/www/html/index.html
chown -R apache:apache /var/www/html
chmod 775 /var/www/html/data
cat > /var/www/html/.env <<'EOF'
APP_ENV=staging
APP_DEBUG=false
DB_DRIVER=sqlite
DB_DATABASE=/var/www/html/data/cdt.sqlite
PAYMENT_GATEWAY=mock
SESSION_SECURE=false
EOF
echo 'ADMIN_PASSWORD_HASH=__ADMIN_HASH__' >> /var/www/html/.env
chown apache:apache /var/www/html/.env
chmod 640 /var/www/html/.env
systemctl restart httpd
echo CDT_USERDATA_OK >> /var/log/cdt-userdata.log
'@
$userData = $userDataTemplate.Replace('__PRESIGN__', $presign).Replace('__ADMIN_HASH__', $bashHash)

$userDataB64 = [Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($userData))

$existing = & $Aws ec2 describe-instances --region $Region --filters "Name=tag:Name,Values=cdt-hml" "Name=instance-state-name,Values=pending,running,stopping,stopped" --query "Reservations[0].Instances[0].InstanceId" --output text 2>$null
if ($existing -and $existing -ne "None") {
    if ($Replace) {
        Write-Host "Terminando instancia anterior: $existing" -ForegroundColor Yellow
        & $Aws ec2 terminate-instances --region $Region --instance-ids $existing | Out-Null
        & $Aws ec2 wait instance-terminated --region $Region --instance-ids $existing
    } else {
        Write-Host "Instancia cdt-hml ja existe: $existing (use -Replace para recriar)" -ForegroundColor Yellow
        $instanceId = $existing
    }
}
if (-not $instanceId) {
    $instanceId = (& $Aws ec2 run-instances --region $Region --image-id $amiId --instance-type $InstanceType --key-name $KeyName --security-group-ids $sgId --subnet-id $subnetId --user-data $userDataB64 --tag-specifications "ResourceType=instance,Tags=[{Key=Name,Value=cdt-hml}]" --query "Instances[0].InstanceId" --output text).Trim()
    Write-Host "Instancia criada: $instanceId"
}

Write-Host "Aguardando status running..." -ForegroundColor Cyan
& $Aws ec2 wait instance-running --region $Region --instance-ids $instanceId

$ip = (& $Aws ec2 describe-instances --region $Region --instance-ids $instanceId --query "Reservations[0].Instances[0].PublicIpAddress" --output text).Trim()
Write-Host ""
Write-Host "URL: http://$ip/index.html" -ForegroundColor Green
Write-Host "Painel: http://$ip/painel.php" -ForegroundColor Green
Write-Host "Aguarde 2-4 min (Apache/PHP + unzip)." -ForegroundColor Yellow
Write-Host "Console AWS > EC2 > cdt-hml > Conectar > Instance Connect > tail /var/log/cdt-userdata.log" -ForegroundColor Cyan