describe('Export Reviewer Certificate plugin tests', () => {
  it('Configure Export Reviewer Certificate plugin into digital journal', function () {
    let lang;
    cy.login('admin', 'fja-aGHSsj30aosk32');
    cy.visit('https://revistaoccv.test/index.php/occv/management/settings/website');
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
          cy.get('textarea#exportReviewerCertificateSettings-certificateGreeting-control-'+lang).invoke('attr','style','display:block !important;').clear().type(certificationGretting, { parseSpecialCharSequences: false, force: true }).invoke('attr','style','display:none !important;');
          
          let certificateContent = "{{reviewer_title}} {{reviewer_fullname}} ({{reviewer_institution}}) ha evaluado el envío «{{publication_title}}» para la revista académica Testing.";
          cy.get('textarea#exportReviewerCertificateSettings-certificateContent-control-'+lang).invoke('attr','style','display:block !important;').clear().type(certificateContent, { parseSpecialCharSequences: false, force: true }).invoke('attr','style','display:none !important;');
          
          let certificationInstitutionDescription = "Esta es una prueba de contenido.";
          cy.get('textarea#exportReviewerCertificateSettings-certificateInstitutionDescription-control-'+lang).invoke('attr','style','display:block !important;').clear().type(certificationInstitutionDescription, { parseSpecialCharSequences: false, force: true }).invoke('attr','style','display:none !important;');
          
          let certificateDate = "Y, para que así conste, se expide este certificado a {{today_day_number}} de {{today_month_name}} de {{today_year_number}}.";
          cy.get('textarea#exportReviewerCertificateSettings-certificateDate-control-'+lang).invoke('attr','style','display:block !important;').clear().type(certificateDate, { parseSpecialCharSequences: false, force: true }).invoke('attr','style','display:none !important;');
          
          let certificateGoodbye = "Y, para que así conste, se expide este certificado a {{today_day_number}} de {{today_month_name}} de {{today_year_number}}.";
          cy.get('textarea#exportReviewerCertificateSettings-certificateGoodbye-control-'+lang).invoke('attr','style','display:block !important;').clear().type(certificateGoodbye, { parseSpecialCharSequences: false, force: true }).invoke('attr','style','display:none !important;');
          
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