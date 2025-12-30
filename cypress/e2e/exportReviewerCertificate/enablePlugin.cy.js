describe('Export Reviewer Certificate Plugin - Enable', () => {
  beforeEach(() => {
    // Suppress uncaught exceptions from OJS
    cy.on('uncaught:exception', (err, runnable) => {
      return false;
    });
  });

  it('Should enable the Export Reviewer Certificate plugin', () => {
    // Login as admin using credentials from environment
    const adminUser = Cypress.env('adminUser');
    const adminPassword = Cypress.env('adminPassword');
    cy.login(adminUser, adminPassword);

    // Navigate to plugins page for 'rdp' journal
    cy.navigateToPluginSettings('rdp');

    // Enable the plugin
    cy.enablePlugin('exportreviewercertificateplugin');

    // Verify plugin is enabled
    cy.get('[id*="select-cell-exportreviewercertificateplugin-enable"]')
      .should('be.checked');

    cy.log('Export Reviewer Certificate Plugin is enabled successfully');
  });
});