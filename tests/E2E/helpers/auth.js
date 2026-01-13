import { expect } from '@playwright/test';

/**
 * Doet precies wat jullie UI doet:
 * 1) web login -> session cookie
 * 2) api login -> access_token
 * 3) token in localStorage op dezelfde origin
 */
export async function loginWebAndApiToken({ page, request, baseUrl, email, password }) {
    await page.goto(`${baseUrl}/login`, { waitUntil: 'domcontentloaded' });

    await page.locator('#email').fill(email);
    await page.locator('#password').fill(password);

    await Promise.all([
        page.waitForURL('**/dashboard', { timeout: 20000 }),
        page.getByRole('button', { name: /inloggen/i }).click(),
    ]);

    await expect(page.locator('body')).toContainText(/logout/i, { timeout: 20000 });

    const apiLogin = await request.post(`${baseUrl}/api/login`, {
        data: { email, password },
    });

    if (!apiLogin.ok()) {
        throw new Error(`API login failed: ${apiLogin.status()} ${apiLogin.statusText()}`);
    }

    const apiJson = await apiLogin.json();
    const token = apiJson?.access_token;

    if (!token) {
        throw new Error(`API login gaf geen access_token terug. Response: ${JSON.stringify(apiJson)}`);
    }

    await page.evaluate((t) => localStorage.setItem('token', t), token);

    return token;
}
