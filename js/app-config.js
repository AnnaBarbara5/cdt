/** @type {{ debug: boolean, showLogoSwitcher: boolean, csrfToken: string }} */
export const appConfig = {
    debug: false,
    csrfToken: "",
};

let ready = null;

export function loadAppConfig() {
    if (!ready) {
        ready = (async () => {
            try {
                const [cfgRes, csrfRes] = await Promise.all([
                    fetch("config-public.php"),
                    fetch("csrf-token.php", { credentials: "same-origin" }),
                ]);
                if (cfgRes.ok) {
                    const cfg = await cfgRes.json();
                    appConfig.debug = Boolean(cfg.debug);
                }
                if (csrfRes.ok) {
                    const csrf = await csrfRes.json();
                    appConfig.csrfToken = String(csrf.csrf_token ?? "");
                }
            } catch {
                /* fallback: formulário ainda valida no cliente */
            }
        })();
    }

    return ready;
}