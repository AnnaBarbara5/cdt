resource "aws_secretsmanager_secret" "app" {
  name = "${var.project_name}/${var.environment}/app"
}

resource "aws_secretsmanager_secret_version" "app" {
  secret_id = aws_secretsmanager_secret.app.id
  secret_string = jsonencode({
    DB_PASSWORD         = random_password.db.result
    ADMIN_PASSWORD_HASH = var.admin_password_hash
    STRIPE_SECRET_KEY   = ""
  })

  lifecycle {
    ignore_changes = [secret_string]
  }
}