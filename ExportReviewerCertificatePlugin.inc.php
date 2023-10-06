<?php

/**
 * @file plugins/generic/exportReviewerCertificate/ExportReviewerCertificatePlugin.inc.php
 *
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ExportReviewerCertificatePlugin
 * @brief Main class plugin
 * 
 * @author epsomsegura
 * @email segurajaramilloepsom@gmail.com
 * @github https://github.com/epsomsegura
 */

use PKP\components\forms\context\ExportReviewerCertificateForm;

import('lib.pkp.classes.plugins.GenericPlugin');
import('lib.pkp.classes.file.FileManager');
require_once(dirname(__FILE__) . '/vendor/autoload.php');
require_once(dirname(__FILE__) . '/src/PDFLib.php');

/**
 * @class ExportReviewerCertificatePlugin
 * @brief Main class plugin
 */
class ExportReviewerCertificatePlugin extends GenericPlugin
{
  public $context;
  public $contextId;
  public $request;
  public $baseUrl;
  public $temporaryFileApiUrl;

  public function register($category, $path, $mainContextId = NULL)
  {
    $success = parent::register($category, $path);
    if ($success && $this->getEnabled()) {
      HookRegistry::register('Schema::get::context', [$this, 'addToSchema']);
      HookRegistry::register('Template::Settings::website::setup', [$this, 'callbackAppearanceTab']);
      HookRegistry::register('APIHandler::endpoints', [$this, 'callbackSetupEndpoints']);
      HookRegistry::register('LoadHandler', [$this, 'setPageHandler']);
      HookRegistry::register('TemplateResource::getFilename', [$this, '_overridePluginTemplates']);
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
    import('classes.file.PublicFileManager');
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

  public function getName()
  {
    return 'exportreviewercertificateplugin';
  }

  /**
   * Provide a name for this plugin
   *
   * The name will appear in the plugins list where editors can
   * enable and disable plugins.
   */
  public function getDisplayName()
  {
    return __('plugins.generic.exportReviewerCertificate.displayName');
  }

  /**
   * Provide a description for this plugin
   *
   * The description will appear in the plugins list where editors can
   * enable and disable plugins.
   */
  public function getDescription()
  {
    return __('plugins.generic.exportReviewerCertificate.description');
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
