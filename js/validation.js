export function digitsOnly(value) {
    return String(value ?? "").replace(/\D/g, "");
}

export function validarCPF(cpf) {
    const digits = digitsOnly(cpf);
    if (digits.length !== 11 || /^(\d)\1{10}$/.test(digits)) return false;
    for (let t = 9; t < 11; t++) {
        let sum = 0;
        for (let i = 0; i < t; i++) {
            sum += parseInt(digits[i], 10) * (t + 1 - i);
        }
        let rest = (sum * 10) % 11;
        if (rest === 10 || rest === 11) rest = 0;
        if (rest !== parseInt(digits[t], 10)) return false;
    }
    return true;
}

export function validarEmail(email) {
    if (!email || typeof email !== "string") return false;
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim());
}

export function validarIdadeMinima(dataIso, minimo = 18) {
    if (!dataIso) return false;
    const nascimento = new Date(dataIso);
    if (Number.isNaN(nascimento.getTime())) return false;
    const hoje = new Date();
    let idade = hoje.getFullYear() - nascimento.getFullYear();
    const m = hoje.getMonth() - nascimento.getMonth();
    if (m < 0 || (m === 0 && hoje.getDate() < nascimento.getDate())) idade--;
    return idade >= minimo;
}

export function validarCep(cep) {
    return digitsOnly(cep).length === 8;
}

export function luhnCheck(digits) {
    let sum = 0;
    let alt = false;
    for (let i = digits.length - 1; i >= 0; i--) {
        let n = parseInt(digits[i], 10);
        if (alt) {
            n *= 2;
            if (n > 9) n -= 9;
        }
        sum += n;
        alt = !alt;
    }
    return sum % 10 === 0;
}

export function validarCartao(numero) {
    const digits = digitsOnly(numero);
    if (digits.length < 13 || digits.length > 19) return false;
    return luhnCheck(digits);
}

export function validarValidadeCartao(validade) {
    if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(validade ?? "")) return false;
    const [mes, ano] = validade.split("/");
    const exp = new Date(2000 + parseInt(ano, 10), parseInt(mes, 10), 0);
    const hoje = new Date();
    hoje.setHours(0, 0, 0, 0);
    return exp >= hoje;
}

export function validarCvv(cvv) {
    return /^\d{3,4}$/.test(digitsOnly(cvv));
}

export function formatarTelefone(value) {
    const x = digitsOnly(value).match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
    if (!x) return "";
    return !x[2] ? x[1] : `(${x[1]}) ${x[2]}${x[3] ? "-" + x[3] : ""}`;
}

export function formatarCpf(value) {
    let v = digitsOnly(value);
    v = v.replace(/(\d{3})(\d)/, "$1.$2");
    v = v.replace(/(\d{3})(\d)/, "$1.$2");
    v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
    return v;
}

export function formatarCep(value) {
    let v = digitsOnly(value);
    if (v.length > 5) v = `${v.substring(0, 5)}-${v.substring(5, 8)}`;
    return v;
}

export function formatarCartao(value) {
    let v = digitsOnly(value);
    v = v.replace(/(\d{4})(\d)/, "$1-$2");
    v = v.replace(/(\d{4})(\d)/, "$1-$2");
    v = v.replace(/(\d{4})(\d)/, "$1-$2");
    return v;
}

export function formatarValidade(value) {
    let v = digitsOnly(value);
    if (v.length > 2) v = `${v.substring(0, 2)}/${v.substring(2, 4)}`;
    return v;
}

export function montarPayloadFormulario(campos) {
    return new URLSearchParams(campos);
}