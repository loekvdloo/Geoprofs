describe("Bulk goedkeuren van verlofaanvragen", () => {

  it("Scenario: Manager keurt meerdere verlofaanvragen tegelijk goed", () => {

    /**
     * STAP 1
     * Medewerker maakt 3 verlofaanvragen aan
     */
    cy.visit("/login");

    cy.get('input[name="email"]').type("medewerker1@geoprofs.nl");
    cy.get('input[name="password"]').type("12345678");
    cy.get("form").submit();

    cy.contains("Welkom").should("exist");

    cy.visit("/verlof/aanvraag");
    cy.url().should("include", "/verlof/aanvraag");

    cy.get("#verlof_type_id").should("exist");

    cy.intercept("POST", "/api/verlof/aanvragen").as("aanvraag");

    for (let i = 1; i <= 3; i++) {
      cy.get("#verlof_type_id").select(1);
      cy.get("#start_datum").clear().type(`2026-02-0${i}`);
      cy.get("#eind_datum").clear().type(`2026-02-0${i}`);
      cy.get("#reden").clear().type(`Bulk test aanvraag ${i}`);

      cy.contains("Indienen").click();
      cy.wait("@aanvraag");
    }

    cy.contains("Bulk test aanvraag 1").should("exist");

    /**
     * STAP 2
     * Auth reset (GEEN logout)
     */
    cy.clearCookies();
    cy.clearLocalStorage();

    /**
     * STAP 3
     * Manager keurt aanvragen in bulk goed
     */
    cy.visit("/login");

    cy.get('input[name="email"]').type("manager1@geoprofs.nl");
    cy.get('input[name="password"]').type("12345678");
    cy.get("form").submit();

    cy.contains("Welkom").should("exist");

    cy.contains("Verlof").click();
    cy.contains("beoordelen").click();

    cy.url().should("include", "/verlof/beoordeling");

    cy.get("table tbody tr")
      .should("have.length.at.least", 3);

    // Selecteer meerdere aanvragen
    cy.get("table tbody tr")
      .eq(0)
      .find('input[type="checkbox"]')
      .check();

    cy.get("table tbody tr")
      .eq(1)
      .find('input[type="checkbox"]')
      .check();

    cy.contains("Bulk goedkeuren (2)")
      .should("not.be.disabled");

    cy.intercept("POST", "/verlof/bulk-accept").as("bulkAccept");

    cy.contains("Bulk goedkeuren").click();

 
  });

});
