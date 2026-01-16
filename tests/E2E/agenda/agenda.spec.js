describe('GeoProfs Verlof Agenda E2E', () => {
  const email = 'manager@geprofs.nl';
  const password = '12345678';

  beforeEach(() => {
    // ga naar login pagina
    cy.visit('/login');
  });

  it('Kan inloggen als manager', () => {
    cy.get('input[name="email"]').type(email);
    cy.get('input[name="password"]').type(password);
    cy.get('button[type="submit"]').click();

    // controleer dat we op dashboard komen
    cy.url().should('include', '/dashboard');
    cy.contains('Welkom bij GeoProfs');
  });

  it('Kan naar verlofpagina navigeren', () => {
    // log in
    cy.get('input[name="email"]').type(email);
    cy.get('input[name="password"]').type(password);
    cy.get('button[type="submit"]').click();

    // klik op "Nieuwe verlofaanvraag indienen"
    cy.contains('Nieuwe verlofaanvraag indienen').click();
    cy.url().should('include', '/verlof/aanvraag');

    // terug naar dashboard
    cy.visit('/dashboard');
  });

  it('Kan agenda openen en checken of dagen renderen', () => {
    // log in
    cy.get('input[name="email"]').type(email);
    cy.get('input[name="password"]').type(password);
    cy.get('button[type="submit"]').click();

    // klik op "Naar verlofagenda"
    cy.contains('Naar verlofagenda').click();
    cy.url().should('include', '/verlof/agenda');

    // check of dagen van de maand zichtbaar zijn
    cy.get('.grid-cols-7').should('exist');

    // check of dagen labels aanwezig zijn
    ['Ma', 'Di', 'Wo', 'Do', 'Vr', 'Za', 'Zo'].forEach((d) => {
      cy.contains(d).should('exist');
    });
  });

  it('Kan terug navigeren van agenda naar verlof', () => {
    // log in
    cy.get('input[name="email"]').type(email);
    cy.get('input[name="password"]').type(password);
    cy.get('button[type="submit"]').click();

    cy.contains('Naar verlofagenda').click();
    cy.contains('← Terug naar verlof').click();

    cy.url().should('include', '/verlof');
  });
});
