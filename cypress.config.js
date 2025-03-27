const { defineConfig } = require('cypress')

module.exports = defineConfig({
  e2e: {
    baseUrl: null,
    specPattern: 'cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',
    supportFile: 'cypress/support/e2e.js',
    setupNodeEvents(on, config) {
    },
  },
})
