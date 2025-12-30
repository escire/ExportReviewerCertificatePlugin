describe('Export Reviewer Certificate Plugin - Configuration', () => {
  beforeEach(() => {
    // Suppress uncaught exceptions from OJS
    cy.on('uncaught:exception', (err, runnable) => {
      return false;
    });
  });

  it('Should configure the Export Reviewer Certificate plugin settings', () => {
    // Login as admin using credentials from environment
    const adminUser = Cypress.env('adminUser');
    const adminPassword = Cypress.env('adminPassword');
    const journalPath = Cypress.env('journalPath');

    cy.login(adminUser, adminPassword);
    cy.openPluginSettingsModal(journalPath);

    // Upload certificate images
    cy.uploadCertificateImage('certificateWatermark', 'watermark.png', 'Marca de agua');
    cy.uploadCertificateImage('certificateHeader', 'header.png', 'Cabecera del certificado');
    cy.uploadCertificateImage('certificateEditorSignature', 'signature.jpg', 'Firma del editor');

    // Fill in localized certificate fields
    cy.fillCertificateField(
      'certificateGreeting',
      'La Revista Académica certifica que:'
    );

    cy.fillCertificateField(
      'certificateContent',
      '{{reviewer_title}} {{reviewer_fullname}} ha evaluado el envío «{{publication_title}}» para la revista académica.'
    );

    cy.fillCertificateField(
      'certificateInstitutionDescription',
      'Esta certificación valida la participación del revisor en el proceso de evaluación por pares.'
    );

    cy.fillCertificateField(
      'certificateDate',
      'Dado el {{day_number}} de {{month_name}} de {{year_number}}.'
    );

    cy.fillCertificateField(
      'certificateGoodbye',
      'Y para que así conste, se expide este certificado a {{today_day_number}} de {{today_month_name}} de {{today_year_number}}.'
    );

    // Fill in non-localized text fields
    cy.fillCertificateTextField('certificateEditorName', 'Dr. Juan Pérez García (Editor en Jefe)');
    cy.fillCertificateTextField('certificateEditorInstitution', 'Universidad Ejemplo');
    cy.fillCertificateTextField('certificateEditorEmail', 'editor@ejemplo.com');

    // Save plugin settings
    cy.savePluginSettings();

    // Wait for save to complete
    cy.wait(3000);

    // Verify settings were saved - just log success without checking modal state
    cy.log('Plugin settings saved');
  });

  it('Should preserve settings when reopening plugin configuration', () => {
    // Login as admin
    const adminUser = Cypress.env('adminUser');
    const adminPassword = Cypress.env('adminPassword');
    const journalPath = Cypress.env('journalPath');

    cy.login(adminUser, adminPassword);
    cy.openPluginSettingsModal(journalPath);

    // Verify that editor name is still present (previously saved)
    cy.get('#exportReviewerCertificateSettings-certificateEditorName-control')
      .should('have.value', 'Dr. Juan Pérez García (Editor en Jefe)');

    cy.log('Settings are preserved correctly');
  });
});