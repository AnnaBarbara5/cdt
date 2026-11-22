terraform {
  required_version = ">= 1.5.0"

  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
    random = {
      source  = "hashicorp/random"
      version = "~> 3.6"
    }
  }

  # Descomente após criar o bucket (bootstrap em docs/aws-publicacao.md)
  # backend "s3" {
  #   bucket         = "SEU-BUCKET-TERRAFORM-STATE"
  #   key            = "cdt/terraform.tfstate"
  #   region         = "sa-east-1"
  #   encrypt        = true
  #   dynamodb_table = "cdt-terraform-locks"
  # }
}

provider "aws" {
  region = var.aws_region

  default_tags {
    tags = {
      Project     = var.project_name
      Environment = var.environment
      ManagedBy   = "terraform"
    }
  }
}