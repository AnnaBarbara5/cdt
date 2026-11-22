locals {
  az_count = var.cost_saver_mode ? 1 : 2
  azs      = slice(data.aws_availability_zones.available.names, 0, local.az_count)
  enable_nat = !var.cost_saver_mode

  ecs_subnet_ids         = var.cost_saver_mode ? aws_subnet.public[*].id : aws_subnet.private[*].id
  ecs_assign_public_ip   = var.cost_saver_mode

  managed_cert_enabled = var.domain_name != "" && var.route53_zone_id != ""
  effective_certificate_arn = local.managed_cert_enabled ? aws_acm_certificate.app[0].arn : var.certificate_arn
  use_https                 = local.effective_certificate_arn != ""
}