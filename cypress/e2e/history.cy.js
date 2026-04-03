describe('Historique des générations', () => {
    beforeEach(() => {
        cy.login()
    })

    it('affiche la page historique (accès BASIC)', () => {
        cy.visit('/history')
        cy.contains(/historique/i).should('be.visible')
    })

    it('affiche un état vide ou une liste de générations', () => {
        cy.visit('/history')
        cy.get('body').then(($body) => {
            if ($body.text().includes('Aucune génération')) {
                cy.contains('Aucune génération').should('be.visible')
                cy.contains('a', /générer/i).should('be.visible')
            } else {
                cy.get('a[href*="/history/"]').should('have.length.greaterThan', 0)
            }
        })
    })

    it('redirige vers /login si non connecté', () => {
        cy.clearCookies()
        cy.visit('/history')
        cy.url().should('include', '/login')
    })
})
