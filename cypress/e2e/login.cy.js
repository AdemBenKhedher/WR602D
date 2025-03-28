describe('Page de connexion', () => {
  beforeEach(() => {
    cy.visit('/login')
  })
  
  it('Connexion valide', () => {
    cy.get('#username').type('ademkhdher@gmail.com')
    cy.get('#password').type('12345678')
    
    cy.get('.login-button').click()
    
    cy.url().should('not.include', '/login')
    cy.contains('Déconnexion').should('be.visible')
  })
  
  it('Connexion invalide', () => {
    cy.get('#username').type('invalide@exemple.com')
    cy.get('#password').type('motdepasseincorrect')
    
    cy.get('.login-button').click()
    
    cy.url().should('include', '/login')
    
    cy.get('.alert-danger').should('be.visible')
  })
})
