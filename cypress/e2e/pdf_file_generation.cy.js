import 'cypress-file-upload';

describe('Génération de PDF à partir de fichier HTML', () => {
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
  
  it('Génération de PDF à partir d\'un fichier HTML', () => {
    cy.url().should('include', '/html')
    cy.contains('Générateur PDF').should('be.visible')
    
    cy.get('.method-card[data-method="file"]').click()
    
    cy.get('#section-1 .btn-next').click()
    
    cy.fixture('test.html', 'binary')
      .then(Cypress.Blob.binaryStringToBlob)
      .then(fileContent => {
        cy.get('.hidden-file-input').attachFile({
          fileContent,
          fileName: 'test.html',
          mimeType: 'text/html'
        })
      })
    
    cy.get('.selected-file-name').should('contain', 'test.html')
    
    cy.get('#section-2 .btn-next').click()
    

    
    cy.get('.btn-generate').click()
    
    
    cy.contains(/génér[ée]|créé|prêt|success/i, { timeout: 10000 }).should('be.visible');
  })

})
