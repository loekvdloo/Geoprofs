import { test, expect } from '@playwright/test';

test.use({ browserName: 'firefox' });

test.describe('TC-US001-VERLOF-03 – ontbrekende verplichte velden', () => {
    test('verlofaanvraag zonder reden wordt afgewezen en er wordt niets opgeslagen', async ({ page }) => {
        const baseUrl = process.env.APP_URL ?? 'http://127.0.0.1:8000';

        const email = 'medewerker1@geoprofs.nl';
        const password = '12345678';

        // --- Debug logging (laat staan tot alles stabiel is)
        page.on('requestfailed', (req) => console.log('REQUEST FAILED:', req.method(), req.url(), req.failure()?.errorText));
        page.on('response', (res) => {
            const url = res.url();
            if (url.includes('/verlof') || url.includes('/api/')) {
                console.log('RESPONSE:', res.status(), res.request().method(), url);
            }
        });

        // 1) Login
        await page.goto(`${baseUrl}/login`, { waitUntil: 'domcontentloaded' });
        await page.locator('#email').fill(email);
        await page.locator('#password').fill(password);

        await Promise.all([
            page.waitForLoadState('networkidle'),
            page.getByRole('button', { name: /inloggen/i }).click(),
        ]);

        // 2) Naar verlof-aanvraag pagina
        await page.goto(`${baseUrl}/verlof/aanvraag`, { waitUntil: 'domcontentloaded' });

        // Guard: als auth ontbreekt
        await expect(page.locator('body')).not.toContainText(/geen geldige login gevonden/i, { timeout: 5000 });

        // 3) Zeker weten dat we op de juiste pagina zijn
        await expect(page.getByText(/nieuwe verlofaanvraag/i)).toBeVisible({ timeout: 15000 });

        // 4) Verloftype select vinden
        let verlofSelect = page.locator('select[name="verlof_type_id"]');
        if (await verlofSelect.count() === 0) {
            verlofSelect = page.locator('select').first();
        }
        await expect(verlofSelect).toBeVisible({ timeout: 15000 });

        // Wacht tot opties geladen zijn (> 1)
        await expect(async () => {
            const count = await verlofSelect.locator('option').count();
            expect(count).toBeGreaterThan(1);
        }).toPass({ timeout: 20000 });

        // Kies "Vakantie"
        const options = await verlofSelect.locator('option').evaluateAll((opts) =>
            opts.map((o) => ({ value: o.value, text: (o.textContent || '').trim() }))
        );
        const vakantieOpt = options.find((o) => /vakantie/i.test(o.text));
        if (!vakantieOpt?.value) {
            throw new Error(`Geen verloftype met "Vakantie" gevonden. Opties: ${JSON.stringify(options)}`);
        }
        await verlofSelect.selectOption(vakantieOpt.value);

        // 5) Datums invullen (date inputs)
        const startInput =
            page.locator('input[name="start_datum"], input[name="startdatum"]').first().or(page.locator('input[type="date"]').first());
        const eindInput =
            page.locator('input[name="eind_datum"], input[name="einddatum"]').first().or(page.locator('input[type="date"]').nth(1));

        await expect(startInput).toBeVisible({ timeout: 15000 });
        await expect(eindInput).toBeVisible({ timeout: 15000 });

        await startInput.fill('2025-12-10');
        await eindInput.fill('2025-12-12');

        // 6) Reden bewust leeg laten
        // (Als er een textarea bestaat, force leeg laten zodat client-side "stale value" niet meedoet)
        const redenField = page.locator('textarea[name="reden"], textarea').first();
        if (await redenField.count()) {
            await redenField.fill('');
        }

        // 7) Huidige rows tellen
        const tableRows = page.locator('table tbody tr');
        const initialRowCount = await tableRows.count();

        // 8) Klik op de juiste submit button
        const submitBtn = page.getByRole('button', { name: /^indienen$/i });
        await expect(submitBtn).toBeVisible({ timeout: 15000 });
        await expect(submitBtn).toBeEnabled({ timeout: 15000 });

        // 9) Verwacht óf:
        // - een API POST (422) óf
        // - client-side validatie (geen request)
        // Daarom: race tussen response en timeout.
        const maybeResponse = page.waitForResponse(
            (resp) =>
                resp.request().method() === 'POST' &&
                (resp.url().includes('/api/verlof/aanvragen') ||
                    resp.url().includes('/verlof') ||
                    resp.url().includes('/verlof/aanvraag') ||
                    resp.url().includes('/verlof/aanvragen')),
            { timeout: 5000 }
        ).catch(() => null);

        await submitBtn.click();

        const resp = await maybeResponse;
        if (resp) {
            console.log(`POST gezien: HTTP ${resp.status()} -> ${resp.url()}`);
            // bij server-side validatie verwacht je meestal 400/422
            expect([400, 422, 302, 200]).toContain(resp.status());
        } else {
            console.log('Geen POST gezien -> waarschijnlijk client-side validatie blokkeert submit (ook OK).');
        }

        // 10) Foutmelding moet zichtbaar zijn (UI)
        await expect(page.locator('body')).toContainText(/kon aanvraag niet indienen|verplicht|reden/i, { timeout: 15000 });

        // 11) Geen nieuwe row toegevoegd
        await expect(async () => {
            const afterCount = await tableRows.count();
            expect(afterCount).toBe(initialRowCount);
        }).toPass({ timeout: 10000 });
    });
});
