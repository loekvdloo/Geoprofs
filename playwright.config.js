import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
    testDir: './tests/E2E',
    use: {
        baseURL: process.env.E2E_BASE_URL || 'http://127.0.0.1:8000',
        headless: false,
        screenshot: 'only-on-failure',
        video: 'retain-on-failure',
        trace: 'retain-on-failure',
    },
    projects: [
        {
            name: 'firefox',
            use: { ...devices['Desktop Firefox'] },
        },
    ],
});
