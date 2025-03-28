describe('Gestion des abonnements', () => {
  beforeEach(() => {
    cy.session('authenticated-user', () => {
      cy.visit('/login')
      cy.get('#username').type('ademkhdher@gmail.com')
      cy.get('#password').type('12345678')
      cy.get('.login-button').click()
      cy.url().should('not.include', '/login')
      
      cy.contains('Déconnexion').should('exist')
      cy.url().should('not.include', '/verify-email')
    })
    
    cy.visit('/subscription')
  })

  it('permet d\'ajouter un abonnement premium', () => {
    cy.contains('h1', 'Nos formules d\'abonnement').should('be.visible')
    
    cy.get('.premium-plan').within(() => {
      cy.contains('h3', 'Premium').should('be.visible')
      
      cy.get('.subscription-btn').contains(/Choisir cette offre|Abonnement actif/).click()
    })
    
      cy.visit('/subscription')
      cy.get('.current-plan').contains('Premium').should('exist')
    })
})
