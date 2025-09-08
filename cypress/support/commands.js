import 'cypress-file-upload';

Cypress.Commands.add('login', (user, password) => {
    cy.on('uncaught:exception', (err, runnable) => {
        return false;
    });
    cy.visit('/index.php/index/login');
    // cy.get('button[class="cookieAccept"]').click();
    cy.get('input[name="username"]').type(user);
    cy.get('input[name="password"]').type(password);
    cy.get('button[type="submit"]').click();
    cy.visit('/index.php/index/admin/index');
    cy.get('.app__pageHeading').should('contains.text','Administración');
});
