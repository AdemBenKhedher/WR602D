describe('Page mon compte', () => {
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
        
        cy.visit('/mon-compte')
      })

  it('Affiche le titre de la page', () => {
    cy.contains('h1', 'Mon compte').should('be.visible')
  })

  it('Affiche les informations personnelles', () => {
    cy.contains('h2', 'Informations personnelles').should('be.visible')
    cy.get('.account-info').should('be.visible')
    cy.contains('.info-label', 'Nom').should('exist')
    cy.contains('.info-label', 'Prénom').should('exist')
    cy.contains('.info-label', 'Email').should('exist')
    cy.contains('.info-label', 'Rôle').should('exist')
  })

  it('Affiche les actions du compte', () => {
    cy.contains('h2', 'Actions du compte').should('be.visible')
    cy.get('.account-actions').should('be.visible')
    cy.contains('a', 'Historique des PDF').should('exist')
    cy.contains('Se déconnecter').should('exist')
  })

  it('Contient la zone de danger', () => {
    cy.get('.danger-zone').should('be.visible')
    cy.contains('h2', 'Zone de danger').should('exist')
    cy.contains('Supprimer mon compte').should('exist')
  })

  it('A une fonctionnalité de confirmation pour la suppression de compte', () => {
    cy.get('#delete-account-btn').click()
    cy.get('#confirmation-modal').should('be.visible')
    cy.contains('Confirmation de suppression').should('be.visible')
    cy.get('#cancel-delete-btn').click()
    cy.get('#confirmation-modal').should('not.be.visible')
  })

  it('Permet de se déconnecter', () => {
    cy.contains('a', 'Se déconnecter').should('be.visible').click()
    cy.url().should('not.include', '/mon-compte')
    cy.contains('a', 'Se connecter').should('exist')
  })

  it('Peut afficher les informations d\'abonnement si présentes', () => {
    cy.get('body').then($body => {
      if ($body.find('.subscription-info').length > 0) {
        cy.contains('h2', 'Abonnement actuel').should('be.visible')
        cy.get('.subscription-info').should('be.visible')
        cy.get('.subscription-name').should('exist')
        cy.get('.subscription-price').should('exist')
        cy.get('.btn-change-plan').should('exist')
      } else {
        cy.log('Aucun abonnement trouvé pour cet utilisateur')
      }
    })
  })

  it('Devenir un administrateur', () => {
    cy.visit('/login')
    cy.get('#username').type('ademkhdher@gmail.com')
    cy.get('#password').type('12345678')
    cy.get('.login-button').click()
    cy.url().should('not.include', '/login')
    cy.visit('/mon-compte')
    
    cy.get('body').then($body => {
      if ($body.find('.admin-role-indicator').length > 0) {
        cy.get('.admin-role-indicator').should('be.visible')
        cy.log('L\'utilisateur est déjà administrateur')
      } else {
        cy.get('.admin-btn').should('be.visible').click()
        
        cy.url().should('not.include', '/mon-compte')
      }
    })
  })
})
