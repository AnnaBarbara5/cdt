# Publicação na AWS (conta própria)

Arquitetura: **ECS Fargate** + **ALB** + **RDS MySQL** + **ECR** + **Secrets Manager**.

## Pré-requisitos

- [AWS CLI](https://docs.aws.amazon.com/cli/latest/userguide/getting-started-install.html) com `aws configure` ou SSO
- [Terraform](https://developer.hashicorp.com/terraform/install) >= 1.5
- [Docker Desktop](https://www.docker.com/products/docker-desktop/)
- Conta AWS com permissões para VPC, ECS, RDS, ECR, IAM, Secrets Manager

## 1. Gerar hash do painel

```powershell
C:\xampp\php\php.exe -r "echo password_hash('SUA_SENHA_FORTE', PASSWORD_DEFAULT), PHP_EOL;"
```

## 2. Provisionar infraestrutura (uma vez)

```powershell
cd infra\terraform
copy terraform.tfvars.example terraform.tfvars
# Edite terraform.tfvars (admin_password_hash, github_org_repo, região)

..\..\scripts\aws-provision.ps1
```

Anote os outputs: `app_url`, `ecr_repository_url`, `github_deploy_role_arn`.

## 3. Banco de dados

O RDS fica **em rede privada**. Para o primeiro `schema.sql`:

- Use **AWS Session Manager** com um bastion, ou
- Temporariamente: `publicly_accessible = true` no Terraform (não recomendado em produção), ou
- Importe o schema a partir de uma máquina na mesma VPC.

Com cliente MySQL e túnel:

```powershell
.\scripts\aws-db-migrate.ps1
```

## 4. Primeira imagem e deploy

```powershell
.\scripts\aws-publish.ps1 -Region sa-east-1
```

Aguarde o target group ficar **healthy** (~2–5 min).

Acesse o `app_url` do Terraform (`http://<alb-dns>/index.html`).

## 5. Deploy automático (GitHub Actions)

1. No Terraform, defina `github_org_repo` no `terraform.tfvars` e reaplique.
2. Copie `github_deploy_role_arn` para o secret do repositório: **`AWS_DEPLOY_ROLE_ARN`**
3. Pushes na `main` (arquivos da app) disparam `.github/workflows/deploy-aws.yml`.

## 6. Variáveis em produção

| Origem | Variável |
|--------|----------|
| Task definition | `APP_ENV`, `DB_*`, `SESSION_SECURE`, etc. |
| Secrets Manager | `DB_PASSWORD`, `ADMIN_PASSWORD_HASH`, `STRIPE_SECRET_KEY` |

Edite segredos no console AWS → Secrets Manager (`cdt/production/app`).

## 7. Homolog e domínio/HTTPS

Guia detalhado: **[aws-homolog-e-dominio.md](aws-homolog-e-dominio.md)**

- Homolog: `.\scripts\aws-provision-homolog.ps1` + `aws-publish.ps1 -ProjectName cdt-hml`
- HTTPS com Route 53: `domain_name` + `route53_zone_id` no `terraform.tfvars`

## 8. Custos aproximados

Produção (2 AZ + NAT): dezenas de USD/mês. Homolog com `cost_saver_mode`: sem NAT, custo bem menor.

## Comandos úteis

```powershell
aws ecs describe-services --cluster cdt-cluster --services cdt-service --region sa-east-1
aws logs tail /ecs/cdt --follow --region sa-east-1
```

## Estado do Terraform (recomendado)

Crie um bucket S3 + tabela DynamoDB para lock e descomente o `backend` em `versions.tf`.