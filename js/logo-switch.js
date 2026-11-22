import { appConfig, loadAppConfig } from "./app-config.js";

const LOGOS = {
    classica: {
        marca: "imagens/LOGOMARCA.svg",
        icone: "imagens/LOGOTIPO.svg",
    },
    alternativa: {
        marca: "imagens/LOGOMARCA-alt.svg",
        icone: "imagens/LOGOTIPO-alt.svg",
    },
};

const STORAGE_KEY = "cdt-logo-variant";

function applyLogoVariant(variant) {
    const pack = LOGOS[variant] ?? LOGOS.classica;
    const img = document.getElementById("site-logo");
    if (img) img.src = pack.marca;

    const favicon = document.querySelector("link[rel='icon']");
    if (favicon) favicon.href = pack.icone;

    document.querySelectorAll("[data-logo-variant]").forEach((btn) => {
        const active = btn.dataset.logoVariant === variant;
        btn.setAttribute("aria-pressed", active ? "true" : "false");
        btn.classList.toggle("bg-brand-600", active);
        btn.classList.toggle("text-white", active);
        btn.classList.toggle("bg-slate-100", !active);
        btn.classList.toggle("text-slate-600", !active);
    });

    try {
        localStorage.setItem(STORAGE_KEY, variant);
    } catch {
        /* ignorar */
    }
}

document.addEventListener("DOMContentLoaded", async () => {
    await loadAppConfig();

    const switcher = document.getElementById("logo-switcher");
    if (switcher && !appConfig.showLogoSwitcher) {
        switcher.classList.add("hidden");
        switcher.setAttribute("aria-hidden", "true");
    }

    let saved = "classica";
    try {
        const v = localStorage.getItem(STORAGE_KEY);
        if (v && LOGOS[v]) saved = v;
    } catch {
        /* ignorar */
    }

    applyLogoVariant(saved);

    document.querySelectorAll("[data-logo-variant]").forEach((btn) => {
        btn.addEventListener("click", () => applyLogoVariant(btn.dataset.logoVariant));
    });
});