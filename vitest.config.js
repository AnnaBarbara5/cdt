import { defineConfig } from "vitest/config";

export default defineConfig({
    test: {
        environment: "jsdom",
        include: ["tests/js/**/*.test.js"],
        coverage: {
            provider: "v8",
            include: ["js/validation.js", "js/form.logic.js"],
            exclude: ["js/form.js"],
            thresholds: {
                lines: 100,
                functions: 100,
                branches: 88,
                statements: 100,
            },
        },
    },
});