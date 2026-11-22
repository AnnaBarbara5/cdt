<?php

declare(strict_types=1);

namespace App\Validation;

final class ValidationErrorCatalog
{
    /**
     * @return array{codigo: string, campo: string, mensagem: string, depuracao: string}
     */
    public static function entry(string $codigo): array
    {
        return match ($codigo) {
            'nome_obrigatorio' => [
                'codigo' => $codigo,
                'campo' => 'nome',
                'mensagem' => 'Informe seu nome completo, como consta no documento.',
                'depuracao' => 'POST.nome ausente ou em branco.',
            ],
            'email_invalido' => [
                'codigo' => $codigo,
                'campo' => 'email',
                'mensagem' => 'Digite um e-mail válido (ex.: nome@provedor.com).',
                'depuracao' => 'E-mail não passou em FILTER_VALIDATE_EMAIL.',
            ],
            'telefone_obrigatorio' => [
                'codigo' => $codigo,
                'campo' => 'telefone',
                'mensagem' => 'Informe um telefone com DDD para contato.',
                'depuracao' => 'POST.telefone ausente ou em branco.',
            ],
            'cpf_pessoal_invalido' => [
                'codigo' => $codigo,
                'campo' => 'cpf_pessoal',
                'mensagem' => 'O CPF informado não é válido. Revise os números e os dígitos verificadores.',
                'depuracao' => 'Falha na validação algorítmica do CPF pessoal.',
            ],
            'idade_minima' => [
                'codigo' => $codigo,
                'campo' => 'data_nascimento',
                'mensagem' => 'É necessário ter pelo menos 18 anos para se filiar.',
                'depuracao' => 'data_nascimento indica idade inferior ao mínimo legal.',
            ],
            'cep_invalido' => [
                'codigo' => $codigo,
                'campo' => 'cep',
                'mensagem' => 'Informe um CEP válido com 8 dígitos.',
                'depuracao' => 'CEP sem 8 dígitos numéricos.',
            ],
            'logradouro_obrigatorio' => [
                'codigo' => $codigo,
                'campo' => 'logradouro',
                'mensagem' => 'Preencha o logradouro do endereço.',
                'depuracao' => 'POST.logradouro ausente ou em branco.',
            ],
            'numero_obrigatorio' => [
                'codigo' => $codigo,
                'campo' => 'numero',
                'mensagem' => 'Informe o número do endereço (use S/N se não houver número).',
                'depuracao' => 'POST.numero ausente ou em branco.',
            ],
            'pagamento_invalido' => [
                'codigo' => $codigo,
                'campo' => 'pagamento',
                'mensagem' => 'Selecione débito ou crédito como forma de pagamento.',
                'depuracao' => 'POST.pagamento diferente de debito|credito.',
            ],
            'cartao_invalido' => [
                'codigo' => $codigo,
                'campo' => 'cartao',
                'mensagem' => 'O número do cartão não é válido. Confira os dígitos ou tente outro cartão.',
                'depuracao' => 'Falha no algoritmo de Luhn ou quantidade de dígitos fora de 13–19.',
            ],
            'validade_invalida' => [
                'codigo' => $codigo,
                'campo' => 'validade',
                'mensagem' => 'A validade deve estar no formato MM/AA e não pode estar vencida.',
                'depuracao' => 'Formato MM/AA inválido ou data de expiração no passado.',
            ],
            'cvv_invalido' => [
                'codigo' => $codigo,
                'campo' => 'cvv',
                'mensagem' => 'O código de segurança (CVV) deve ter 3 ou 4 dígitos.',
                'depuracao' => 'CVV fora do padrão numérico 3–4 dígitos.',
            ],
            'nome_cartao_obrigatorio' => [
                'codigo' => $codigo,
                'campo' => 'nome_cartao',
                'mensagem' => 'Informe o nome exatamente como impresso no cartão.',
                'depuracao' => 'POST.nome_cartao ausente ou em branco.',
            ],
            'cpf_cartao_invalido' => [
                'codigo' => $codigo,
                'campo' => 'cpf_cartao',
                'mensagem' => 'O CPF do titular do cartão não é válido.',
                'depuracao' => 'Falha na validação algorítmica do CPF do titular.',
            ],
            'csrf_invalido' => [
                'codigo' => $codigo,
                'campo' => 'geral',
                'mensagem' => 'Sessão expirada ou token de segurança inválido. Recarregue a página e tente novamente.',
                'depuracao' => 'csrf_token ausente ou diferente da sessão.',
            ],
            'rate_limit' => [
                'codigo' => $codigo,
                'campo' => 'geral',
                'mensagem' => 'Muitas tentativas em pouco tempo. Aguarde alguns minutos e tente novamente.',
                'depuracao' => 'Rate limit excedido para o IP/bucket informado.',
            ],
            default => [
                'codigo' => 'desconhecido',
                'campo' => 'geral',
                'mensagem' => 'Verifique os dados do formulário e tente novamente.',
                'depuracao' => 'Código de erro não catalogado: ' . $codigo,
            ],
        };
    }
}