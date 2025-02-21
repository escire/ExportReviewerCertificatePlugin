<?php

/**
 * @file plugins/generic/exportReviewerCertificate/controllers/tab/ExportReviewerCertificateSettingsTabFormHandler.inc.php
 *
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ExportReviewerCertificateSettingsTabFormHandler
 * @brief File implemeting the export reviewer certificate settings tab form handler.
 * 
 * @owner: eScire 
 * @co_authors: eScire, Epsom Enrique Segura Jaramillo, Araceli Hernández Morales y Joel Torres Hernández
 * @email: contacto@escire.lat
 * @github: https://github.com/escire-ojs-plugins/exportReviewerCertificate
 */

 namespace APP\plugins\generic\exportReviewerCertificate\controllers\tab;

use APP\core\Application;
use APP\file\PublicFileManager;
use APP\pages\management\SettingsHandler;
use PKP\db\DAORegistry;

import('pages/management/SettingsHandler');
import('lib.pkp.classes.validation.ValidatorFactory');

/**
 * @class ExportReviewerCertificateSettingsTabFormHandler
 * @brief Class implemeting the export reviewer certificate settings tab form handler.
 */
class ExportReviewerCertificateSettingsTabFormHandler extends SettingsHandler
{
	public $args;
	public $context;
	public $contextDao;
	public $request;

	function saveFormData(...$functionArgs)
	{
		$this->request = Application::get()->getRequest();
		$this->contextDao = Application::get()->getContextDAO();
		$this->context = Application::get()->getRequest()->getContext();
		$this->args = $this->request->_requestVars;
		$paramKeys = array_keys($this->args);

		foreach ($paramKeys as $paramKey) {
			if (!(in_array($paramKey, ['certificateWatermark', 'certificateHeader', 'certificateEditorSignature']))) {
				$this->context->setData($paramKey, $this->args[$paramKey]);
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
				$this->context->setData($paramKey, $this->uploadImage($paramKey, $keyName));
			}
		}

		$this->contextDao->updateObject($this->context);
		return false;
	}

	/**
	 * Upload image
	 * 
	 * @param $paramKey string Request parameter key name
	 * @param $keyName string File name prefix for filename
	 * @return string|null Return a uploaded image file properties JSON string or null.
	 */
	private function uploadImage($paramKey, $keyName): ?string
	{
		import('classes.file.PublicFileManager');
		$publicFileManager = new PublicFileManager();
		$fileProperties = ["name" => NULL, "uploadName" => NULL, "altText" => NULL];
		// Check if context has value and params is null

		if ($this->context->getData($paramKey) && !$this->args[$paramKey]) {
		//if (isset($this->args[$paramKey]['temporaryFileId']) && !$this->args[$paramKey]['temporaryFileId'] && $this->context->getData($paramKey)) {
			$fileProperties = json_decode($this->context->getData($paramKey), true);
			$this->deleteExistingFile($fileProperties['uploadName']);
			return "";
		}
		// Check if context has value and params has value and temporary file id is null
		if(isset($this->args[$paramKey]['temporaryFileId'])){
		if ($this->args[$paramKey] && !$this->args[$paramKey]['temporaryFileId'] && $this->context->getData($paramKey)) {
			$fileProperties = json_decode($this->context->getData($paramKey), true);
			$fileProperties['altText'] = $this->args[$paramKey]['altText'];
		}
		}
		// Check if request has temporary file id
		//if ($this->args[$paramKey] && $this->args[$paramKey]['temporaryFileId']) {
		if (isset($this->args[$paramKey]['temporaryFileId']) && $this->args[$paramKey]['temporaryFileId']) {
			// Delete file if exists
			if ($this->context->getData($paramKey)) {
				$fileProperties = json_decode($this->context->getData($paramKey), true);
				$this->deleteExistingFile($fileProperties['uploadName']);
			}
			$temporaryFileId = $this->args[$paramKey]['temporaryFileId'];
			$user = $this->request->getUser();
			$temporaryFile = DAORegistry::getDAO('TemporaryFileDAO')->getTemporaryFile($temporaryFileId, $user->getId());
			// Prepare fileProperties array
			$fileProperties = [
				"name" => $temporaryFile->getData('originalFileName'),
				"uploadName" => $keyName . $this->context->getId() . $publicFileManager->getImageExtension($temporaryFile->getFileType()),
				"altText" => $this->args[$paramKey]['altText']
			];
			$publicFileManager->copyContextFile($this->context->getId(), $temporaryFile->getFilePath(), $fileProperties['uploadName']);
		}

		return "{\"name\":\"" . $fileProperties['name'] . "\",\"uploadName\":\"" . $fileProperties['uploadName'] . "\",\"altText\":\"" . $fileProperties['altText'] . "\"}";
	}



	/**
	 * Delete image
	 * 
	 * @param $fileName string Filename to be deleted
	 */
	private function deleteExistingFile($fileName): void
	{
		import('classes.file.PublicFileManager');
		$publicFileManager = new PublicFileManager();
//		error_log( print_r('*********Base Path**********', TRUE) );
		$basePath = $this->request->getBasePath();
		error_log( print_r($basePath, TRUE) );
//		error_log( print_r('*********Get Path**********', TRUE) );
                //error_log( print_r( __DIR__)[0] . $this->request->getBasePath() . '/public/journals/' . $this->context->getId() . '/' . $fileName, TRUE) );
		if (!empty($this->context->getId()) && !empty($basePath)) {
			$filePath = explode($this->request->getBasePath(), __DIR__)[0] . $this->request->getBasePath() . '/public/journals/' . $this->context->getId() . '/' . $fileName;
			$publicFileManager->deleteByPath($filePath);
		}
		//$publicFileManager->deleteByPath($filePath);
	}
 }
