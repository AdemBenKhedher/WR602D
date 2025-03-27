describe('Génération de PDF à partir de code HTML', () => {
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
    
    cy.visit('/html')
  })
  
  it('Génération de PDF à partir de code HTML', () => {
    cy.url().should('include', '/html')
    cy.contains('Générateur PDF').should('be.visible')
    
    cy.get('.method-card[data-method="code"]').click()
    
    cy.get('#section-1 .btn-next').click()
    
    const htmlCode = '<!DOCTYPE html><html><head><title>Test Document</title></head><body><h1>Test Heading</h1><p>This is a test paragraph.</p></body></html>'
    cy.get('.code-editor').type(htmlCode)
    
    cy.get('#section-2 .btn-next').click()
    
    cy.get('.btn-generate').click()
    
    
    cy.contains(/génér[ée]|créé|prêt|success/i, { timeout: 10000 }).should('be.visible');
    
  })
})
