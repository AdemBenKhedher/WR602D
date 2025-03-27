describe('Page d\'accueil', () => {
  it('Visite la page d\'accueil', () => {
    cy.visit('http://symfony.mmi-troyes.fr:8319/')
    cy.contains('h1', 'Bienvenue').should('be.visible')
  })
})
