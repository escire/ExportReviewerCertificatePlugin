# Export Reviewer Certificate Plugin for OJS

This is a OJS 3.3 plugin that allows reviewers to download evaluation completion certificate per reviewed article.

## Table of content
- [Prerequisites](#prerequisites)
- [Installation](#installation)
    - [Cloning from github repository](#cloning-from-github-repository)
    - [Using tar.gz file](#using-tar.gz-file)
- [Settings](#settings)
    - [Manual](#manual)
    - [Languages](#languages)
- [Possible problems](#possible-problems)
- [Licence](#licence)
- [Maintainers](#maintainers)

## Prerequisites

- Open Journal Systems - OJS 3.3 [🌐 Information/Download link](https://pkp.sfu.ca/software/ojs/download/archive/) 
- PHP 7.4 [🌐 Information link](https://www.php.net/releases/7_4_0.php)
- PHP GD extension (php7.4-gd) [🌐 Information link](https://www.php.net/manual/en/book.image.php)
- Linux server is preferred


## Installation

### Cloning from github repository
If you have server access, you can clone this repo into <ojs_root_dir>/plugins/generic directory following this steps:

1. Clone the repository into the **<OJS_ROOT_DIR>/plugins/generic** directory using the following command
    ```
    git clone https://github.com/escire/ExportReviewerCertificatePlugin.git ExportReviewerCertificatePlugin
    ```
2. Verify that the download was completed with the name ExportReviewerCertificatePlugin.
3. Navigate to the plugin directory.
4. Set the plugin branch compatible with the OJS version where it will be deployed:

    For OJS 3.3:
    ````
    git checkout ojs33_export_reviewer_certificate_plugin
    ````

    For OJS 3.4:
    ````
    git checkout ojs34_export_reviewer_certificate_plugin
    ````
5. Log in as an administrator and go to **Settings > Website > Plugins**, then enable the **ExportReviewerCertificatePlugin**.
6. Configure the plugin so it works correctly with the journal you will use.

### Using tar.gz file
1. Download OJS 3.3 plugin version using tar.gz compressed mode [🌐 Download link](https://github.com/escire-ojs-plugins/exportReviewerCertificate/releases/tag/V1.1.5.2)
2. Inflate the file and rename the plugin name removing the version number, folder name must be exportReviewerCertificate
3. Compress the exportReviewerCertificate folder and prepare to upload
4. Login into OJS 3.3 and go to journal website settings.
5. Open Plugin modules tab and import tar.gz plugin
6. That´s all, now you can enable and configure the plugin to each journal

## Settings

### Manual
You can found a basic manual slides clicking this [link](https://docs.google.com/presentation/d/1JYImDqrfUTHMzBFLoflQOABSx70c8nt4nFZBR9lBKzI/edit?usp=sharing). This manual explains the journal certificate document configuration form, the reviewer personal details form and how to set each optional and required parameters including some special keywords used to assign specific data into exported certificate.

### Languages
This plugin version has English, Spanish, French and Portuguese languages but you can add new languages cloning any **country local code named folder** located into plugin directory /<ojs_root_dir>/plugins/generic/exportReviewerCertificate/locale, renaming folder name using **Country local code standard** and editing **locale.po** file content without deleting any code line. If you don't know the country local code you want to add you can search this on [saimana.com](https://saimana.com/list-of-country-locale-code/).


## Cypress tests
First, you should to install some npm packages using this commands into OJS root project directory:

````
- npm install --save-dev cypress
- npm install --save-dev cypress-file-upload
````
Then, configure the **cypress.config.js** file, this is an example:
````
const { defineConfig } = require('cypress');

module.exports = defineConfig({
    e2e: {
        setupNodeEvents(on, config) {
            // Agrega aquí el contenido de plugins/index.js
            return config;
        },
        baseUrl: 'http://localhost/, // You can replace this URL with yours
        specPattern: 'plugins/generic/exportReviewerCertificate/cypress/e2e/**/*.cy.{js,jsx,ts,tsx}', // Specs directory
        supportFile: 'plugins/generic/exportReviewerCertificate/cypress/support/e2e.js', // Support file
    },
});
````
You can update the specPattern and directory properties with your project settings.

Finally, execute this command to run tests in terminal:

````
- npx cypress run
````

If you want to run tests using graphic interface, run this command:
````
- npx cypress open
````
Follow the steps into main form and run.

## Possible problems

- If plugin not working execute this on root project

```
php tools/upgrade.php upgrade
php lib/pkp/tools/installPluginVersion.php plugins/generic/exportReviewerCertificate/version.xml
```

## Licence

- GNU GPL v3

## Authors
- [📧 eScire](mailto:contacto@escire.lat) - [🌐 Website](https://www.escire.lat/)
- [📧 Araceli Hernández Morales](mailto:araceli@escire.lat)
- [📧 Joel Torres Hernández](mailto:joel@escire.lat)
- [📧 Epsom Segura](mailto:epsom@escire.lat)
