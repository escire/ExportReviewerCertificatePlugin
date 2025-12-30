import 'cypress-file-upload';

/**
 * Custom command to login to OJS
 * @param {string} user - Username
 * @param {string} password - Password
 */
Cypress.Commands.add('login', (user, password) => {
    // Suppress uncaught exceptions to avoid test failures from OJS JS errors
    cy.on('uncaught:exception', (err, runnable) => {
        return false;
    });

    // Visit login page
    cy.visit('/index.php/index/login');

    // Wait for page to load
    cy.get('input[name="username"]', { timeout: 10000 }).should('be.visible');

    // Accept cookies if present (with error handling)
    cy.get('body').then($body => {
        if ($body.find('button[class="cookieAccept"]').length > 0) {
            cy.get('button[class="cookieAccept"]').click();
            cy.wait(500);
        }
    });

    // Fill in credentials and submit
    cy.get('input[name="username"]').clear().type(user, { delay: 50 });
    cy.get('input[name="password"]').clear().type(password, { delay: 50, log: false }); // Don't log password
    cy.get('button[type="submit"]').click();

    // Wait for successful login and verify
    cy.wait(2000);
    cy.url({ timeout: 15000 }).should('not.include', '/login');
});

/**
 * Custom command to navigate to plugin settings
 * @param {string} journalPath - Journal path (e.g., 'rdp')
 */
Cypress.Commands.add('navigateToPluginSettings', (journalPath = 'index') => {
    const url = journalPath === 'index'
        ? '/index.php/index/management/settings/website#plugins'
        : `/index.php/${journalPath}/management/settings/website#plugins`;

    cy.visit(url);
    cy.get('#plugins-button').should('be.visible');
});

/**
 * Custom command to enable a plugin
 * @param {string} pluginId - Plugin ID (e.g., 'exportreviewercertificateplugin')
 */
Cypress.Commands.add('enablePlugin', (pluginId) => {
    cy.get(`#cell-${pluginId}-name`).should('exist');
    cy.get(`[id*="select-cell-${pluginId}-enable"]`).then($checkbox => {
        if (!$checkbox.is(':checked')) {
            cy.log(`Enabling plugin: ${pluginId}`);
            cy.wrap($checkbox).check();
            cy.wait(1000); // Wait for plugin to enable
        }
        cy.log(`Plugin ${pluginId} is enabled`);
    });
});

/**
 * Custom command to upload an image for certificate settings
 * @param {string} fieldId - Field ID (e.g., 'certificateWatermark')
 * @param {string} fileName - File name in fixtures/certificateFiles/
 * @param {string} altText - Alternative text for the image
 */
Cypress.Commands.add('uploadCertificateImage', (fieldId, fileName, altText) => {
    const deleteButtonSelector = `#exportReviewerCertificateSettings-${fieldId}-control .pkpButton.pkpButton--isWarnable`;

    // Check if there's an existing image and delete it first
    cy.get('body').then($body => {
        if ($body.find(deleteButtonSelector).length > 0) {
            cy.get(deleteButtonSelector).click();
            cy.wait(500);
        }
    });

    // Upload new image
    cy.get(`#exportReviewerCertificateSettings-${fieldId}-hiddenFileId`)
        .attachFile(`certificateFiles/${fileName}`);

    // Set alt text
    cy.get(`#exportReviewerCertificateSettings-${fieldId}-altText`)
        .clear()
        .type(altText);

    cy.log(`Uploaded ${fileName} for ${fieldId}`);
});

/**
 * Custom command to fill a localized text field in certificate settings
 * @param {string} fieldId - Field ID (e.g., 'certificateGreeting')
 * @param {string} text - Text to enter
 * @param {string} locale - Locale (will be auto-detected if not provided)
 */
Cypress.Commands.add('fillCertificateField', (fieldId, text, locale = null) => {
    if (locale) {
        cy.get(`div#exportReviewerCertificateSettings-${fieldId}-control-${locale}`)
            .clear()
            .type(text, { parseSpecialCharSequences: false });
    } else {
        // Auto-detect locale from HTML lang attribute
        cy.get('html').invoke('attr', 'lang').then((lang) => {
            const normalizedLang = lang.replace('-', '_');
            cy.get(`div#exportReviewerCertificateSettings-${fieldId}-control-${normalizedLang}`)
                .clear()
                .type(text, { parseSpecialCharSequences: false });
        });
    }
    cy.log(`Filled ${fieldId} with text`);
});

/**
 * Custom command to fill a non-localized text field in certificate settings
 * @param {string} fieldId - Field ID (e.g., 'certificateEditorName')
 * @param {string} text - Text to enter
 */
Cypress.Commands.add('fillCertificateTextField', (fieldId, text) => {
    cy.get(`#exportReviewerCertificateSettings-${fieldId}-control`)
        .clear()
        .type(text);
    cy.log(`Filled ${fieldId} with: ${text}`);
});

/**
 * Custom command to save plugin settings
 */
Cypress.Commands.add('savePluginSettings', () => {
    cy.get('#exportjournalcertificate button.pkpButton[label="Guardar"]').click();
    cy.wait(2000); // Wait for settings to save
    cy.log('Plugin settings saved');
});

/**
 * Custom command to open plugin settings modal
 * @param {string} journalPath - Journal path (e.g., 'rdp')
 */
Cypress.Commands.add('openPluginSettingsModal', (journalPath = 'index') => {
    cy.navigateToPluginSettings(journalPath);

    // Click on setup button
    cy.get('#setup-button').click();

    // Click on Export Journal Certificate button
    cy.get('#exportjournalcertificate-button').should('exist').click();

    cy.log('Plugin settings modal opened');
});
