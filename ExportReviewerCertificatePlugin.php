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

use APP\core\Application;
use APP\file\PublicFileManager;
use APP\plugins\generic\exportReviewerCertificate\controllers\tab\ExportReviewerCertificateSettingsTabFormHandler;
use APP\plugins\generic\exportReviewerCertificate\repositories\ReviewerCertificateRepository;
use PKP\core\Core;
use PKP\core\Registry;
use PKP\facades\Locale;
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
      // Instalar el endpoint de API si no existe
      $this->installApiEndpoint();
      
      Hook::add('Schema::get::context', [$this, 'addToSchema']);
      Hook::add('Template::Settings::website::setup', [$this, 'callbackAppearanceTab']);
      Hook::add('LoadHandler', [$this, 'setPageHandler']);
      Hook::add('TemplateResource::getFilename', [$this, '_overridePluginTemplates']);
      Hook::add('TemplateManager::fetch', [$this, 'handleTemplateFetch']);
    }

    return $success;
  }

  /**
   * Instalar el endpoint de API en la estructura de OJS
   */
  private function installApiEndpoint(): void
  {
    $apiDir = Core::getBaseDir() . '/api/v1/_exportReviewerCertificateSettings';
    $apiIndexFile = $apiDir . '/index.php';
    
    // Crear directorio si no existe
    if (!file_exists($apiDir)) {
      mkdir($apiDir, 0755, true);
    }
    
    // Copiar el archivo index.php si no existe o está desactualizado
    $sourceFile = $this->getPluginPath() . '/api/v1/index.php';
    if (file_exists($sourceFile) && (!file_exists($apiIndexFile) || filemtime($sourceFile) > filemtime($apiIndexFile))) {
      copy($sourceFile, $apiIndexFile);
    }
  }

  public function callbackAppearanceTab($hookName, $args)
  {
    $templateMgr = &$args[1];
    $output = &$args[2];
    $request = &Registry::get('request');
    $context = $request->getContext();
    $dispatcher = $request->getDispatcher();
    $supportedFormLocales = $context->getSupportedFormLocales();
    $locales = array_map(function ($localeKey) {
      $localeMetadata = Locale::getMetadata($localeKey);
      return ['key' => $localeKey, 'label' => $localeMetadata->getDisplayName()];
    }, $supportedFormLocales);
    $contextApiUrl = $dispatcher->url($request, ROUTE_API, $context->getPath(), '_exportReviewerCertificateSettings');
    $publicFileManager = new PublicFileManager();
    $baseUrl = $request->getBaseUrl() . '/' . $publicFileManager->getContextFilesPath($context->getId());
    $temporaryFileApiUrl = $dispatcher->url($request, ROUTE_API, $context->getPath(), 'temporaryFiles');
    
    // Load the form class
    require_once($this->getPluginPath() . '/classes/components/form/context/ExportReviewerCertificateForm.inc.php');
    
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
    $page = &$params[0];
    $op = &$params[1];
    $handler = &$params[3];
    
    if ($page === "reviewer" && $op === "download") {
      require_once($this->getPluginPath() . '/controllers/pdf/ExportReviewerCertificatePdfHandler.inc.php');
      $handler = new \APP\plugins\generic\exportReviewerCertificate\controllers\pdf\ExportReviewerCertificatePdfHandler();
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

  /**
   * Hook callback: Assign variables to reviewCompleted.tpl template
   * @param string $hookName
   * @param array $args [$templateMgr, $template, $cache_id, $compile_id, &$result]
   * @return bool
   */
  public function handleTemplateFetch($hookName, $args)
  {
    $templateMgr = $args[0];
    $template = $args[1];

    if (strpos($template, 'reviewCompleted.tpl') !== false) {
      $request = Application::get()->getRequest();
      $user = $request->getUser();
      $submission = $templateMgr->getTemplateVars('submission');

      if ($user && $submission) {
        $repository = new ReviewerCertificateRepository();
        $certificate = $repository->getReviewerCertificate(
          $user->getId(),
          $submission->getId()
        );

        $templateMgr->assign([
          'certificateDownloaded' => !is_null($certificate),
          'certificateDownloadDate' => $certificate ? $certificate['created_at'] : null
        ]);
      }
    }

    return false;
  }

  /**
   * @copydoc Plugin::getInstallMigration()
   */
  public function getInstallMigration()
  {
    return new ExportReviewerCertificateMigration();
  }
}
