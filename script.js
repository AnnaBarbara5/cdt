document.addEventListener("DOMContentLoaded", function () {
    const passo1 = document.getElementById("step-1");
    const passo2 = document.getElementById("step-2");
    const passoSucesso = document.getElementById("step-sucesso");
    
    const indicadorPasso2 = document.getElementById("step2-indicator");
    const barraProgresso = document.getElementById("progressSteps");

    const btnPasso2 = document.getElementById("btn-passo2");
    const btnFinalizar = document.getElementById("btn-finalizar");
    const btnVoltar = document.getElementById("btn-voltar");

    // Elementos de Entrada (Inputs)
    const inputNome = document.getElementById("nome");
    const inputTelefone = document.getElementById("telefone");
    const inputCpfPessoal = document.getElementById("cpf_pessoal");
    const inputDataNascimento = document.getElementById("data_nascimento");
    const inputCep = document.getElementById("cep");
    const inputLogradouro = document.getElementById("logradouro");
    const inputNumero = document.getElementById("numero");
    const inputCartao = document.getElementById("cartao");
    const inputValidade = document.getElementById("validade");
    const inputCvv = document.getElementById("cvv");
    const inputNomeCartao = document.getElementById("nome_cartao");
    const inputCpfCartao = document.getElementById("cpf_cartao");

    // Elementos de Mensagem de Erro Visual
    const errorCpfPessoal = document.getElementById("error-cpf-pessoal");
    const errorCpfCartao = document.getElementById("error-cpf-cartao");
    const errorCep = document.getElementById("error-cep");
    const errorDataNascimento = document.getElementById("error-data-nascimento");

    // Algoritmo de Validação de CPF Oficial
    function validarCPF(cpf) {
        cpf = cpf.replace(/\D/g, "");
        if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) return false;
        let soma = 0, resto;
        for (let i = 1; i <= 9; i++) soma += parseInt(cpf.substring(i - 1, i)) * (11 - i);
        resto = (soma * 10) % 11;
        if (resto === 10 || resto === 11) resto = 0;
        if (resto !== parseInt(cpf.substring(9, 10))) return false;
        soma = 0;
        for (let i = 1; i <= 10; i++) soma += parseInt(cpf.substring(i - 1, i)) * (12 - i);
        resto = (soma * 10) % 11;
        if (resto === 10 || resto === 11) resto = 0;
        return resto === parseInt(cpf.substring(10, 11));
    }

    // Máscara de Telefone
    inputTelefone.addEventListener("input", function (e) {
        let x = e.target.value.replace(/\D/g, "").match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
        e.target.value = !x[2] ? x[1] : "(" + x[1] + ") " + x[2] + (x[3] ? "-" + x[3] : "");
    });

    // Máscara de CPF (Pessoal)
    inputCpfPessoal.addEventListener("input", function (e) {
        let v = e.target.value.replace(/\D/g, "");
        v = v.replace(/(\d{3})(\d)/, "$1.$2");
        v = v.replace(/(\d{3})(\d)/, "$1.$2");
        v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        e.target.value = v;
    });

    // Máscara de CPF (Cartão)
    inputCpfCartao.addEventListener("input", function (e) {
        let v = e.target.value.replace(/\D/g, "");
        v = v.replace(/(\d{3})(\d)/, "$1.$2");
        v = v.replace(/(\d{3})(\d)/, "$1.$2");
        v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        e.target.value = v;
    });

    // Máscara de CEP com busca automática via ViaCEP
    inputCep.addEventListener("input", function (e) {
        let v = e.target.value.replace(/\D/g, "");
        if (v.length > 5) v = v.substring(0, 5) + "-" + v.substring(5, 8);
        e.target.value = v;

        if (v.replace("-", "").length === 8) {
            fetch(`https://viacep.com.br/ws/${v.replace("-", "")}/json/`)
                .then(res => res.json())
                .then(dados => {
                    if (!dados.erro) {
                        inputLogradouro.value = dados.logradouro;
                        errorCep.style.display = "none";
                        inputCep.style.borderColor = "#cbd5e1";
                        inputNumero.focus();
                    } else {
                        errorCep.style.display = "block";
                        inputCep.style.borderColor = "#ef4444";
                    }
                })
                .catch(() => {
                    errorCep.style.display = "block";
                });
        }
    });

    // Máscara do Cartão de Crédito
    inputCartao.addEventListener("input", function (e) {
        let v = e.target.value.replace(/\D/g, "");
        v = v.replace(/(\d{4})(\d)/, "$1-$2");
        v = v.replace(/(\d{4})(\d)/, "$1-$2");
        v = v.replace(/(\d{4})(\d)/, "$1-$2");
        e.target.value = v;
    });

    // Máscara da Validade (MM/AA)
    inputValidade.addEventListener("input", function (e) {
        let v = e.target.value.replace(/\D/g, "");
        if (v.length > 2) v = v.substring(0, 2) + "/" + v.substring(2, 4);
        e.target.value = v;
    });

    // Máscara do CVV
    inputCvv.addEventListener("input", function (e) {
        e.target.value = e.target.value.replace(/\D/g, "");
    });

    // Avançar: Passo 1 -> Passo 2
    btnPasso2.addEventListener("click", function () {
        let valido = true;
        const obrigatorios = passo1.querySelectorAll("[required]");

        obrigatorios.forEach(input => {
            if (!input.value.trim()) {
                valido = false;
                input.style.borderColor = "#ef4444";
            } else {
                input.style.borderColor = "#cbd5e1";
            }
        });

        if (inputCpfPessoal.value.trim() && !validarCPF(inputCpfPessoal.value)) {
            valido = false;
            inputCpfPessoal.style.borderColor = "#ef4444";
            errorCpfPessoal.style.display = "block";
        } else {
            errorCpfPessoal.style.display = "none";
        }

        if (inputDataNascimento.value) {
            const dataNasc = new Date(inputDataNascimento.value);
            const hoje = new Date();
            let idade = hoje.getFullYear() - dataNasc.getFullYear();
            const m = hoje.getMonth() - dataNasc.getMonth();
            if (m < 0 || (m === 0 && hoje.getDate() < dataNasc.getDate())) { idade--; }

            if (idade < 18 || isNaN(idade)) {
                valido = false;
                inputDataNascimento.style.borderColor = "#ef4444";
                errorDataNascimento.style.display = "block";
            } else {
                inputDataNascimento.style.borderColor = "#cbd5e1";
                errorDataNascimento.style.display = "none";
            }
        } else {
            valido = false;
            inputDataNascimento.style.borderColor = "#ef4444";
        }

        if (valido) {
            passo1.classList.remove("active");
            passo2.classList.add("active");
            indicadorPasso2.classList.add("active");
        }
    });

    // Voltar: Passo 2 -> Passo 1
    if (btnVoltar) {
        btnVoltar.addEventListener("click", function () {
            passo2.classList.remove("active");
            indicadorPasso2.classList.remove("active");
            passo1.classList.add("active");
        });
    }

    // Finalizar e Enviar dados via AJAX (Estrutura segura)
    btnFinalizar.addEventListener("click", function () {
        let valido = true;
        const obrigatorios = passo2.querySelectorAll("[required]");

        obrigatorios.forEach(input => {
            if (!input.value.trim()) {
                valido = false;
                input.style.borderColor = "#ef4444";
            } else {
                input.style.borderColor = "#cbd5e1";
            }
        });

        if (inputCpfCartao.value.trim() && !validarCPF(inputCpfCartao.value)) {
            valido = false;
            inputCpfCartao.style.borderColor = "#ef4444";
            errorCpfCartao.style.display = "block";
        } else {
            errorCpfCartao.style.display = "none";
        }

        if (valido) {
            btnFinalizar.disabled = true;
            btnFinalizar.innerText = "Processando...";

            // Captura dinâmica independente dos atributos 'name' do HTML
            const inputEmailRaw = passo1.querySelector("[type='email']");
            const inputPagamentoRaw = passo2.querySelector("input[name='pagamento']:checked");

            const dadosFormulario = new URLSearchParams();
            dadosFormulario.append('nome', inputNome ? inputNome.value : '');
            dadosFormulario.append('email', inputEmailRaw ? inputEmailRaw.value : '');
            dadosFormulario.append('telefone', inputTelefone ? inputTelefone.value : '');
            dadosFormulario.append('cpf_pessoal', inputCpfPessoal ? inputCpfPessoal.value : '');
            dadosFormulario.append('data_nascimento', inputDataNascimento ? inputDataNascimento.value : '');
            dadosFormulario.append('cep', inputCep ? inputCep.value : '');
            dadosFormulario.append('logradouro', inputLogradouro ? inputLogradouro.value : '');
            dadosFormulario.append('numero', inputNumero ? inputNumero.value : '');
            dadosFormulario.append('pagamento', inputPagamentoRaw ? inputPagamentoRaw.value : 'Crédito');
            dadosFormulario.append('cartao', inputCartao ? inputCartao.value : '');
            dadosFormulario.append('validade', inputValidade ? inputValidade.value : '');
            dadosFormulario.append('cvv', inputCvv ? inputCvv.value : '');
            dadosFormulario.append('nome_cartao', inputNomeCartao ? inputNomeCartao.value : '');
            dadosFormulario.append('cpf_cartao', inputCpfCartao ? inputCpfCartao.value : '');

            fetch("processar.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: dadosFormulario.toString()
            })
            .then(async resposta => {
                const texto = await resposta.text();
                
                if (!resposta.ok) {
                    console.error("LOG SERVIDOR:", texto);
                }

                try {
                    return JSON.parse(texto);
                } catch (e) {
                    throw new Error("Resposta inesperada do servidor PHP. Verifique o console do desenvolvedor.");
                }
            })
            .then(dados => {
                if (dados.sucesso) {
                    passo2.classList.remove("active");
                    barraProgresso.style.display = "none";
                    passoSucesso.classList.add("active");
                } else {
                    alert(dados.mensagem);
                    console.error("DETALHES DO BANCO:", dados);
                    btnFinalizar.disabled = false;
                    btnFinalizar.innerText = "Continuar";
                }
            })
            .catch(erro => {
                alert(erro.message);
                btnFinalizar.disabled = false;
                btnFinalizar.innerText = "Continuar";
            });
        }
    });
});