// ***********************************************
// This file can be used to create custom Cypress commands
// and overwrite existing ones.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************

// -- This is a parent command --
// Improving the login command to optionally check for email verification
Cypress.Commands.add('login', (email, password, checkVerification = false) => {
  cy.session([email, password], () => {
    cy.visit('/login')
    cy.get('#username').type(email)
    cy.get('#password').type(password)
    cy.get('.login-button').click()
    cy.url().should('not.include', '/login')
    
    cy.contains('Déconnexion').should('be.visible')
    
    if (checkVerification) {
      cy.get('.email-verification-warning').should('not.exist')
      
      cy.url().should('not.include', '/verify-email')
    }
  })
})

import 'cypress-file-upload'

// Cypress.Commands.add('login', (email, password) => { ... })
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
