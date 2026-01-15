import { defineConfig, devices } from "@playwright/test";

export default defineConfig({
    timeout: 60000, // globale timeout 60s
    testDir: "./tests/E2E",
    use: {
        baseURL: "http://localhost:8000",
        headless: false, // zodat je ziet wat er gebeurt
        viewport: { width: 1280, height: 720 },
    },
    projects: [
        {
            name: "firefox",
            use: { ...devices["Desktop Firefox"] },
        },
        {
            name: "chromium",
            use: { ...devices["Desktop Chrome"] },
        },
    ],
});
