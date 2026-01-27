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
use PKP\core\Core;
use PKP\db\DAORegistry;

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
	 * @return string Return uploaded image file properties as JSON string.
	 */
	private function uploadImage($paramKey, $keyName): string
	{
		$publicFileManager = new PublicFileManager();
		
		// Obtener datos actuales de la base de datos
		$currentDataString = $this->context->getData($paramKey);
		$currentData = null;
		if ($currentDataString && is_string($currentDataString)) {
			$currentData = json_decode($currentDataString, true);
		}
		
		// Si no viene nada en el request, mantener lo que ya está guardado
		if (!isset($this->args[$paramKey]) || $this->args[$paramKey] === null) {
			return $currentDataString ?: '';
		}
		
		// Detectar eliminación explícita (array vacío sin temporaryFileId)
		if (is_array($this->args[$paramKey]) && empty($this->args[$paramKey]['temporaryFileId'])) {
			// Si es un array vacío, significa eliminación
			if (count($this->args[$paramKey]) === 0) {
				if ($currentData && isset($currentData['uploadName'])) {
					$this->deleteExistingFile($currentData['uploadName']);
				}
				return '';
			}
			// Si tiene uploadName, es un reenvío de datos existentes
			if (isset($this->args[$paramKey]['uploadName'])) {
				return json_encode($this->args[$paramKey], JSON_UNESCAPED_UNICODE);
			}
		}
		
		// Nueva imagen subida
		if (isset($this->args[$paramKey]['temporaryFileId']) && $this->args[$paramKey]['temporaryFileId']) {
			// Eliminar archivo existente si hay
			if ($currentData && isset($currentData['uploadName'])) {
				$this->deleteExistingFile($currentData['uploadName']);
			}
			
			$temporaryFileId = $this->args[$paramKey]['temporaryFileId'];
			$user = $this->request->getUser();
			$temporaryFile = DAORegistry::getDAO('TemporaryFileDAO')->getTemporaryFile($temporaryFileId, $user->getId());
			$fileName = $keyName . $this->context->getId() . $publicFileManager->getImageExtension($temporaryFile->getFileType());
			$publicFileManager->copyContextFile($this->context->getId(), $temporaryFile->getFilePath(), $fileName);
			
			// Obtener dimensiones de la imagen
			$filePath = $publicFileManager->getContextFilesPath($this->context->getId()) . '/' . $fileName;
			list($width, $height) = getimagesize($filePath);
			
			$fileProperties = [
				"name" => $temporaryFile->getData('originalFileName'),
				"uploadName" => $fileName,
				"width" => $width,
				"height" => $height,
				"dateUploaded" => Core::getCurrentDate(),
				"altText" => !empty($this->args[$paramKey]['altText']) ? $this->args[$paramKey]['altText'] : ''
			];
			
			return json_encode($fileProperties, JSON_UNESCAPED_UNICODE);
		}
		
		// Por defecto, mantener lo que ya está en la base de datos
		return $currentDataString ?: '';
	}



	/**
	 * Delete image
	 * 
	 * @param $fileName string Filename to be deleted
	 */
	private function deleteExistingFile($fileName): void
	{
		$publicFileManager = new PublicFileManager();
		$basePath = $this->request->getBasePath();
		
		if (!empty($this->context->getId()) && !empty($basePath)) {
			$filePath = explode($this->request->getBasePath(), __DIR__)[0] . $this->request->getBasePath() . '/public/journals/' . $this->context->getId() . '/' . $fileName;
			$publicFileManager->deleteByPath($filePath);
		}
	}
 }
