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
			// Campos regulares (texto, etc.)
			if (!(in_array($paramKey, ['certificateWatermark', 'certificateHeader', 'certificateEditorSignature']))) {
				$this->context->setData($paramKey, $this->args[$paramKey]);
			}
			
			// Campos de imagen (requieren procesamiento especial)
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
		
		// Si no viene nada en el request, mantener lo que ya está guardado
		if (!isset($this->args[$paramKey]) || $this->args[$paramKey] === null) {
			if ($this->context->getData($paramKey)) {
				return $this->context->getData($paramKey);
			}
			return "";
		}
		
		// Detectar eliminación explícita (array vacío)
		if (empty($this->args[$paramKey]) && $this->context->getData($paramKey)) {
			if (is_array($this->args[$paramKey]) && count($this->args[$paramKey]) === 0) {
				$fileProperties = json_decode($this->context->getData($paramKey), true);
				$this->deleteExistingFile($fileProperties['uploadName']);
				return "";
			}
			return $this->context->getData($paramKey);
		}
		
		// Nueva imagen subida (tiene temporaryFileId)
		if (isset($this->args[$paramKey]['temporaryFileId']) && $this->args[$paramKey]['temporaryFileId']) {
			// Borrar imagen anterior si existe
			if ($this->context->getData($paramKey)) {
				$fileProperties = json_decode($this->context->getData($paramKey), true);
				$this->deleteExistingFile($fileProperties['uploadName']);
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
			
			return json_encode($fileProperties);
		}
		
		// El formulario reenvió los datos existentes (pasa en ediciones de solo texto)
		if (is_array($this->args[$paramKey]) && isset($this->args[$paramKey]['uploadName'])) {
			return json_encode($this->args[$paramKey]);
		}
		
		// Por defecto, mantener lo que ya está en la base de datos
		if ($this->context->getData($paramKey)) {
			return $this->context->getData($paramKey);
		}
		
		return "";
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
		$basePath = $this->request->getBasePath();
		
		if (!empty($this->context->getId()) && !empty($basePath)) {
			$filePath = explode($this->request->getBasePath(), __DIR__)[0] . $this->request->getBasePath() . '/public/journals/' . $this->context->getId() . '/' . $fileName;
			$publicFileManager->deleteByPath($filePath);
		}
	}
 }
