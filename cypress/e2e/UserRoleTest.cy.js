describe("User Role Edit", () => {
    const userId = 2;

    it("kan gebruiker inloggen en rollen/afdeling wijzigen", () => {
        cy.visit("http://localhost:8000/login");
        cy.get('input[name="email"]').type("medewerker@geoprofs.nl");
        cy.get('input[name="password"]').type("12345678");
        cy.get('button[type="submit"]').click();

        cy.url().should("include", "/dashboard");

        cy.visit(`http://localhost:8000/admin/users/${userId}/edit`);

        cy.get('[data-testid="role-select"]').should("be.visible");
        cy.get('[data-testid="afdeling-select"]').should("be.visible");

        cy.get('[data-testid="role-select"]').select("2");
        cy.get('[data-testid="afdeling-select"]').select("3"); 

        cy.get('[data-testid="save-button"]').click();

        cy.get('[data-testid="success-message"]', { timeout: 10000 }).should(
            "contain.text",
            "Rol en afdeling bijgewerkt"
        );

        cy.request(`/api/admin/users/${userId}`).then((res) => {
            expect(res.status).to.eq(200);
            expect(res.body.role_id).to.eq(2);
            expect(res.body.afdeling_id).to.eq(3);
        });

        cy.get('[data-testid="back-button"]').click();
        cy.url().should("include", "/admin/users");
    });
});
