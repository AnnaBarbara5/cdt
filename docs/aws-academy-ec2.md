# Publicar CDT na AWS Academy (Vocareum)

A conta **Learner Lab** **não permite** criar IAM, ECS Fargate e RDS (como no Terraform completo).  
Use **1 EC2 + Docker + SQLite**.

## Pré-requisitos

- CLI configurada (`aws sts get-caller-identity` ok)
- Lab Vocareum **iniciado** (credenciais válidas)
- `terraform.homolog.tfvars` com `admin_password_hash`

## Automático (recomendado)

No PowerShell, na raiz do projeto:

```powershell
.\scripts\academy-launch-ec2.ps1
```

O script envia o ZIP para S3, cria a EC2 (`cdt-hml`), instala Docker via **user-data** e sobe o `docker-compose.academy.yml`.  
Ao final, mostra **URL** e **IP**. Aguarde **3–8 minutos** para o build.

Senha do painel: arquivo local `data\.painel-homolog.local.txt`.

---

## Manual: subir EC2 pelo console

1. Lab iniciado → **AWS Console** → **EC2** → **Launch instance**
2. Nome: `cdt-hml`
3. AMI: **Amazon Linux 2023**
4. Tipo: **t3.micro** (ou o permitido no lab)
5. Key pair: use a **mesma chave** do lab (ou crie e baixe)
6. Security group: permitir **SSH 22** e **HTTP 80** (origem 0.0.0.0/0 só para teste do lab)
7. Launch

## 2. Conectar (PowerShell)

```powershell
ssh -i "C:\caminho\para\sua-chave.pem" ec2-user@IP_PUBLICO_DA_EC2
```

No Linux da EC2, instale Docker:

```bash
sudo dnf update -y
sudo dnf install -y docker git
sudo systemctl enable --now docker
sudo usermod -aG docker ec2-user
# saia e entre de novo no SSH
```

## 3. Enviar o projeto

No **seu PC** (na pasta do projeto):

```powershell
scp -i "C:\caminho\chave.pem" -r c:\Projetos\cdt ec2-user@IP_DA_EC2:~/cdt
```

Ou `git clone` na EC2 se o repositório for público.

## 4. Hash do painel na EC2

No PC:

```powershell
C:\xampp\php\php.exe -d display_errors=0 -r "echo password_hash('SUA_SENHA', PASSWORD_DEFAULT), PHP_EOL;"
```

Na EC2:

```bash
cd ~/cdt
export ADMIN_PASSWORD_HASH='COLE_O_HASH'
sudo docker compose -f docker-compose.academy.yml up -d --build
```

Acesse: `http://IP_PUBLICO_DA_EC2/index.html`

## 5. Encerramento

**End Lab** apaga a EC2 — é só para demonstração da disciplina.

---

O Terraform `infra/terraform` é para conta **completa** (pessoal/empresa). Na Academy use este guia.