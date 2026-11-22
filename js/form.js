import { coletarDados, registrarMascaras, validarPasso1, validarPasso2 } from "./form.logic.js";
import { erroPorCodigo } from "./erros.js";
import { initModalFeedback, modalErroApi, modalErroValidacao, setModalDebugVisible } from "./modal.js";
import { setErrorVisible, setInputState } from "./ui.js";
import { appConfig, loadAppConfig } from "./app-config.js";

document.addEventListener("DOMContentLoaded", async () => {
    await loadAppConfig();
    setModalDebugVisible(appConfig.debug);

    initModalFeedback();

    const cadastroForm = document.getElementById("financialForm");
    const campoNome = document.getElementById("nome");
    if (cadastroForm) {
        cadastroForm.scrollIntoView({ behavior: "smooth", block: "start" });
    }
    requestAnimationFrame(() => {
        campoNome?.focus({ preventScroll: true });
    });

    const passo1 = document.getElementById("step-1");
    const passo2 = document.getElementById("step-2");
    const passoSucesso = document.getElementById("step-sucesso");
    const indicadorPasso2 = document.getElementById("step2-indicator");
    const barraProgresso = document.getElementById("progressSteps");

    const campos = {
        nome: document.getElementById("nome"),
        email: document.getElementById("email"),
        telefone: document.getElementById("telefone"),
        cpfPessoal: document.getElementById("cpf_pessoal"),
        dataNascimento: document.getElementById("data_nascimento"),
        cep: document.getElementById("cep"),
        logradouro: document.getElementById("logradouro"),
        numero: document.getElementById("numero"),
        cartao: document.getElementById("cartao"),
        validade: document.getElementById("validade"),
        cvv: document.getElementById("cvv"),
        nomeCartao: document.getElementById("nome_cartao"),
        cpfCartao: document.getElementById("cpf_cartao"),
        pagamento: passo2.querySelector("input[name='pagamento']:checked"),
        errorCpfPessoal: document.getElementById("error-cpf-pessoal"),
        errorCpfCartao: document.getElementById("error-cpf-cartao"),
        errorCep: document.getElementById("error-cep"),
        errorDataNascimento: document.getElementById("error-data-nascimento"),
        errorCartao: document.getElementById("error-cartao"),
        errorValidade: document.getElementById("error-validade"),
        errorCvv: document.getElementById("error-cvv"),
        obrigatorios: passo2.querySelectorAll("[required]"),
        inputs: Object.fromEntries(
            [...passo1.querySelectorAll("[required]")].map((el) => [el.id, el]),
        ),
    };

    const mostrarErro = (el, visivel) => setErrorVisible(el, visivel);

    registrarMascaras(campos, async (digits) => {
        try {
            const res = await fetch(`https://viacep.com.br/ws/${digits}/json/`);
            const dados = await res.json();
            if (!dados.erro) {
                campos.logradouro.value = dados.logradouro;
                mostrarErro(campos.errorCep, false);
                setInputState(campos.cep, true);
                campos.numero.focus();
            } else {
                mostrarErro(campos.errorCep, true);
                setInputState(campos.cep, false);
            }
        } catch {
            mostrarErro(campos.errorCep, true);
            setInputState(campos.cep, false);
        }
    });

    document.getElementById("btn-passo2").addEventListener("click", () => {
        const resultado = validarPasso1(campos, mostrarErro);
        if (!resultado.valido) {
            modalErroValidacao("Revise seus dados pessoais", resultado.erros);
            return;
        }
        passo1.classList.remove("active");
        passo2.classList.add("active");
        indicadorPasso2.classList.add("active");
    });

    document.getElementById("btn-voltar").addEventListener("click", () => {
        passo2.classList.remove("active");
        indicadorPasso2.classList.remove("active");
        passo1.classList.add("active");
    });

    const btnFinalizar = document.getElementById("btn-finalizar");
    btnFinalizar.addEventListener("click", async () => {
        campos.pagamento = passo2.querySelector("input[name='pagamento']:checked");
        const resultado = validarPasso2(campos, mostrarErro);
        if (!resultado.valido) {
            modalErroValidacao("Revise os dados de pagamento", resultado.erros);
            return;
        }

        btnFinalizar.disabled = true;
        btnFinalizar.textContent = "Processando...";

        try {
            const body = coletarDados(campos, appConfig.csrfToken);
            const resposta = await fetch("processar.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                credentials: "same-origin",
                body: body.toString(),
            });
            const texto = await resposta.text();
            let dados;
            try {
                dados = JSON.parse(texto);
            } catch {
                modalErroValidacao("Resposta inválida do servidor", [
                    erroPorCodigo("resposta_invalida", texto.slice(0, 120)),
                ]);
                return;
            }

            if (dados.sucesso) {
                passo2.classList.remove("active");
                barraProgresso.classList.add("hidden");
                passoSucesso.classList.add("active");
            } else {
                modalErroApi(dados, String(resposta.status), appConfig.debug);
            }
        } catch (erro) {
            modalErroValidacao("Falha de comunicação", [
                erroPorCodigo("rede_falha", erro instanceof Error ? erro.message : String(erro)),
            ]);
        } finally {
            btnFinalizar.disabled = false;
            btnFinalizar.textContent = "Continuar";
        }
    });
});