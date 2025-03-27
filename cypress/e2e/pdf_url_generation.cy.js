describe('Génération de PDF à partir d\'URL', () => {
  beforeEach(() => {
    cy.session('authenticated-user', () => {
      cy.visit('/login')
      cy.get('#username').type('ademkhdher@gmail.com')
      cy.get('#password').type('12345678')
      cy.get('.login-button').click()
      cy.url().should('not.include', '/login')
      
      cy.contains('Déconnexion').should('be.visible')
      cy.url().should('not.include', '/verify-email')
    })
    
    cy.visit('/pdf')
  })

  it('Génération de PDF avec URL invalide', () => {
    cy.url().should('include', '/pdf')
    
    cy.get('.url-input').type('invalid-url')
    
    cy.get('button[type="submit"]').click()
    
    cy.url().should('include', '/pdf')
    
    cy.get('.alert-danger').should('contain', 'URL invalide')
    
    cy.get('#pdf-form').should('be.visible')
  })

  it('Génération de PDF à partir d\'une URL', () => {
    cy.url().should('include', '/pdf')

    cy.get('.url-input').type('https://sparksuite.github.io/simple-html-invoice-template/')
    
    cy.get('button[type="submit"]').click()
    
    cy.get('.success-message').should('be.visible')
  })
})
