import { defineConfig } from "@playwright/test";
import path from "path";
import { fileURLToPath } from "url";

const root = path.dirname(fileURLToPath(import.meta.url));
const php =
    process.platform === "win32"
        ? process.env.PHP_BIN || "C:\\xampp\\php\\php.exe"
        : process.env.PHP_BIN || "php";

export default defineConfig({
    testDir: "tests/e2e",
    timeout: 60000,
    use: {
        baseURL: "http://127.0.0.1:4173",
        trace: "on-first-retry",
    },
    webServer: {
        command: `"${php}" -S 127.0.0.1:4173 -t .`,
        cwd: root,
        port: 4173,
        reuseExistingServer: !process.env.CI,
        env: {
            ...process.env,
            APP_ENV: "testing",
            APP_DEBUG: "false",
            DB_DRIVER: "sqlite",
            DB_DATABASE: path.join(root, "data", "e2e-playwright.sqlite"),
            PAYMENT_GATEWAY: "mock",
        },
    },
});