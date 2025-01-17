describe('Export Reviewer Certificate plugin tests', () => {
  it('Configure Export Reviewer Certificate plugin into digital journal', function () {
    let lang;
    cy.login('mcruzescire', 'PqaEtiiZ.2So.');
    cy.visit('https://turia.uv.test/index.php/celestinesca/management/settings/website');
    cy.get('#setup-button').click();
    cy.get('html').invoke('attr', 'lang').then((lang) => {
      lang = lang.replace('-','_');
      cy.get('#exportjournalcertificate-button').should('exist')
        .then((element) => {
          element.click();
          cy.get('#exportReviewerCertificateSettings-certificateWatermark-control .pkpButton.pkpButton--isWarnable').should('exist').then((element) => { element.click(); });
          cy.get('#exportReviewerCertificateSettings-certificateWatermark-hiddenFileId').attachFile('certificateFiles/watermark.png');
          cy.get('#exportReviewerCertificateSettings-certificateWatermark-altText').clear().type('Marca');

          cy.get('#exportReviewerCertificateSettings-certificateHeader-control .pkpButton.pkpButton--isWarnable').should('exist').then((element) => { element.click(); });
          cy.get('#exportReviewerCertificateSettings-certificateHeader-hiddenFileId').attachFile('certificateFiles/header.png');
          cy.get('#exportReviewerCertificateSettings-certificateHeader-altText').clear().type('Cabecera');

          cy.get('#exportReviewerCertificateSettings-certificateEditorSignature-control .pkpButton.pkpButton--isWarnable').should('exist').then((element) => { element.click(); });
          cy.get('#exportReviewerCertificateSettings-certificateEditorSignature-hiddenFileId').attachFile('certificateFiles/signature.jpg');
          cy.get('#exportReviewerCertificateSettings-certificateEditorSignature-altText').clear().type('Firma');

          let certificationGretting = 'certificationGretting 1';
          cy.get('div#exportReviewerCertificateSettings-certificateGreeting-control-'+lang).clear().type(certificationGretting, { parseSpecialCharSequences: false });
          
          let certificateContent = "{{reviewer_title}} {{reviewer_fullname}} ({{reviewer_institution}}) ha evaluado el envío «{{publication_title}}» para la revista académica Testing.";
          cy.get('div#exportReviewerCertificateSettings-certificateContent-control-'+lang).clear().type(certificateContent, { parseSpecialCharSequences: false });
          
          let certificationInstitutionDescription = "Esta es una prueba de contenido.";
          cy.get('div#exportReviewerCertificateSettings-certificateInstitutionDescription-control-'+lang).clear().type(certificationInstitutionDescription, { parseSpecialCharSequences: false });
          
          let certificateDate = "Y, para que así conste, se expide este certificado a {{today_day_number}} de {{today_month_name}} de {{today_year_number}}.";
          cy.get('div#exportReviewerCertificateSettings-certificateDate-control-'+lang).clear().type(certificateDate, { parseSpecialCharSequences: false });
          
          let certificateGoodbye = "Y, para que así conste, se expide este certificado a {{today_day_number}} de {{today_month_name}} de {{today_year_number}}.";
          cy.get('div#exportReviewerCertificateSettings-certificateGoodbye-control-'+lang).clear().type(certificateGoodbye, { parseSpecialCharSequences: false });
          
          let certificateEditorName = "Amaranta Saguar García (Secretaria)";
          cy.get('#exportReviewerCertificateSettings-certificateEditorName-control').clear().type(certificateEditorName);
          
          let certificateEditorInstitution = "eScire";
          cy.get('#exportReviewerCertificateSettings-certificateEditorInstitution-control').clear().type(certificateEditorInstitution);
          
          let certificateEditorEmail = "contacto@escire.lat";
          cy.get('#exportReviewerCertificateSettings-certificateEditorEmail-control').clear().type(certificateEditorEmail);
          
          cy.get('#exportjournalcertificate button.pkpButton[label="Guardar"]').click();
        });
    });
  });
});