import { test, expect } from '@playwright/test';

test.describe('TC-US001-VERLOF-01 – succesvolle verlofaanvraag', () => {
    test('werknemer dient verlofaanvraag in en ziet deze in overzicht', async ({ page }) => {
        const baseURL = 'http://127.0.0.1:8000';

        // Debug: log failed requests/responses (super handig bij "select blijft leeg")
        page.on('requestfailed', (req) => {
            console.log('REQUEST FAILED:', req.method(), req.url(), req.failure()?.errorText);
        });
        page.on('response', async (res) => {
            const url = res.url();
            if (url.includes('/api/') && res.status() >= 400) {
                console.log('API ERROR:', res.status(), url);
                try {
                    const txt = await res.text();
                    console.log('API BODY:', txt.slice(0, 500));
                } catch {}
            }
        });

        // 1) Login pagina
        await page.goto(`${baseURL}/login`);

        await page.locator('#email').fill('medewerker@geoprofs.nl');
        await page.locator('#password').fill('12345678');

        await page.getByRole('button', { name: /inloggen/i }).click();

        // 2) Wacht tot we op dashboard zitten
        await page.waitForURL(/\/dashboard/, { timeout: 10000 });

        // 3) BELANGRIJK: wacht tot token bestaat (Login.jsx zet token na /api/login)
        await page.waitForFunction(() => {
            return !!window.localStorage.getItem('token');
        }, { timeout: 10000 });

        // 4) Naar verlofaanvraag
        await page.goto(`${baseURL}/verlof/aanvraag`);
        await expect(page.getByRole('heading', { name: /nieuwe verlofaanvraag/i })).toBeVisible({ timeout: 10000 });

        // 5) Verloftype selecteren (pak specifiek de juiste select)
        const verlofSelect = page.locator('select[name="verlof_type_id"]');
        await expect(verlofSelect).toBeVisible({ timeout: 10000 });

        // Wacht tot opties geladen zijn (> 1 optie)
        await page.waitForFunction(() => {
            const el = document.querySelector('select[name="verlof_type_id"]');
            return el && el.options && el.options.length > 1;
        }, { timeout: 15000 });

        // Debug dump options
        const optionDump = await verlofSelect.locator('option').evaluateAll(opts =>
            opts.map(o => ({
                value: (o.getAttribute('value') || '').trim(),
                text: (o.textContent || '').trim(),
            }))
        );
        console.log('Verloftype options:', optionDump);

        // Kies "Vakantie" als die bestaat, anders kies de eerste echte optie
        const vakantieValue = optionDump.find(o => o.text.toLowerCase().includes('vakantie'))?.value;
        const fallbackValue = optionDump.find(o => o.value && !o.text.toLowerCase().includes('kies'))?.value;

        const chosenValue = vakantieValue || fallbackValue;
        if (!chosenValue) {
            throw new Error('Geen geldig verloftype gevonden (alleen placeholder/leeg). Check API call voor verloftypes.');
        }

        await verlofSelect.selectOption(chosenValue);

        // 6) Datums invullen (type=date verwacht YYYY-MM-DD)
        await page.locator('input[name="start_datum"]').fill('2025-12-10');
        await page.locator('input[name="eind_datum"]').fill('2025-12-12');

        // 7) Reden invullen
        const reden = 'Vakantie met familie naar Spanje';
        await page.locator('textarea[name="reden"]').fill(reden);

        // 8) Indienen
        await page.getByRole('button', { name: /indienen/i }).click();

        // 9) Assertions in overzicht
        await expect(page.getByRole('heading', { name: /mijn verlofaanvragen/i })).toBeVisible({ timeout: 10000 });
        await expect(page.locator('body')).toContainText(reden);
        await expect(page.locator('body')).toContainText(/pending/i);
    });
});
