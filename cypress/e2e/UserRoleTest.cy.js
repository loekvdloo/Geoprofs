describe("User Role Edit", () => {
    const userId = 2;

    it("kan gebruiker inloggen en rollen/afdeling wijzigen", () => {
        // 1️⃣ Login
        cy.visit("http://localhost:8000/login");
        cy.get('input[name="email"]').type("medewerker@geoprofs.nl");
        cy.get('input[name="password"]').type("12345678");
        cy.get('button[type="submit"]').click();

        // 2️⃣ Controleer dashboard
        cy.url().should("include", "/dashboard");

        // 3️⃣ Ga naar gebruikers bewerkpagina
        cy.visit(`http://localhost:8000/admin/users/${userId}/edit`);

        // 4️⃣ Controleer dat dropdowns zichtbaar zijn
        cy.get('[data-testid="role-select"]').should("be.visible");
        cy.get('[data-testid="afdeling-select"]').should("be.visible");

        // 5️⃣ Selecteer nieuwe rol en afdeling
        cy.get('[data-testid="role-select"]').select("2"); // pas value aan zoals nodig
        cy.get('[data-testid="afdeling-select"]').select("3"); // pas value aan zoals nodig

        // 6️⃣ Klik opslaan
        cy.get('[data-testid="save-button"]').click();

        // 7️⃣ Controleer success message (tekst aanpassen aan API)
        cy.get('[data-testid="success-message"]', { timeout: 10000 }).should(
            "contain.text",
            "Rol en afdeling bijgewerkt"
        );

        // 8️⃣ Controleer backend update via API
        cy.request(`/api/admin/users/${userId}`).then((res) => {
            expect(res.status).to.eq(200);
            expect(res.body.role_id).to.eq(2);
            expect(res.body.afdeling_id).to.eq(3);
        });

        // 9️⃣ Test back-knop
        cy.get('[data-testid="back-button"]').click();
        cy.url().should("include", "/admin/users");
    });
});
