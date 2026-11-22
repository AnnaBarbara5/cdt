output "ecr_repository_url" {
  value       = aws_ecr_repository.app.repository_url
  description = "URL para docker push"
}

output "alb_dns_name" {
  value       = aws_lb.main.dns_name
  description = "URL pública (HTTP) da aplicação"
}

output "app_url" {
  value = local.managed_cert_enabled ? "https://${var.domain_name}" : (
    local.use_https ? "https://${aws_lb.main.dns_name}" : "http://${aws_lb.main.dns_name}"
  )
  description = "Link do formulário"
}

output "domain_name" {
  value       = var.domain_name
  description = "Domínio configurado (se houver)"
}

output "cost_saver_mode" {
  value = var.cost_saver_mode
}

output "rds_endpoint" {
  value       = aws_db_instance.main.address
  description = "Host MySQL (privado)"
  sensitive   = true
}

output "secrets_manager_arn" {
  value       = aws_secretsmanager_secret.app.arn
  description = "Segredos da aplicação"
}

output "ecs_cluster_name" {
  value = aws_ecs_cluster.main.name
}

output "ecs_service_name" {
  value = aws_ecs_service.app.name
}

output "github_deploy_role_arn" {
  value       = try(aws_iam_role.github_deploy[0].arn, "")
  description = "IAM role para AWS_DEPLOY_ROLE_ARN no GitHub"
}