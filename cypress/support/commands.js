// Commande réutilisable pour se connecter avant chaque test
Cypress.Commands.add('login', () => {
    cy.visit('/login')
    cy.get('#inputEmail').type(Cypress.env('USER_EMAIL'))
    cy.get('#inputPassword').type(Cypress.env('USER_PASSWORD'))
    cy.get('button[type="submit"]').click()
    cy.url().should('not.include', '/login')
})
