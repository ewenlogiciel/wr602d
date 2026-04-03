describe('Page profil', () => {
    beforeEach(() => {
        cy.login()
        cy.visit('/profile')
    })

    it('affiche les informations de l\'utilisateur connecté', () => {
        cy.get('input[name="profile_form[firstname]"]').should('have.value', 'Test')
        cy.get('input[name="profile_form[lastname]"]').should('have.value', 'User')
    })

    it('met à jour le numéro de téléphone', () => {
        cy.get('input[name="profile_form[phone]"]').clear().type('+33 6 00 00 00 00')
        cy.get('button[type="submit"]').first().click()
        cy.contains(/profil mis à jour|succès/i).should('exist')
    })

    it('affiche une erreur si le mot de passe actuel est incorrect', () => {
        cy.get('input[name="current_password"]').type('mauvais_mdp')
        cy.get('input[name="new_password"]').type('nouveau123')
        cy.get('input[name="confirm_password"]').type('nouveau123')
        cy.contains('button', /changer|modifier/i).click()
        cy.contains(/incorrect|invalide|wrong/i).should('exist')
    })

    it('affiche le plan actuel de l\'utilisateur', () => {
        cy.contains(/basic/i).should('exist')
    })
})
