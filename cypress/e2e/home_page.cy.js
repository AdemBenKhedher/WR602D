describe('Page d\'accueil', () => {
  it('Visite la page d\'accueil', () => {
    cy.visit('/')
    cy.contains('h1', 'Bienvenue').should('be.visible')
  })
})
