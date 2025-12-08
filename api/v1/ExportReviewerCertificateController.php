<?php

/**
 * @file plugins/generic/exportReviewerCertificate/api/v1/ExportReviewerCertificateController.php
 *
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ExportReviewerCertificateController
 * @brief API Controller for Export Reviewer Certificate Settings
 * 
 * Copyright (c) 2025 Gustavo Casiano - eScire
 * gustavo@escire.lat  
 * @github: https://github.com/escire-ojs-plugins/exportReviewerCertificate
 */

namespace APP\plugins\generic\exportReviewerCertificate\api\v1;

use APP\core\Application;
use APP\file\PublicFileManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use PKP\core\PKPBaseController;
use PKP\db\DAORegistry;
use PKP\security\Role;

class ExportReviewerCertificateController extends PKPBaseController
{
    /**
     * @copydoc \PKP\core\PKPBaseController::getHandlerPath()
     */
    public function getHandlerPath(): string
    {
        return '_exportReviewerCertificateSettings';
    }

    /**
     * @copydoc \PKP\core\PKPBaseController::getRouteGroupMiddleware()
     */
    public function getRouteGroupMiddleware(): array
    {
        return [
            'has.user',
            'has.context',
        ];
    }

    /**
     * @copydoc \PKP\core\PKPBaseController::getGroupRoutes()
     */
    public function getGroupRoutes(): void
    {
        Route::middleware([
            self::roleAuthorizer([
                Role::ROLE_ID_SITE_ADMIN,
                Role::ROLE_ID_MANAGER,
            ]),
        ])->group(function () {
            Route::put('', $this->edit(...))
                ->name('exportReviewerCertificate.edit');
        });
    }

    /**
     * Save export reviewer certificate settings
     */
    public function edit(Request $illuminateRequest): JsonResponse
    {
        $request = Application::get()->getRequest();
        $context = $request->getContext();
        $contextDao = Application::get()->getContextDAO();
        
        $params = $illuminateRequest->all();
        $paramKeys = array_keys($params);

        foreach ($paramKeys as $paramKey) {
            if (!(in_array($paramKey, ['certificateWatermark', 'certificateHeader', 'certificateEditorSignature']))) {
                $context->setData($paramKey, $params[$paramKey]);
            }
            if (in_array($paramKey, ['certificateWatermark', 'certificateHeader', 'certificateEditorSignature'])) {
                if ($paramKey == "certificateWatermark") {
                    $keyName = "certificate_watermark_";
                }
                if ($paramKey == "certificateHeader") {
                    $keyName = "certificate_header_";
                }
                if ($paramKey == "certificateEditorSignature") {
                    $keyName = "certificate_editor_signature_";
                }
                $context->setData($paramKey, $this->uploadImage($paramKey, $keyName, $params, $context, $request));
            }
        }

        $contextDao->updateObject($context);
        
        return response()->json([
            'success' => true,
            'message' => __('plugins.generic.exportReviewerCertificate.settings.saved')
        ], Response::HTTP_OK);
    }

    /**
     * Upload image
     * 
     * @param $paramKey string Request parameter key name
     * @param $keyName string File name prefix for filename
     * @param $params array Request parameters
     * @param $context Context
     * @param $request PKPRequest
     * @return string|null Return a uploaded image file properties JSON string or null.
     */
    private function uploadImage($paramKey, $keyName, $params, $context, $request): ?string
    {
        $publicFileManager = new PublicFileManager();
        $fileProperties = ["name" => NULL, "uploadName" => NULL, "altText" => NULL];
        
        // Check if context has value and params is null
        if ($context->getData($paramKey) && !$params[$paramKey]) {
            $fileProperties = json_decode($context->getData($paramKey), true);
            $this->deleteExistingFile($fileProperties['uploadName'], $context, $request);
            return "";
        }
        
        // Check if context has value and params has value and temporary file id is null
        if(isset($params[$paramKey]['temporaryFileId'])){
            if ($params[$paramKey] && !$params[$paramKey]['temporaryFileId'] && $context->getData($paramKey)) {
                $fileProperties = json_decode($context->getData($paramKey), true);
                $fileProperties['altText'] = $params[$paramKey]['altText'];
            }
        }
        
        // Check if request has temporary file id
        if (isset($params[$paramKey]['temporaryFileId']) && $params[$paramKey]['temporaryFileId']) {
            // Delete file if exists
            if ($context->getData($paramKey)) {
                $fileProperties = json_decode($context->getData($paramKey), true);
                $this->deleteExistingFile($fileProperties['uploadName'], $context, $request);
            }
            $temporaryFileId = $params[$paramKey]['temporaryFileId'];
            $user = $request->getUser();
            $temporaryFile = DAORegistry::getDAO('TemporaryFileDAO')->getTemporaryFile($temporaryFileId, $user->getId());
            
            // Prepare fileProperties array
            $fileProperties = [
                "name" => $temporaryFile->getData('originalFileName'),
                "uploadName" => $keyName . $context->getId() . $publicFileManager->getImageExtension($temporaryFile->getFileType()),
                "altText" => $params[$paramKey]['altText']
            ];
            $publicFileManager->copyContextFile($context->getId(), $temporaryFile->getFilePath(), $fileProperties['uploadName']);
        }

        return json_encode($fileProperties);
    }

    /**
     * Delete image
     * 
     * @param $fileName string Filename to be deleted
     * @param $context Context
     * @param $request PKPRequest
     */
    private function deleteExistingFile($fileName, $context, $request): void
    {
        $publicFileManager = new PublicFileManager();
        $basePath = $request->getBasePath();
        
        if (!empty($context->getId()) && !empty($basePath)) {
            $filePath = explode($request->getBasePath(), __DIR__)[0] . $request->getBasePath() . '/public/journals/' . $context->getId() . '/' . $fileName;
            $publicFileManager->deleteByPath($filePath);
        }
    }
}


