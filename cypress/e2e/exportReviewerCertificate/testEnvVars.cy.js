describe('Environment Variables Test', () => {
    it('Should load environment variables correctly', () => {
        cy.log('Base URL: ' + Cypress.config('baseUrl'));
        cy.log('Admin User: ' + Cypress.env('adminUser'));
        cy.log('Reviewer User: ' + Cypress.env('reviewerUser'));
        cy.log('Test Submission ID: ' + Cypress.env('testSubmissionId'));
        cy.log('Journal Path: ' + Cypress.env('journalPath'));

        expect(Cypress.env('adminUser')).to.exist;
        expect(Cypress.env('adminPassword')).to.exist;
        expect(Cypress.env('testSubmissionId')).to.exist;
        expect(Cypress.env('journalPath')).to.exist;
    });
});
