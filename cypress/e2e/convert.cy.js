describe('Conversion PDF', () => {
    beforeEach(() => {
        cy.login()
    })

    // ── URL vers PDF ────────────────────────────────────────────
    describe('URL vers PDF', () => {
        it('affiche le champ URL', () => {
            cy.visit('/convert/url')
            cy.get('input[name="url"]').should('be.visible')
        })

        it('convertit une URL et ouvre le PDF dans un nouvel onglet', () => {
            // Intercepte le POST et renvoie un faux PDF
            cy.intercept('POST', '/convert/url', {
                statusCode: 200,
                headers: { 'Content-Type': 'application/pdf' },
                body: '%PDF-1.4 mock',
            }).as('convertUrl')

            cy.on('window:before:load', (win) => {
                cy.stub(win, 'open').as('windowOpen')
            })

            cy.visit('/convert/url')
            cy.get('input[name="url"]').type('https://example.com')
            cy.get('button[type="submit"]').click()

            cy.wait('@convertUrl')
            cy.get('@windowOpen').should('have.been.called')
        })
    })

    // ── Éditeur WYSIWYG ─────────────────────────────────────────
    describe('Éditeur WYSIWYG', () => {
        it('affiche l\'éditeur Quill', () => {
            cy.visit('/convert/wysiwyg')
            cy.get('#quill-editor', { timeout: 5000 }).should('be.visible')
        })

        it('convertit le contenu de l\'éditeur en PDF', () => {
            cy.intercept('POST', '/convert/wysiwyg', {
                statusCode: 200,
                headers: { 'Content-Type': 'application/pdf' },
                body: '%PDF-1.4 mock',
            }).as('convertWysiwyg')

            cy.on('window:before:load', (win) => {
                cy.stub(win, 'open').as('windowOpen')
            })

            cy.visit('/convert/wysiwyg')
            cy.get('#quill-editor .ql-editor', { timeout: 5000 }).type('Test de conversion WYSIWYG')
            cy.get('button[type="submit"]').click()

            cy.wait('@convertWysiwyg')
            cy.get('@windowOpen').should('have.been.called')
        })
    })

    // ── Markdown vers PDF ───────────────────────────────────────
    describe('Markdown vers PDF', () => {
        it('affiche le champ d\'upload Markdown', () => {
            cy.visit('/convert/markdown')
            cy.get('input[name="file"]').should('exist')
        })
    })

    // ── Contrôle d'accès ────────────────────────────────────────
    describe('Contrôle d\'accès', () => {
        it('redirige vers /tools si l\'outil est réservé PREMIUM', () => {
            cy.visit('/convert/fusionner')
            cy.url().should('include', '/tools')
            cy.contains('Votre plan actuel ne donne pas accès à cet outil.').should('exist')
        })
    })
})
