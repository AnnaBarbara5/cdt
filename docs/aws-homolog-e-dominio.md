# Homologação barata + domínio/HTTPS

## Homologação (`cdt-hml`)

Perfil **cost_saver_mode**: 1 AZ, **sem NAT Gateway**, ECS em subnet pública. RDS continua privado na VPC (acesso só da app).

### Provisionar

```powershell
.\scripts\aws-provision-homolog.ps1
```

Isso usa o workspace Terraform `homolog` e `terraform.homolog.tfvars`.

### Publicar imagem

```powershell
.\scripts\aws-publish.ps1 -ProjectName cdt-hml
```

### GitHub Actions

1. Provisione homolog com `project_name = cdt-hml` e `github_org_repo` no tfvars.
2. Secret: **`AWS_DEPLOY_ROLE_ARN_HOMOLOG`** (output `github_deploy_role_arn` do workspace homolog).
3. Actions → **Deploy AWS** → escolha **homolog**.

---

## Domínio e HTTPS (produção)

### Opção A — Automático (Route 53 na mesma conta)

No `terraform.tfvars`:

```hcl
domain_name     = "cadastro.seudominio.com.br"
route53_zone_id = "Z0XXXXXXXXXXXX"   # Console Route 53 → hosted zone
cost_saver_mode = false
```

```powershell
.\scripts\aws-provision.ps1
```

O Terraform cria:

- Certificado **ACM** com validação DNS
- Registros de validação no Route 53
- **Alias A** apontando o domínio para o ALB
- Listener **HTTPS** + redirect HTTP → HTTPS
- `SESSION_SECURE=true` na aplicação

Após `apply`, aguarde alguns minutos até o certificado ficar **Issued**.

URL final no output `app_url`: `https://cadastro.seudominio.com.br`

### Opção B — Certificado manual

1. Emita o certificado no ACM (validação DNS ou e-mail).
2. No `terraform.tfvars`: `certificate_arn = "arn:aws:acm:..."`
3. Crie manualmente o CNAME/alias no seu DNS apontando para o DNS do ALB.

### CORS em produção

Se o front chamar API em outro host:

```hcl
allowed_cors_origins = "https://cadastro.seudominio.com.br"
```

---

## Comparativo de custo (ordem de grandeza)

| Item              | Produção     | Homolog (`cost_saver_mode`) |
|-------------------|-------------|-----------------------------|
| NAT Gateway       | ~2 × uso    | **0**                       |
| AZs               | 2           | 1                           |
| RDS + ALB + Fargate | similar   | similar (1 task)            |

Homolog não substitui backup/snapshot em produção; use `environment = staging` e `deletion_protection = false` no RDS de teste.