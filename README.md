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

1. Go to **/<ojs_root_dir>/plugins/generic** replacing **<ojs_root_dir>** with your project path
```
cd /<ojs_root_dir>/plugins/generic
```
2. Clone this repo using **ojs33_export_reviewer_certificate_plugin** branch from plugin´s [github repository](https://github.com/escire/ExportReviewerCertificatePlugin)
```
git clone --branch ojs33_export_reviewer_certificate_plugin --single-branch https://github.com/escire-ojs-plugins/exportReviewerCertificate/tree/ojs33_export_reviewer_certificate_plugin
```
3. That´s all, now you can enable and configure the plugin to each journal

### Using tar.gz file
1. Download OJS 3.3 plugin version using tar.gz compressed mode [🌐 Download link](https://github.com/escire-ojs-plugins/exportReviewerCertificate/releases/tag/V1.1.5.2)
2. Login into OJS 3.3 and go to journal website settings.
3. Open Plugin modules tab and import tar.gz plugin
4. That´s all, now you can enable and configure the plugin to each journal

## Settings

### Manual
You can found a basic manual slides clicking this [link](https://docs.google.com/presentation/d/1JYImDqrfUTHMzBFLoflQOABSx70c8nt4nFZBR9lBKzI/edit?usp=sharing). This manual explains the journal certificate document configuration form, the reviewer personal details form and how to set each optional and required parameters including some special keywords used to assign specific data into exported certificate.

### Languages
This plugin version has English, Spanish, French and Portuguese languages but you can add new languages cloning any **country local code named folder** located into plugin directory /<ojs_root_dir>/plugins/generic/exportReviewerCertificate/locale, renaming folder name using **Country local code standard** and editing **locale.po** file content without deleting any code line. If you don't know the country local code you want to add you can search this on [saimana.com](https://saimana.com/list-of-country-locale-code/).


## Cypress Tests

This plugin includes a comprehensive Cypress end-to-end test suite.

### Installation

Navigate to the plugin directory and install dependencies:

```bash
cd plugins/generic/exportReviewerCertificate
npm install
```

### Configuration

1. Copy the environment file template:
```bash
cp cypress/.env.example cypress/.env
```

2. Edit `cypress/.env` with your actual credentials and settings:
```env
CYPRESS_BASE_URL=https://your-ojs-site.com
CYPRESS_ADMIN_USER=your_admin_username
CYPRESS_ADMIN_PASSWORD=your_admin_password
CYPRESS_REVIEWER_USER=your_reviewer_username
CYPRESS_REVIEWER_PASSWORD=your_reviewer_password
CYPRESS_TEST_SUBMISSION_ID=1
CYPRESS_JOURNAL_PATH=your_journal_path
```

### Running Tests

Run all tests in headless mode:
```bash
npm run cypress:run
```

Run tests with interactive GUI:
```bash
npm run cypress:open
```

Run specific test file:
```bash
npx cypress run --spec "cypress/e2e/exportReviewerCertificate/certificateDownload.cy.js"
```

### Test Suite

The test suite includes:
- **enablePlugin.cy.js** - Plugin enablement verification
- **pluginManagement.cy.js** - Configuration settings management
- **certificateDownload.cy.js** - Certificate download flow and PDF validation
- **testEnvVars.cy.js** - Environment variables verification

### Test Artifacts

Test artifacts are stored in:
- `cypress/downloads/` - Downloaded PDF certificates
- `cypress/screenshots/` - Failure screenshots
- `cypress/videos/` - Test execution recordings



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
