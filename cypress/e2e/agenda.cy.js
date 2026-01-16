describe("GeoProfs Verlof Agenda E2E", () => {
  beforeEach(() => {
    // ga naar login
    cy.visit("/login");

    // login als manager
    cy.get('input[name="email"]').type("manager1@geoprofs.nl");
    cy.get('input[name="password"]').type("12345678");
    cy.get("form").submit();

    // check of dashboard geladen is
    cy.contains("Welkom").should("exist");
  });

  it("Kan naar verlofpagina navigeren via dashboard", () => {
    // klik op Verlof (menu / knop)
    cy.contains("Verlof").click();

    // check URL
    cy.url().should("include", "/verlof");
  });

  it("Kan agenda openen", () => {
    // naar verlof
    cy.contains("Verlof").click();

    // klik Agenda
    cy.contains("agenda").click();

    // check agenda pagina
    cy.url().should("include", "/verlof/agenda");

    // check of dagen renderen
    cy.contains("Ma").should("exist");
    cy.contains("Di").should("exist");
    cy.contains("Wo").should("exist");
  });

  
});
