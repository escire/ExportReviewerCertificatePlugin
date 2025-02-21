<?php

/**
 * @file plugins/generic/exportReviewerCertificate/ExportReviewerCertificatePlugin.inc.php
 *
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ExportReviewerCertificatePlugin
 * @brief Main class plugin
 * 
 * @owner: eScire 
 * @co_authors: eScire, Epsom Enrique Segura Jaramillo, Araceli Hernández Morales y Joel Torres Hernández
 * @email: contacto@escire.lat
 * @github: https://github.com/escire-ojs-plugins/exportReviewerCertificate
 */

namespace APP\plugins\generic\exportReviewerCertificate;

use APP\file\PublicFileManager;
use APP\i18n\AppLocale;
use APP\plugins\generic\exportReviewerCertificate\controllers\tab\ExportReviewerCertificateSettingsTabFormHandler;
use PKP\components\forms\context\ExportReviewerCertificateForm;
use PKP\core\Registry;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;

require_once(dirname(__FILE__) . '/vendor/autoload.php');

/**
 * @class ExportReviewerCertificatePlugin
 * @brief Main class plugin
 */
class ExportReviewerCertificatePlugin extends GenericPlugin
{
  /**
   * @see Plugin::getDisplayName()
   */
  public function getDisplayName()
  {
    return __('plugins.generic.exportReviewerCertificate.name');
  }

  /**
   * @see Plugin::getDescription()
   */
  public function getDescription()
  {
    return __('plugins.generic.exportReviewerCertificate.description');
  }

  public function register($category, $path, $mainContextId = null): bool
  {
    $success = parent::register($category, $path);

    if ($success && $this->getEnabled()) {
      Hook::add('Schema::get::context', [$this, 'addToSchema']);
      Hook::add('Template::Settings::website::setup', [$this, 'callbackAppearanceTab']);
      Hook::add('APIHandler::endpoints', [$this, 'callbackSetupEndpoints']);
      Hook::add('LoadHandler', [$this, 'setPageHandler']);
      Hook::add('TemplateResource::getFilename', [$this, '_overridePluginTemplates']);
    }

    return $success;
  }

  function callbackSetupEndpoints($hook, $args)
  {
    $endpoints = &$args[0];
    import('plugins.generic.exportReviewerCertificate.controllers.tab.ExportReviewerCertificateSettingsTabFormHandler');
    $handler = new ExportReviewerCertificateSettingsTabFormHandler();
    $endpoints['PUT'][] =
      [
        'pattern' => '/{contextPath}/api/{version}/contexts/{contextId}/exportReviewerCertificateSettings',
        'handler' => [$handler, 'saveFormData'],
        'roles' => array(ROLE_ID_SITE_ADMIN, ROLE_ID_MANAGER)
      ];
  }

  public function callbackAppearanceTab($hookName, $args)
  {
    $templateMgr = &$args[1];
    $output = &$args[2];
    $request = &Registry::get('request');
    $context = $request->getContext();
    $dispatcher = $request->getDispatcher();
    $supportedFormLocales = $context->getSupportedFormLocales();
    $localeNames = AppLocale::getAllLocales();
    $locales = array_map(function ($localeKey) use ($localeNames) {
      return ['key' => $localeKey, 'label' => $localeNames[$localeKey]];
    }, $supportedFormLocales);
    $contextApiUrl = $dispatcher->url($request, ROUTE_API, $context->getPath(), 'contexts/' . $context->getId() . "/exportReviewerCertificateSettings");
    $publicFileManager = new PublicFileManager();
    $baseUrl = $request->getBaseUrl() . '/' . $publicFileManager->getContextFilesPath($context->getId());
    $temporaryFileApiUrl = $dispatcher->url($request, ROUTE_API, $context->getPath(), 'temporaryFiles');
    $this->import('classes.components.form.context.ExportReviewerCertificateForm');
    $ExportReviewerCertificateForm = new ExportReviewerCertificateForm(
      $contextApiUrl,
      $locales,
      $context,
      $baseUrl,
      $temporaryFileApiUrl
    );
    $templateMgr->setConstants(['FORM_EXPORT_REVIEWER_CERTIFICATE',]);
    $state = $templateMgr->getTemplateVars('state');
    $state['components'][FORM_EXPORT_REVIEWER_CERTIFICATE] = $ExportReviewerCertificateForm->getConfig();
    $templateMgr->assign('state', $state);
    $output .= $templateMgr->fetch($this->getTemplateResource('appearanceTab.tpl'));
    return false;
  }

  /**
   * Enable the settings form in the site-wide plugins list
   *
   * @return boolean
   */
  public function isSitePlugin()
  {
    return false;
  }

  public function setPageHandler($hookName, $params)
  {
    if ($params[0] === "reviewer" && $params[1] === "download") {
      $this->import('controllers.pdf.ExportReviewerCertificatePdfHandler');
      define('HANDLER_CLASS', 'ExportReviewerCertificatePdfHandler');
      return true;
    }
    return false;
  }

  /**
   * Extend the context entity's schema with an institutionalHome property
   */
  public function addToSchema($hookName, $args)
  {
    $schema = $args[0];

    $schema->properties->certificateWatermark = (object) [
      'type' => 'string',
      'apiSummary' => true,
      'multilingual' => false,
      'validation' => ['nullable'],
      "properties" => [
        "temporaryFileId" => [
          "type" => "integer"
        ],
        "name" => [
          "type" => "string"
        ],
        "uploadName" => [
          "type" => "string"
        ],
        "altText" => [
          "type" => "string"
        ]
      ]
    ];

    $schema->properties->certificateHeader = (object) [
      'type' => 'string',
      'apiSummary' => true,
      'multilingual' => false,
      'validation' => ['nullable','dimensions:min_width=1600,min_height=300','mimes:png'],
      "properties" => [
        "temporaryFileId" => [
          "type" => "integer"
        ],
        "name" => [
          "type" => "string"
        ],
        "uploadName" => [
          "type" => "string"
        ],
        "altText" => [
          "type" => "string"
        ]
      ]
    ];

    $schema->properties->certificateGreeting = (object) [
      'type' => 'string',
      'multilingual' => true,
      'apiSummary' => true,
      'validation' => ['nullable']
    ];
    
    $schema->properties->certificateContent = (object) [
      'type' => 'string',
      'multilingual' => true,
      'apiSummary' => true,
      'validation' => ['nullable']
    ];

    $schema->properties->certificateInstitutionDescription = (object) [
      'type' => 'string',
      'multilingual' => true,
      'apiSummary' => true,
      'validation' => ['nullable']
    ];

    $schema->properties->certificateDate = (object) [
      'type' => 'string',
      'multilingual' => true,
      'apiSummary' => true,
      'validation' => ['nullable']
    ];
    
    $schema->properties->certificateGoodbye = (object) [
      'type' => 'string',
      'multilingual' => true,
      'apiSummary' => true,
      'validation' => ['nullable']
    ];

    $schema->properties->certificateEditorSignature = (object) [
      'type' => 'string',
      'apiSummary' => true,
      'multilingual' => false,
      'validation' => ['nullable'],
      "properties" => [
        "temporaryFileId" => [
          "type" => "integer"
        ],
        "name" => [
          "type" => "string"
        ],
        "uploadName" => [
          "type" => "string"
        ],
        "altText" => [
          "type" => "string"
        ]
      ]
    ];

    $schema->properties->certificateEditorName = (object) [
      'type' => 'string',
      'multilingual' => false,
      'apiSummary' => true,
      'validation' => ['nullable']
    ];
    
    $schema->properties->certificateEditorInstitution = (object) [
      'type' => 'string',
      'multilingual' => false,
      'apiSummary' => true,
      'validation' => ['nullable']
    ];

    $schema->properties->certificateEditorEmail = (object) [
      'type' => 'string',
      'multilingual' => false,
      'apiSummary' => true,
      'validation' => ['nullable']
    ];
    
    return false;
  }
}
