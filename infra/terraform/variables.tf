variable "aws_region" {
  type        = string
  description = "Região AWS (ex.: sa-east-1)"
  default     = "sa-east-1"
}

variable "project_name" {
  type        = string
  description = "Nome do projeto (prefixo dos recursos)"
  default     = "cdt"
}

variable "environment" {
  type        = string
  description = "Ambiente (production, staging)"
  default     = "production"
}

variable "vpc_cidr" {
  type    = string
  default = "10.20.0.0/16"
}

variable "app_desired_count" {
  type        = number
  description = "Número de tarefas ECS Fargate"
  default     = 1
}

variable "app_cpu" {
  type    = number
  default = 256
}

variable "app_memory" {
  type    = number
  default = 512
}

variable "db_instance_class" {
  type    = string
  default = "db.t4g.micro"
}

variable "db_username" {
  type    = string
  default = "cdt"
}

variable "admin_password_hash" {
  type        = string
  description = "password_hash() da senha do painel: php -r \"echo password_hash('SUA_SENHA', PASSWORD_DEFAULT), PHP_EOL;\""
  sensitive   = true
}

variable "allowed_cors_origins" {
  type        = string
  description = "CORS_ALLOWED_ORIGINS (vazio = mesma origem)"
  default     = ""
}

variable "github_org_repo" {
  type        = string
  description = "Repositório GitHub para OIDC (ex.: usuario/cdt). Vazio = sem OIDC CI"
  default     = ""
}

variable "certificate_arn" {
  type        = string
  description = "ARN do certificado ACM na mesma região (HTTPS). Vazio = só HTTP ou use domain_name"
  default     = ""
}

variable "cost_saver_mode" {
  type        = bool
  description = "Homolog: 1 AZ, sem NAT Gateway, ECS em subnet pública (bem mais barato)"
  default     = false
}

variable "domain_name" {
  type        = string
  description = "FQDN do site (ex.: cadastro.seudominio.com.br). Requer route53_zone_id"
  default     = ""
}

variable "route53_zone_id" {
  type        = string
  description = "ID da hosted zone Route 53 do domínio raiz"
  default     = ""
}