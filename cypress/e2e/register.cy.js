describe('Page d\'inscription', () => {
  beforeEach(() => {
    cy.visit('/register')
  })
  
  it('Création de compte valide', () => {
    const uniqueEmail = `test${Date.now()}@example.com`
    
    cy.get('#registration_form_email').type(uniqueEmail)
    cy.get('#registration_form_plainPassword').type('MotDePasse123!')
    cy.get('#registration_form_firstname').type('John')
    cy.get('#registration_form_lastname').type('Doe')
    cy.get('#registration_form_agreeTerms').check()
    
    cy.get('.btn-register').click()
    
    cy.url().should('not.include', '/register')
  })
  
  it('Création de compte invalide', () => {
    cy.get('#registration_form_email').type('email-invalide')
    cy.get('#registration_form_plainPassword').type('court')  // Mot de passe trop court
    cy.get('#registration_form_firstname').type('John')
    cy.get('#registration_form_lastname').type('Doe')
    cy.get('#registration_form_agreeTerms').check()
    
    cy.get('.btn-register').click()
    
    cy.url().should('include', '/register')
    
    cy.get('.form-errors').should('be.visible')
    cy.get('.form-errors').should('have.length.at.least', 1)
  })
})
