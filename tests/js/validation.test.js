import { describe, expect, it, vi } from "vitest";
import {
    digitsOnly,
    formatarCartao,
    formatarCep,
    formatarCpf,
    formatarTelefone,
    formatarValidade,
    luhnCheck,
    montarPayloadFormulario,
    validarCartao,
    validarCep,
    validarCPF,
    validarCvv,
    validarEmail,
    validarIdadeMinima,
    validarValidadeCartao,
} from "../../js/validation.js";

describe("validation.js", () => {
    it("valida CPF", () => {
        expect(validarCPF("529.982.247-25")).toBe(true);
        expect(validarCPF("111.111.111-11")).toBe(false);
    });

    it("valida e-mail e CEP", () => {
        expect(validarEmail("a@b.com")).toBe(true);
        expect(validarEmail("invalido")).toBe(false);
        expect(validarCep("22760-000")).toBe(true);
    });

    it("valida idade mínima", () => {
        vi.useFakeTimers();
        vi.setSystemTime(new Date("2026-11-22"));
        expect(validarIdadeMinima("1995-01-01", 18)).toBe(true);
        expect(validarIdadeMinima("2015-01-01", 18)).toBe(false);
        expect(validarIdadeMinima("2008-11-23", 18)).toBe(false);
        vi.useRealTimers();
        expect(validarIdadeMinima("", 18)).toBe(false);
    });

    it("valida cartão e cvv", () => {
        expect(luhnCheck("5555555555554444")).toBe(true);
        expect(luhnCheck("4111111111111111")).toBe(true);
        expect(validarCartao("4111-1111-1111-1111")).toBe(true);
        expect(validarCvv("123")).toBe(true);
        expect(validarValidadeCartao("12/30")).toBe(true);
        expect(validarValidadeCartao("01/20")).toBe(false);
    });

    it("valida cartão com tamanhos inválidos", () => {
        expect(validarCartao("123")).toBe(false);
        expect(validarCartao("4111111111111112")).toBe(false);
    });

    it("valida e-mail vazio e nulo", () => {
        expect(validarEmail("")).toBe(false);
        expect(validarEmail(null)).toBe(false);
    });

    it("valida cpf com dígito incorreto", () => {
        expect(validarCPF("529.982.247-00")).toBe(false);
    });

    it("formata telefone parcial", () => {
        expect(formatarTelefone("21")).toBe("21");
    });

    it("digitsOnly com nulo e validade inválida", () => {
        expect(digitsOnly(null)).toBe("");
        expect(validarValidadeCartao("13/30")).toBe(false);
    });

    it("aplica máscaras e monta payload", () => {
        expect(digitsOnly("(21) 9")).toBe("219");
        expect(formatarTelefone("21988887777")).toContain("(21)");
        expect(formatarCpf("52998224725")).toBe("529.982.247-25");
        expect(formatarCep("22760000")).toBe("22760-000");
        expect(formatarCartao("4111111111111111")).toContain("-");
        expect(formatarValidade("1230")).toBe("12/30");
        const payload = montarPayloadFormulario({ nome: "Ana" });
        expect(payload.get("nome")).toBe("Ana");
    });
});