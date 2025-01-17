describe('Export Reviewer Certificate plugin tests', () => {
  it('Export Reviewer Certificate is enable', function () {
    cy.login('mcruzescire', 'PqaEtiiZ.2So.');
    cy.visit('https://turia.uv.test/index.php/celestinesca/management/settings/website#plugins');
    cy.get('#cell-exportreviewercertificateplugin-name').should('exist').then(($element) => {
      cy.get('[id*="select-cell-exportreviewercertificateplugin-enable"]').should('exist')
        .then(($checkbox) => {
          if (!$checkbox.is(':checked')) {
            cy.log("Atention, Plugin is not enabled, starting enable");
            cy.wrap($checkbox).check();
            cy.log("OK, Plugin is enabled");
          }
          else{
            cy.log("OK, Plugin is enabled");
          }
        });
    });
  });
})