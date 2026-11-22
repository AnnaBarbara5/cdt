import {
    validarCPF,
    validarEmail,
    validarIdadeMinima,
    formatarTelefone,
    formatarCpf,
    formatarCep,
    formatarCartao,
    formatarValidade,
    validarCPF as validarCpfCartao,
    validarCartao,
    validarValidadeCartao,
    validarCvv,
    montarPayloadFormulario,
} from "./validation.js";
import { erroPorCodigo } from "./erros.js";
import { setInputState } from "./ui.js";

function pushErro(erros, codigo, depuracaoExtra = "") {
    erros.push(erroPorCodigo(codigo, depuracaoExtra));
}

export function validarPasso1(campos, mostrarErro) {
    const erros = [];
    let valido = true;

    const codigoCampoVazio = {
        nome: "nome_obrigatorio",
        email: "email_invalido",
        telefone: "telefone_obrigatorio",
        cpf_pessoal: "cpf_pessoal_invalido",
        data_nascimento: "idade_minima",
        cep: "cep_invalido",
        logradouro: "logradouro_obrigatorio",
        numero: "numero_obrigatorio",
    };

    for (const [chave, input] of Object.entries(campos.inputs)) {
        if (input.hasAttribute("required") && !input.value.trim()) {
            valido = false;
            setInputState(input, false);
            pushErro(erros, codigoCampoVazio[chave] ?? "campo_obrigatorio", `campo vazio: ${chave}`);
        } else if (input.hasAttribute("required")) {
            setInputState(input, true);
        }
    }

    if (campos.email?.value?.trim() && !validarEmail(campos.email.value)) {
        valido = false;
        setInputState(campos.email, false);
        pushErro(erros, "email_invalido");
    }

    if (campos.cpfPessoal.value.trim() && !validarCPF(campos.cpfPessoal.value)) {
        valido = false;
        setInputState(campos.cpfPessoal, false);
        mostrarErro(campos.errorCpfPessoal, true);
        pushErro(erros, "cpf_pessoal_invalido");
    } else {
        setInputState(campos.cpfPessoal, true);
        mostrarErro(campos.errorCpfPessoal, false);
    }

    if (!validarIdadeMinima(campos.dataNascimento.value, 18)) {
        valido = false;
        setInputState(campos.dataNascimento, false);
        mostrarErro(campos.errorDataNascimento, true);
        pushErro(erros, "idade_minima");
    } else {
        setInputState(campos.dataNascimento, true);
        mostrarErro(campos.errorDataNascimento, false);
    }

    return { valido, erros: deduplicarErros(erros) };
}

function deduplicarErros(erros) {
    const visto = new Set();
    return erros.filter((e) => {
        const chave = `${e.codigo}:${e.campo}`;
        if (visto.has(chave)) return false;
        visto.add(chave);
        return true;
    });
}

export function validarPasso2(campos, mostrarErro) {
    const erros = [];
    let valido = true;

    campos.obrigatorios.forEach((input) => {
        if (!input.value.trim()) {
            valido = false;
            setInputState(input, false);
            pushErro(erros, "campo_obrigatorio", `campo vazio: ${input.id || "pagamento"}`);
        } else {
            setInputState(input, true);
        }
    });

    if (campos.cartao?.value.trim() && !validarCartao(campos.cartao.value)) {
        valido = false;
        setInputState(campos.cartao, false);
        if (campos.errorCartao) mostrarErro(campos.errorCartao, true);
        pushErro(erros, "cartao_invalido");
    } else if (campos.cartao) {
        setInputState(campos.cartao, true);
        if (campos.errorCartao) mostrarErro(campos.errorCartao, false);
    }

    if (campos.validade?.value.trim() && !validarValidadeCartao(campos.validade.value)) {
        valido = false;
        setInputState(campos.validade, false);
        if (campos.errorValidade) mostrarErro(campos.errorValidade, true);
        pushErro(erros, "validade_invalida");
    } else if (campos.validade) {
        setInputState(campos.validade, true);
        if (campos.errorValidade) mostrarErro(campos.errorValidade, false);
    }

    if (campos.cvv?.value.trim() && !validarCvv(campos.cvv.value)) {
        valido = false;
        setInputState(campos.cvv, false);
        if (campos.errorCvv) mostrarErro(campos.errorCvv, true);
        pushErro(erros, "cvv_invalido");
    } else if (campos.cvv) {
        setInputState(campos.cvv, true);
        if (campos.errorCvv) mostrarErro(campos.errorCvv, false);
    }

    if (campos.cpfCartao.value.trim() && !validarCpfCartao(campos.cpfCartao.value)) {
        valido = false;
        setInputState(campos.cpfCartao, false);
        mostrarErro(campos.errorCpfCartao, true);
        pushErro(erros, "cpf_cartao_invalido");
    } else {
        setInputState(campos.cpfCartao, true);
        mostrarErro(campos.errorCpfCartao, false);
    }

    return { valido, erros: deduplicarErros(erros) };
}

export function coletarDados(campos, csrfToken = "") {
    const payload = {
        nome: campos.nome.value,
        email: campos.email.value,
        telefone: campos.telefone.value,
        cpf_pessoal: campos.cpfPessoal.value,
        data_nascimento: campos.dataNascimento.value,
        cep: campos.cep.value,
        logradouro: campos.logradouro.value,
        numero: campos.numero.value,
        pagamento: campos.pagamento?.value ?? "debito",
        cartao: campos.cartao.value,
        validade: campos.validade.value,
        cvv: campos.cvv.value,
        nome_cartao: campos.nomeCartao.value,
        cpf_cartao: campos.cpfCartao.value,
    };
    if (csrfToken) {
        payload.csrf_token = csrfToken;
    }
    return montarPayloadFormulario(payload);
}

export function registrarMascaras(campos, buscarCep) {
    campos.telefone.addEventListener("input", (e) => {
        e.target.value = formatarTelefone(e.target.value);
    });
    campos.cpfPessoal.addEventListener("input", (e) => {
        e.target.value = formatarCpf(e.target.value);
    });
    campos.cpfCartao.addEventListener("input", (e) => {
        e.target.value = formatarCpf(e.target.value);
    });
    campos.cep.addEventListener("input", (e) => {
        e.target.value = formatarCep(e.target.value);
        const digits = e.target.value.replace(/\D/g, "");
        if (digits.length === 8) buscarCep(digits);
    });
    campos.cartao.addEventListener("input", (e) => {
        e.target.value = formatarCartao(e.target.value);
    });
    campos.validade.addEventListener("input", (e) => {
        e.target.value = formatarValidade(e.target.value);
    });
    campos.cvv.addEventListener("input", (e) => {
        e.target.value = e.target.value.replace(/\D/g, "");
    });
}