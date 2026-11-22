import { normalizarErrosApi } from "./erros.js";

let modalAberto = false;
let mostrarDebugGlobal = false;
let focoAnterior = null;

function getRefs() {
    return {
        root: document.getElementById("modal-feedback"),
        titulo: document.getElementById("modal-feedback-titulo"),
        subtitulo: document.getElementById("modal-feedback-subtitulo"),
        lista: document.getElementById("modal-feedback-lista"),
        debugWrap: document.getElementById("modal-feedback-debug"),
        debugText: document.getElementById("modal-feedback-debug-text"),
        icone: document.getElementById("modal-feedback-icone"),
        painel: document.getElementById("modal-feedback-painel"),
    };
}

export function setModalDebugVisible(visivel) {
    mostrarDebugGlobal = Boolean(visivel);
}

function focusableNoModal(root) {
    return [...root.querySelectorAll("button, [href], input, select, textarea, [tabindex]:not([tabindex='-1'])")].filter(
        (el) => !el.hasAttribute("disabled") && el.offsetParent !== null,
    );
}

function fecharModal() {
    const { root } = getRefs();
    if (!root) return;
    root.classList.add("hidden");
    root.setAttribute("aria-hidden", "true");
    document.body.classList.remove("overflow-hidden");
    modalAberto = false;
    if (focoAnterior instanceof HTMLElement) {
        focoAnterior.focus();
    }
    focoAnterior = null;
}

function renderLista(lista, itens) {
    lista.innerHTML = "";
    lista.setAttribute("aria-live", "polite");
    lista.setAttribute("role", "list");
    itens.forEach((item) => {
        const li = document.createElement("li");
        li.className = "rounded-lg border border-red-100 bg-red-50/60 px-3 py-2 text-sm text-slate-800";
        li.setAttribute("role", "listitem");
        li.innerHTML = `<strong class="block text-red-800">${item.mensagem}</strong>`;
        if (item.campo && item.campo !== "geral") {
            const meta = document.createElement("span");
            meta.className = "mt-1 block text-xs text-slate-500";
            meta.textContent = `Campo: ${item.campo} · Código: ${item.codigo}`;
            li.appendChild(meta);
        }
        lista.appendChild(li);
    });
}

/**
 * @param {{ tipo?: 'erro'|'aviso', titulo: string, subtitulo?: string, itens: Array<{codigo?:string,campo?:string,mensagem:string,depuracao?:string}>, depuracao?: string, mostrarDebug?: boolean }} opcoes
 */
export function abrirModalFeedback(opcoes) {
    const refs = getRefs();
    if (!refs.root) return;

    focoAnterior = document.activeElement;

    const tipo = opcoes.tipo ?? "erro";
    refs.titulo.textContent = opcoes.titulo;
    refs.subtitulo.textContent = opcoes.subtitulo ?? "";
    refs.subtitulo.classList.toggle("hidden", !opcoes.subtitulo);

    renderLista(refs.lista, opcoes.itens);

    const depuracao =
        opcoes.depuracao ??
        opcoes.itens.map((e) => `[${e.codigo ?? "—"}] ${e.depuracao ?? ""}`).filter(Boolean).join("\n");

    const exibirDebug = opcoes.mostrarDebug ?? mostrarDebugGlobal;
    refs.debugWrap.classList.toggle("hidden", !exibirDebug || !depuracao);
    refs.debugText.textContent = depuracao;

    refs.icone.textContent = tipo === "erro" ? "!" : "i";
    refs.icone.className =
        tipo === "erro"
            ? "flex h-11 w-11 items-center justify-center rounded-full bg-red-100 text-xl font-bold text-red-700"
            : "flex h-11 w-11 items-center justify-center rounded-full bg-amber-100 text-xl font-bold text-amber-800";

    refs.root.classList.remove("hidden");
    refs.root.setAttribute("aria-hidden", "false");
    document.body.classList.add("overflow-hidden");
    modalAberto = true;

    const btn = refs.root.querySelector("[data-modal-fechar].btn-primary, [data-modal-fechar]:not(.absolute)");
    btn?.focus();
}

export function initModalFeedback() {
    const { root } = getRefs();
    if (!root) return;

    root.querySelectorAll("[data-modal-fechar]").forEach((el) => {
        el.addEventListener("click", fecharModal);
    });

    root.addEventListener("keydown", (ev) => {
        if (!modalAberto || ev.key !== "Tab") return;
        const focusables = focusableNoModal(root);
        if (focusables.length === 0) return;
        const first = focusables[0];
        const last = focusables[focusables.length - 1];
        if (ev.shiftKey && document.activeElement === first) {
            ev.preventDefault();
            last.focus();
        } else if (!ev.shiftKey && document.activeElement === last) {
            ev.preventDefault();
            first.focus();
        }
    });

    document.addEventListener("keydown", (ev) => {
        if (modalAberto && ev.key === "Escape") fecharModal();
    });
}

export function modalErroValidacao(titulo, erros, subtitulo = "Corrija os itens abaixo para continuar.") {
    abrirModalFeedback({
        tipo: "erro",
        titulo,
        subtitulo,
        itens: erros,
        mostrarDebug: mostrarDebugGlobal,
    });
}

/** @param {{ mensagem?: string, erros?: unknown[] }} payload */
export function modalErroApi(payload, httpStatus = "", debug = mostrarDebugGlobal) {
    const itens = normalizarErrosApi(payload.erros);
    if (itens.length === 0) {
        itens.push({
            codigo: "api",
            campo: "geral",
            mensagem: payload.mensagem || "Não foi possível concluir o cadastro.",
            depuracao: httpStatus ? `HTTP ${httpStatus}` : "",
        });
    }
    abrirModalFeedback({
        tipo: "erro",
        titulo: payload.mensagem || "Erro ao processar cadastro",
        subtitulo: "Detalhes de cada campo:",
        itens,
        depuracao: httpStatus ? `status_http=${httpStatus}` : undefined,
        mostrarDebug: debug,
    });
}