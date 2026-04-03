describe('Formulaire de Connexion', () => {
    it('test 1 - connexion OK', () => {
        cy.visit('/login');

        cy.get('#inputEmail').type(Cypress.env('USER_EMAIL'));
        cy.get('#inputPassword').type(Cypress.env('USER_PASSWORD'));

        cy.get('button[type="submit"]').click();

        // Après connexion réussie, on quitte /login et le header affiche le prénom
        cy.url().should('not.include', '/login');
        cy.get('.header-dropdown-name').should('exist');
    });

    it('test 2 - connexion KO', () => {
        cy.visit('/login');

        cy.get('#inputEmail').type('mauvais@example.com');
        cy.get('#inputPassword').type('mauvais_mdp');

        cy.get('button[type="submit"]').click();

        // Le message d'erreur Symfony s'affiche
        cy.contains('Invalid credentials.').should('exist');
    });
});
