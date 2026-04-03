const { defineConfig } = require('cypress')

module.exports = defineConfig({
  e2e: {
    baseUrl: 'http://localhost:8319',
    supportFile: 'cypress/support/commands.js',
  },
})
