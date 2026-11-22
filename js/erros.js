/** Catálogo alinhado a App\Validation\ValidationErrorCatalog (PHP). */
export const CATALOGO_ERROS = {
    nome_obrigatorio: {
        codigo: "nome_obrigatorio",
        campo: "nome",
        mensagem: "Informe seu nome completo, como consta no documento.",
        depuracao: "Campo nome vazio no formulário.",
    },
    email_invalido: {
        codigo: "email_invalido",
        campo: "email",
        mensagem: "Digite um e-mail válido (ex.: nome@provedor.com).",
        depuracao: "Formato de e-mail incorreto.",
    },
    telefone_obrigatorio: {
        codigo: "telefone_obrigatorio",
        campo: "telefone",
        mensagem: "Informe um telefone com DDD para contato.",
        depuracao: "Campo telefone vazio.",
    },
    cpf_pessoal_invalido: {
        codigo: "cpf_pessoal_invalido",
        campo: "cpf_pessoal",
        mensagem: "O CPF informado não é válido. Revise os números e os dígitos verificadores.",
        depuracao: "Validação de CPF pessoal falhou.",
    },
    idade_minima: {
        codigo: "idade_minima",
        campo: "data_nascimento",
        mensagem: "É necessário ter pelo menos 18 anos para se filiar.",
        depuracao: "Data de nascimento abaixo do mínimo.",
    },
    cep_invalido: {
        codigo: "cep_invalido",
        campo: "cep",
        mensagem: "Informe um CEP válido com 8 dígitos.",
        depuracao: "CEP incompleto ou ViaCEP retornou erro.",
    },
    logradouro_obrigatorio: {
        codigo: "logradouro_obrigatorio",
        campo: "logradouro",
        mensagem: "Preencha o logradouro do endereço.",
        depuracao: "Campo logradouro vazio.",
    },
    numero_obrigatorio: {
        codigo: "numero_obrigatorio",
        campo: "numero",
        mensagem: "Informe o número do endereço (use S/N se não houver número).",
        depuracao: "Campo número vazio.",
    },
    pagamento_invalido: {
        codigo: "pagamento_invalido",
        campo: "pagamento",
        mensagem: "Selecione débito ou crédito como forma de pagamento.",
        depuracao: "Modalidade de pagamento ausente.",
    },
    cartao_invalido: {
        codigo: "cartao_invalido",
        campo: "cartao",
        mensagem: "O número do cartão não é válido. Confira os dígitos ou tente outro cartão.",
        depuracao: "Falha no algoritmo de Luhn.",
    },
    validade_invalida: {
        codigo: "validade_invalida",
        campo: "validade",
        mensagem: "A validade deve estar no formato MM/AA e não pode estar vencida.",
        depuracao: "Validade fora do formato ou expirada.",
    },
    cvv_invalido: {
        codigo: "cvv_invalido",
        campo: "cvv",
        mensagem: "O código de segurança (CVV) deve ter 3 ou 4 dígitos.",
        depuracao: "CVV com tamanho inválido.",
    },
    nome_cartao_obrigatorio: {
        codigo: "nome_cartao_obrigatorio",
        campo: "nome_cartao",
        mensagem: "Informe o nome exatamente como impresso no cartão.",
        depuracao: "Campo titular do cartão vazio.",
    },
    cpf_cartao_invalido: {
        codigo: "cpf_cartao_invalido",
        campo: "cpf_cartao",
        mensagem: "O CPF do titular do cartão não é válido.",
        depuracao: "Validação do CPF do titular falhou.",
    },
    campo_obrigatorio: {
        codigo: "campo_obrigatorio",
        campo: "geral",
        mensagem: "Preencha todos os campos obrigatórios antes de continuar.",
        depuracao: "Um ou mais campos required estão vazios.",
    },
    rede_falha: {
        codigo: "rede_falha",
        campo: "geral",
        mensagem: "Não foi possível falar com o servidor. Verifique sua conexão e tente de novo.",
        depuracao: "Exceção de rede ou resposta não JSON.",
    },
    resposta_invalida: {
        codigo: "resposta_invalida",
        campo: "geral",
        mensagem: "O servidor retornou uma resposta inesperada.",
        depuracao: "JSON.parse falhou ou HTTP não OK.",
    },
};

export function erroPorCodigo(codigo, depuracaoExtra = "") {
    const base = CATALOGO_ERROS[codigo] ?? {
        codigo: "desconhecido",
        campo: "geral",
        mensagem: "Ocorreu um erro ao validar os dados.",
        depuracao: `Código não mapeado: ${codigo}`,
    };
    return {
        ...base,
        depuracao: depuracaoExtra ? `${base.depuracao} | ${depuracaoExtra}` : base.depuracao,
    };
}

/** @param {unknown} lista */
export function normalizarErrosApi(lista) {
    if (!Array.isArray(lista) || lista.length === 0) {
        return [];
    }
    return lista.map((item) => {
        if (typeof item === "string") {
            return {
                codigo: "legado",
                campo: "geral",
                mensagem: item,
                depuracao: "Mensagem legada do servidor.",
            };
        }
        if (item && typeof item === "object" && "mensagem" in item) {
            return {
                codigo: item.codigo ?? "api",
                campo: item.campo ?? "geral",
                mensagem: String(item.mensagem),
                depuracao: String(item.depuracao ?? ""),
            };
        }
        return erroPorCodigo("desconhecido");
    });
}