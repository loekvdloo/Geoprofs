import { test, expect } from '@playwright/test';

test.use({ browserName: 'firefox' });

test.describe('TC-US001-VERLOF-02 – ongeldige periode', () => {
    test('einddatum vóór startdatum wordt afgekeurd en er wordt niets opgeslagen', async ({ page, request }) => {
        const baseUrl = 'http://127.0.0.1:8000';

        const email = 'medewerker1@geoprofs.nl';
        const password = '12345678';

        // ongeldig: eind < start
        const startDatum = '2025-12-10';
        const eindDatum = '2025-12-05';
        const reden = `Test ongeldige periode ${Date.now()}`;

        // (optioneel) log relevante failures
        page.on('response', (res) => {
            if (res.status() >= 400) {
                const url = res.url();
                if (url.includes('/api/') || url.includes('/verlof') || url.includes('/login')) {
                    console.log(`HTTP ${res.status()} -> ${url}`);
                }
            }
        });

        // 1) WEB LOGIN (sessie cookie)
        await page.goto(`${baseUrl}/login`, { waitUntil: 'domcontentloaded' });
        await page.locator('#email').fill(email);
        await page.locator('#password').fill(password);

        await Promise.all([
            page.waitForURL('**/dashboard', { timeout: 20000 }),
            page.getByRole('button', { name: /inloggen/i }).click(),
        ]);

        await expect(page.locator('body')).toContainText(/logout/i, { timeout: 20000 });

        // 2) API LOGIN (token zoals je echte Login.jsx ook doet)
        const apiLogin = await request.post(`${baseUrl}/api/login`, {
            data: { email, password },
        });

        expect(apiLogin.ok()).toBeTruthy();

        const apiJson = await apiLogin.json();
        const token = apiJson?.access_token;

        if (!token) {
            throw new Error(`API login gaf geen access_token terug. Response: ${JSON.stringify(apiJson)}`);
        }

        // Token in localStorage zetten op dezelfde origin
        await page.evaluate((t) => {
            localStorage.setItem('token', t);
        }, token);

        // 3) NAAR VERLOFAANVRAAG
        await page.goto(`${baseUrl}/verlof/aanvraag`, { waitUntil: 'domcontentloaded' });
        await page.waitForLoadState('networkidle', { timeout: 20000 });

        // Guard-message mag NIET meer verschijnen
        await expect(page.locator('body')).not.toContainText(/geen geldige login gevonden/i, { timeout: 5000 });
        await expect(page.locator('body')).toContainText(/nieuwe verlofaanvraag/i, { timeout: 20000 });

        // 4) Aantal rows vóór indienen
        const rows = page.locator('table tbody tr');
        const beforeCount = await rows.count();

        // 5) Verloftype kiezen (native select)
        const nativeSelect = page.locator('select[name="verlof_type_id"]');
        await expect(nativeSelect).toBeVisible({ timeout: 20000 });

        // wacht tot opties geladen zijn
        await expect(async () => {
            const count = await nativeSelect.locator('option').count();
            expect(count).toBeGreaterThan(1);
        }).toPass({ timeout: 20000 });

        const options = await nativeSelect.locator('option').evaluateAll((opts) =>
            opts.map((o) => ({ value: o.value, text: (o.textContent || '').trim() }))
        );

        const vakantie = options.find((o) => /vakantie/i.test(o.text) && o.value);
        if (!vakantie) {
            throw new Error(`Geen verloftype met "Vakantie" gevonden. Opties: ${JSON.stringify(options)}`);
        }

        await nativeSelect.selectOption(vakantie.value);

        // 6) Datums + reden invullen
        await page.locator('input[name="start_datum"]').fill(startDatum);
        await page.locator('input[name="eind_datum"]').fill(eindDatum);

        const redenField = page.locator('textarea[name="reden"]').or(page.locator('input[name="reden"]'));
        await expect(redenField).toBeVisible({ timeout: 20000 });
        await redenField.fill(reden);

        // 7) Indienen
        await page.getByRole('button', { name: /indienen/i }).click();

        // 8) Verwacht foutmelding (jullie UI had eerder “Kon aanvraag niet indienen.”)
        await expect(page.locator('body')).toContainText(
            /kon aanvraag niet indienen|ongeldig|einddatum|startdatum|periode/i,
            { timeout: 20000 }
        );

        // 9) Er mag geen nieuwe rij bij zijn gekomen
        await expect(async () => {
            const afterCount = await rows.count();
            expect(afterCount).toBe(beforeCount);
        }).toPass({ timeout: 20000 });

        // 10) reden mag niet in tabel staan
        await expect(page.locator('table')).not.toContainText(reden);
    });
});

