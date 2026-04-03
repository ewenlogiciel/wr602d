describe('Liste des outils', () => {
    beforeEach(() => {
        cy.login()
        cy.visit('/tools')
    })

    it('affiche la liste des outils disponibles', () => {
        cy.contains('URL vers PDF').should('be.visible')
        cy.contains('HTML vers PDF').should('be.visible')
        cy.contains('Markdown vers PDF').should('be.visible')
        cy.contains('Éditeur WYSIWYG').should('be.visible')
    })

    it('affiche les outils BASIC accessibles au compte test', () => {
        cy.contains('Word vers PDF').should('be.visible')
        cy.contains('Excel vers PDF').should('be.visible')
        cy.contains('PowerPoint vers PDF').should('be.visible')
    })

    it('les outils PREMIUM sont verrouillés pour un compte BASIC', () => {
        cy.contains('Fusionner des PDF')
            .closest('div[class*="rounded"]')
            .within(() => {
                cy.contains(/upgrade|changer|premium/i).should('exist')
            })
    })

    it('le filtre FREE n\'affiche que les outils gratuits', () => {
        cy.contains('button', 'FREE').click()
        cy.contains('URL vers PDF').should('be.visible')
        cy.contains('Word vers PDF').should('not.be.visible')
    })

    it('navigue vers la page de conversion en cliquant sur un outil accessible', () => {
        cy.get('a[href*="/convert/"]').first().click()
        cy.url().should('include', '/convert/')
    })
})
