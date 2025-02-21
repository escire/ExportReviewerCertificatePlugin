# Export Reviewer Certificate Plugin for OJS

This is a OJS 3.4 plugin that allows reviewers to download evaluation completion certificate per reviewed article.

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

## Prerequisites

- Open Journal Systems - OJS 3.4 [🌐 Information/Download link](https://pkp.sfu.ca/software/ojs/download/archive/) 
- PHP 7.4|8+ 
- PHP GD extension 
- Linux server is preferred


## Installation

### Cloning from github repository
If you have server access, you can clone this repo into <ojs_root_dir>/plugins/generic directory following this steps:

1. Go to **/<ojs_root_dir>/plugins/generic** replacing **<ojs_root_dir>** with your project path
```
cd /<ojs_root_dir>/plugins/generic
```
2. Clone this repo using **ojs34_export_reviewer_certificate_plugin** branch from plugin´s [github repository](https://github.com/epsomsegura/exportReviewerCertificate)
```
git clone --branch ojs34_export_reviewer_certificate_plugin --single-branch https://github.com/epsomsegura/exportReviewerCertificate.git
```
3. That´s all, now you can enable and configure the plugin to each journal

### Using tar.gz file
1. Download OJS 3.3 plugin version using tar.gz compressed mode [🌐 Download link](https://github.com/epsomsegura/exportReviewerCertificate/archive/refs/tags/V1.1.0.tar.gz)
2. Login into OJS 3.3 and go to journal website settings.
3. Open Plugin modules tab and import tar.gz plugin
4. That´s all, now you can enable and configure the plugin to each journal

## Settings

### Manual
You can found a basic manual slides clicking this [link](https://docs.google.com/presentation/d/1JYImDqrfUTHMzBFLoflQOABSx70c8nt4nFZBR9lBKzI/edit?usp=sharing). This manual explains the journal certificate document configuration form, the reviewer personal details form and how to set each optional and required parameters including some special keywords used to assign specific data into exported certificate.

### Languages
This plugin version has English, Spanish, French and Portuguese languages but you can add new languages cloning any **country local code named folder** located into plugin directory /<ojs_root_dir>/plugins/generic/exportReviewerCertificate/locale, renaming folder name using **Country local code standard** and editing **locale.po** file content without deleting any code line. If you don't know the country local code you want to add you can search this on [saimana.com](https://saimana.com/list-of-country-locale-code/).


## Possible problems

- If plugin not working execute this on root project

```
php tools/upgrade.php upgrade
php lib/pkp/tools/installPluginVersion.php plugins/generic/exportReviewerCertificate/version.xml
```

## Licence

GNU GPL v3
