# Export Reviewer Certificate Plugin for OJS

This is a OJS 3.3 plugin that allows reviewers to download evaluation completion certificate per reviewed article.

## Table of content
- [Prerequisites](#prerequisites)
- [Installation](#installation)
    - [Clonning github repository](#clonning-github-repository)
    - [Using tar.gz file](#using-tar.gz-file)
- [Settings](#settings)
- [Usage](#usage)
- [Notes](#notes)

## Prerequisites

- Open Journal Systems - OJS 3.3 [🌐 Information/Download link](https://pkp.sfu.ca/software/ojs/download/archive/) 
- PHP 7.4 [🌐 Information link](https://www.php.net/releases/7_4_0.php)
- PHP GD extension (php7.4-gd) [🌐 Information link](https://www.php.net/manual/en/book.image.php)
- Linux server is preferred


## Installation

### Clonning github repository
If you have server access, you can clone this repo into <ojs_root_dir>/plugins/generic directory following this steps:

1. Go to **/<ojs_root_dir>/plugins/generic** replacing **<ojs_root_dir>** with your project path
```
cd /<ojs_root_dir>/plugins/generic
```
2. Clone this repo using **ojs33_export_reviewer_certificate_plugin** branch from plugin´s [github repository](https://github.com/epsomsegura/exportReviewerCertificate)
```
git clone --branch ojs33_export_reviewer_certificate_plugin --single-branch https://github.com/epsomsegura/exportReviewerCertificate.git
```
3. That´s all, now you can enable and configure the plugin to each journal

### Using tar.gz file


## Settings


## Usage


## Possible problems

- If plugin not working execute this on root project

```
php tools/upgrade.php upgrade
php lib/pkp/tools/installPluginVersion.php plugins/generic/exportReviewerCertificate/version.xml
```

## Licence

