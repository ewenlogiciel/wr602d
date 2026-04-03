describe('Page d\'accueil', () => {
    it('affiche le hero et les plans tarifaires', () => {
        cy.visit('/')
        cy.contains('PDF').should('be.visible')
        cy.contains('FREE').should('be.visible')
        cy.contains('BASIC').should('be.visible')
        cy.contains('PREMIUM').should('be.visible')
    })

    it('redirige vers /login si on accède à /tools sans être connecté', () => {
        cy.visit('/tools')
        cy.url().should('include', '/login')
    })

    it('affiche le lien vers les outils après connexion', () => {
        cy.login()
        cy.visit('/')
        cy.get('a[href*="/tools"]').should('exist')
    })
})
