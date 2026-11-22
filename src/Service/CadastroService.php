<?php

declare(strict_types=1);

namespace App\Service;

use App\ClienteCadastro;
use App\Payment\PaymentGatewayFactory;
use App\Payment\PaymentGatewayInterface;
use App\Payment\PaymentSanitizer;
use App\Repository\ClienteRepository;
use App\Validator;

final class CadastroService
{
    public function __construct(
        private readonly ClienteRepository $repository,
        private readonly PaymentGatewayInterface $gateway,
    ) {
    }

    /** @param array<string, mixed> $post
     * @return array{sucesso: bool, mensagem: string, id?: int, erros?: list<array{codigo: string, campo: string, mensagem: string, depuracao: string}>}
     */
    public function registrar(array $post): array
    {
        $errors = Validator::validateCadastroPayload($post);
        if ($errors !== []) {
            return [
                'sucesso' => false,
                'mensagem' => 'Não foi possível concluir o cadastro. Revise os campos indicados.',
                'erros' => $errors,
            ];
        }

        $tokenResult = $this->gateway->tokenize([
            'cartao' => (string) $post['cartao'],
            'validade' => (string) $post['validade'],
            'cvv' => (string) $post['cvv'],
            'pagamento' => (string) $post['pagamento'],
            'nome_cartao' => (string) $post['nome_cartao'],
            'cpf_cartao' => (string) $post['cpf_cartao'],
        ]);

        $last4 = PaymentSanitizer::lastFourDigits((string) $post['cartao']);
        $brand = PaymentSanitizer::detectBrand((string) $post['cartao']);

        $cadastro = ClienteCadastro::fromValidatedPost(
            $post,
            $tokenResult['token'],
            $last4,
            $brand
        );

        $this->repository->ensureSchema();
        $id = $this->repository->insert($cadastro);

        return [
            'sucesso' => true,
            'mensagem' => 'Cadastro recebido. Pagamento em análise.',
            'id' => $id,
        ];
    }

    public static function createDefault(): self
    {
        return new self(new ClienteRepository(), PaymentGatewayFactory::create());
    }
}