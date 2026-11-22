import { describe, expect, it, vi } from "vitest";
import { coletarDados, registrarMascaras, validarPasso1, validarPasso2 } from "../../js/form.logic.js";

function input(value = "x", required = true) {
    const el = document.createElement("input");
    el.value = value;
    if (required) el.setAttribute("required", "required");
    return el;
}

describe("form.js", () => {
    it("falha passo 1 com campos obrigatórios vazios", () => {
        const campos = {
            inputs: { nome: input("", true) },
            cpfPessoal: input("529.982.247-25"),
            dataNascimento: input("1995-05-05"),
            errorCpfPessoal: document.createElement("span"),
            errorDataNascimento: document.createElement("span"),
        };
        expect(
            validarPasso1(campos, (el, vis) => {
                el.style.display = vis ? "block" : "none";
            }).valido,
        ).toBe(false);
    });

    it("falha passo 1 com cpf e idade inválidos", () => {
        const campos = {
            inputs: { nome: input("Maria") },
            cpfPessoal: input("111.111.111-11"),
            dataNascimento: input("2015-01-01"),
            errorCpfPessoal: document.createElement("span"),
            errorDataNascimento: document.createElement("span"),
        };
        vi.useFakeTimers();
        vi.setSystemTime(new Date("2026-11-22"));
        expect(
            validarPasso1(campos, (el, vis) => {
                el.style.display = vis ? "block" : "none";
            }).valido,
        ).toBe(false);
        vi.useRealTimers();
    });

    it("valida passo 1 com sucesso", () => {
        const error = document.createElement("span");
        const campos = {
            inputs: {
                nome: input("Maria"),
                email: input("maria@test.com"),
            },
            cpfPessoal: input("529.982.247-25"),
            dataNascimento: input("1995-05-05"),
            errorCpfPessoal: error,
            errorDataNascimento: document.createElement("span"),
        };
        const ok = validarPasso1(campos, (el, vis) => {
            el.style.display = vis ? "block" : "none";
        });
        expect(ok.valido).toBe(true);
    });

    it("falha passo 2 com campos vazios", () => {
        const campos = {
            obrigatorios: [input("", true)],
            cpfCartao: input("529.982.247-25"),
            errorCpfCartao: document.createElement("span"),
        };
        expect(
            validarPasso2(campos, (el, vis) => {
                el.style.display = vis ? "block" : "none";
            }).valido,
        ).toBe(false);
    });

    it("rejeita cartão com luhn inválido no passo 2", () => {
        const campos = {
            obrigatorios: [],
            cartao: input("2255-5444-8522-3685"),
            validade: input("10/30"),
            cvv: input("454"),
            cpfCartao: input("074.924.947-18"),
            errorCartao: document.createElement("span"),
            errorValidade: document.createElement("span"),
            errorCvv: document.createElement("span"),
            errorCpfCartao: document.createElement("span"),
        };
        expect(
            validarPasso2(campos, (el, vis) => {
                el.style.display = vis ? "block" : "none";
            }).valido,
        ).toBe(false);
    });

    it("valida passo 2 com sucesso", () => {
        const campos = {
            obrigatorios: [input("4111111111111111")],
            cartao: input("4111-1111-1111-1111"),
            validade: input("12/30"),
            cvv: input("123"),
            cpfCartao: input("529.982.247-25"),
            errorCartao: document.createElement("span"),
            errorValidade: document.createElement("span"),
            errorCvv: document.createElement("span"),
            errorCpfCartao: document.createElement("span"),
        };
        expect(
            validarPasso2(campos, (el, vis) => {
                el.style.display = vis ? "block" : "none";
            }).valido,
        ).toBe(true);
    });

    it("valida passo 2 com cpf inválido", () => {
        const error = document.createElement("span");
        const cpfCartao = input("111.111.111-11");
        const campos = {
            obrigatorios: [input("4111111111111111")],
            cpfCartao,
            errorCpfCartao: error,
        };
        const ok = validarPasso2(campos, (el, vis) => {
            el.style.display = vis ? "block" : "none";
        });
        expect(ok.valido).toBe(false);
    });

    it("registra máscaras nos campos", () => {
        const telefone = input("");
        const cep = input("");
        const cartao = input("");
        const cpfPessoal = input("");
        const cpfCartao = input("");
        const validade = input("");
        const cvv = input("");
        let cepBuscado = false;
        registrarMascaras(
            {
                telefone,
                cpfPessoal,
                cpfCartao,
                cep,
                cartao,
                validade,
                cvv,
                logradouro: input(""),
                numero: input(""),
            },
            () => {
                cepBuscado = true;
            },
        );
        telefone.value = "21988887777";
        telefone.dispatchEvent(new Event("input", { bubbles: true }));
        expect(telefone.value).toContain("(");
        cpfPessoal.value = "52998224725";
        cpfPessoal.dispatchEvent(new Event("input", { bubbles: true }));
        expect(cpfPessoal.value).toContain(".");
        cpfCartao.dispatchEvent(new Event("input", { bubbles: true }));
        cartao.value = "4111111111111111";
        cartao.dispatchEvent(new Event("input", { bubbles: true }));
        expect(cartao.value).toContain("-");
        validade.value = "1230";
        validade.dispatchEvent(new Event("input", { bubbles: true }));
        expect(validade.value).toBe("12/30");
        cvv.value = "12a3";
        cvv.dispatchEvent(new Event("input", { bubbles: true }));
        expect(cvv.value).toBe("123");
        cep.value = "22760000";
        cep.dispatchEvent(new Event("input", { bubbles: true }));
        expect(cepBuscado).toBe(true);
    });

    it("coleta dados do formulário", () => {
        const radio = document.createElement("input");
        radio.type = "radio";
        radio.value = "debito";
        const payload = coletarDados({
            nome: input("Maria"),
            email: input("a@a.com"),
            telefone: input("21999999999"),
            cpfPessoal: input("529.982.247-25"),
            dataNascimento: input("1995-01-01"),
            cep: input("22760-000"),
            logradouro: input("Rua A"),
            numero: input("10"),
            pagamento: radio,
            cartao: input("4111111111111111"),
            validade: input("12/30"),
            cvv: input("123"),
            nomeCartao: input("Maria"),
            cpfCartao: input("529.982.247-25"),
        });
        expect(payload.get("pagamento")).toBe("debito");

        const semPagamento = coletarDados({
            nome: input("Maria"),
            email: input("a@a.com"),
            telefone: input("21999999999"),
            cpfPessoal: input("529.982.247-25"),
            dataNascimento: input("1995-01-01"),
            cep: input("22760-000"),
            logradouro: input("Rua A"),
            numero: input("10"),
            cartao: input("4111111111111111"),
            validade: input("12/30"),
            cvv: input("123"),
            nomeCartao: input("Maria"),
            cpfCartao: input("529.982.247-25"),
        });
        expect(semPagamento.get("pagamento")).toBe("debito");
        expect(payload.get("nome")).toBe("Maria");
    });
});