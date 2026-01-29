<?php

/**
 * @file plugins/generic/exportReviewerCertificate/controllers/pdf/ExportReviewerCertificatePdfHandler.inc.php
 *
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ExportReviewerCertificatePdfHandler
 * @brief File implemeting the export reviewer certificate in PDF format handler.
 *
 * @owner: eScire
 * @co_authors: eScire, Epsom Enrique Segura Jaramillo, Araceli Hernández Morales y Joel Torres Hernández
 * @email: contacto@escire.lat
 * @github: https://github.com/escire-ojs-plugins/exportReviewerCertificate
 */

namespace APP\plugins\generic\exportReviewerCertificate\controllers\pdf;

use APP\core\Application;
use APP\facades\Repo;
use APP\handler\Handler;
use APP\plugins\generic\exportReviewerCertificate\PDFLib;
use APP\plugins\generic\exportReviewerCertificate\repositories\ReviewerCertificateRepository;
use PKP\core\Core;
use PKP\core\JSONMessage;
use PKP\core\PKPApplication;
use PKP\facades\Locale;
use PKP\file\FileManager;
use PKP\log\event\PKPSubmissionEventLogEntry;
use PKP\log\event\SubmissionFileEventLogEntry;
use PKP\security\authorization\PolicySet;
use PKP\security\authorization\RoleBasedHandlerOperationPolicy;
use PKP\security\Role;
use PKP\services\PKPFileService;
use PKP\submissionFile\SubmissionFile;

// Constante para el evento de descarga de certificado de revisor
define('SUBMISSION_LOG_REVIEWER_CERTIFICATE_DOWNLOAD', 0x40000020);

/**
 * @class ExportReviewerCertificatePdfHandler
 * @brief Class implemeting the export reviewer certificate in PDF format handler.
 */
class ExportReviewerCertificatePdfHandler extends Handler
{
	private $_request;
	private $certificate_dataset;
	private $locale;
	private $review_certificate;
	private $reviewCertificateRepository;

	public function __construct()
	{
		$this->reviewCertificateRepository = new ReviewerCertificateRepository();
		$this->addRoleAssignment([Role::ROLE_ID_REVIEWER], ['reviewer', 'download']);
		// Set global variables
		$this->locale = Locale::getLocale();
		$this->certificate_dataset = [
			"certificate_watermark" => NULL,
			"certificate_header" => NULL,
			"certificate_greeting" => NULL,
			"certificate_content" => NULL,
			"institution_description" => NULL,
			"certificate_date" => NULL,
			"certificate_goodbye" => NULL,
			"certificate_editor_sign" => NULL,
			"certificate_editor_name" => NULL,
			"certificate_editor_institution" => NULL,
			"certificate_editor_email" => NULL,
			"reviewer_title" => NULL,
			"reviewer_fullname" => NULL,
			"publication_title" => NULL
		];
	}

	/**
	 * Authorize reviewer roles to download evaluation completion certificate
	 */
	public function authorize($request, &$args, $roleAssignments)
	{
		$rolePolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);

		foreach ($roleAssignments as $role => $operations) {
			$rolePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, $role, $operations));
		}
		$this->addPolicy($rolePolicy);

		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Show/Download PDF evaluation completion certificate
	 */
	public function download($args, $request)
	{
		$currentUser = $request->getUser();
		$this->_request = $request;
		$params = $request->_requestVars;
		
		// Validations
		if (!$currentUser) {
			return new JSONMessage(false, __('plugins.generic.exportReviewerCertificate.error.notLoggedIn'));
		}
		if (!isset($params['submission'])) {
			return new JSONMessage(false, __('plugins.generic.exportReviewerCertificate.error.submissionNotSet'));
		}
		
		// Check if certificate has already been downloaded
		$this->review_certificate = $this->reviewCertificateRepository->getReviewerCertificate(
			$currentUser->_data['id'], 
			$params['submission']
		);
		
		if ($this->review_certificate) {
			$request->getSession()->flash(
				'notification', 
				__('plugins.generic.exportReviewerCertificate.certificate.alreadyDownloaded')
			);
			$request->redirect(null, 'reviewer', 'submission', [$params['submission']]);
			return;
		}
		
		$this->certificate_dataset["reviewer_title"] = isset($params['reviewer_title']) ? $params['reviewer_title'] : "C.";
		
		$this->reviewer();
		$this->journal();
		$this->submission($params['submission']);
		
		// Register certificate download before generating PDF
		$this->reviewCertificateRepository->registerReviewerCertificate($currentUser->_data['id'], $params['submission']);
		
		// Obtener el submission
		$submission = Repo::submission()->get($params['submission']);
		
		// Generar el PDF y obtener el contenido
		require_once(dirname(__FILE__) . '/../../src/PDFLib.php');
		$pdfLib = new PDFLib($this->certificate_dataset);
		$pdfContent = $pdfLib->output();
		
		// Save file and register in activity log
		if ($submission) {
			$submissionFile = $this->saveReviewerCertificatePDF($request, $submission, $currentUser, $pdfContent);
			
			if ($submissionFile) {
				$reviewerName = str_replace(' ', '_', $currentUser->getFullName());
				$fileName = 'certificado_revisor_' . $reviewerName . '_' . date('YmdHis') . '.pdf';
				
				// Crear el event log
				$eventLog = Repo::eventLog()->newDataObject();
				$eventLog->setData('assocType', PKPApplication::ASSOC_TYPE_SUBMISSION);
				$eventLog->setData('assocId', $submission->getId());
				$eventLog->setData('eventType', SUBMISSION_LOG_REVIEWER_CERTIFICATE_DOWNLOAD);
				$eventLog->setData('userId', $currentUser->getId());
				$eventLog->setData('message', 'plugins.generic.exportReviewerCertificate.log.certificateDownloaded');
				$eventLog->setData('isTranslate', false);
				$eventLog->setData('dateLogged', Core::getCurrentDate());
				
				// Agregar parámetros adicionales
				$eventLog->setData('reviewerName', $currentUser->getFullName());
				$eventLog->setData('username', $currentUser->getUsername());
				$eventLog->setData('filename', $fileName);
				
				$eventLogId = Repo::eventLog()->add($eventLog);
				
				//error_log('ExportReviewerCertificate: Event log created with ID ' . $eventLogId);
			} else {
				error_log('ExportReviewerCertificate: Failed to save submission file, no event log created');
			}
		}
		
		// Download PDF to browser
		header('Content-Type: application/pdf');
		header('Content-Disposition: inline; filename="' . $this->certificate_dataset['reviewer_fullname'] . '-certificate.pdf"');
		echo $pdfContent;
		exit;
	}

	/**
	 *  Get reviewer data and set into certificate dataset
	 */
	private function reviewer(): void
	{
		if (Application::get()->getRequest()->getUser()) {
			if ($reviewer = Application::get()->getRequest()->getUser()) {
				$locale = $this->locale;
				$reviewer = json_decode(json_encode($reviewer->_data, JSON_UNESCAPED_UNICODE));
				$this->certificate_dataset['reviewer_fullname'] = $reviewer->givenName->$locale . ' ' . $reviewer->familyName->$locale;
			}
		}
	}

	/**
	 * Get journal data and set into certificate dataset
	 */
	private function journal(): void
	{
		if (Application::get()->getRequest()->getUser()) {
			if ($journal = Application::get()->getRequest()->getContext()) {
				$locale = $this->locale;
				
				$watermarkData = $journal->getData('certificateWatermark');
				$headerData = $journal->getData('certificateHeader');
				$signatureData = $journal->getData('certificateEditorSignature');
				
				$watermark = $watermarkData ? json_decode($watermarkData, true) : null;
				$header = $headerData ? json_decode($headerData, true) : null;
				$signature = $signatureData ? json_decode($signatureData, true) : null;
				
				$journal = json_decode(json_encode($journal->_data, JSON_UNESCAPED_UNICODE));
				$protocol = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://');
				$basePath = $protocol . $_SERVER['HTTP_HOST'] . $this->_request->getBasePath() . "/public/journals/" . $journal->id . "/";

				$this->certificate_dataset['certificate_watermark'] = $basePath . ($watermark['uploadName'] ?? '');
				$this->certificate_dataset['certificate_header'] = $basePath . ($header['uploadName'] ?? '');

				$this->certificate_dataset["certificate_greeting"] = $journal->certificateGreeting->$locale;
				$this->certificate_dataset["certificate_content"] = $journal->certificateContent->$locale;
				$this->certificate_dataset["institution_description"] = $journal->certificateInstitutionDescription->$locale ?? NULL;
				$this->certificate_dataset["certificate_date"] = $journal->certificateDate->$locale;
				$this->certificate_dataset["certificate_goodbye"] = $journal->certificateGoodbye->$locale;

				$this->certificate_dataset['certificate_editor_sign'] = $basePath . ($signature['uploadName'] ?? '');
				$this->certificate_dataset['certificate_editor_name'] = $journal->certificateEditorName;
				$this->certificate_dataset['certificate_editor_institution'] = $journal->certificateEditorInstitution ?? NULL;
				$this->certificate_dataset['certificate_editor_email'] = $journal->certificateEditorEmail ?? NULL;
			}
		}
	}

	/**
	 * Get submmission data and set into certificate dataset
	 */
	private function submission($submissionId)
	{
		if (Application::get()->getRequest()->getContext()) {
			if ($submission = Repo::submission()->get($submissionId)) {
				$currentUser = Application::get()->getRequest()->getUser();
				
				
				$reviewAssignments = Repo::reviewAssignment()->getCollector()
					->filterBySubmissionIds([$submissionId])
					->filterByReviewerIds([$currentUser->getId()])
					->getMany();
				
				$reviewCompletedDate = null;
				foreach ($reviewAssignments as $ra) {
					if ($ra->getDateCompleted()) {
						$reviewCompletedDate = $ra->getDateCompleted();
						break;
					}
				}
				
				$publication = $submission->getCurrentPublication();
				if ($publication) {
					$locale = $this->locale;	
					$title = $publication->getLocalizedData('title', $locale);
					if (!$title) {
						$title = $publication->getLocalizedTitle();
					}	
					$this->certificate_dataset['publication_title'] = $title;
					// Usar la fecha de finalización de la revisión en lugar de lastModified
					$dateToUse = $reviewCompletedDate ? $reviewCompletedDate : date('Y-m-d H:i:s');
					$this->certificate_dataset['day_number'] = date('d', strtotime($dateToUse));
					$this->certificate_dataset['month_name'] =  $this->monthText($dateToUse);
					$this->certificate_dataset['year_number'] = date('Y', strtotime($dateToUse));
					$this->certificate_dataset['today_day_number'] =  ($this->review_certificate ? date('d', strtotime($this->review_certificate['created_at'])) : date('d'));
					$this->certificate_dataset['today_month_number'] = ($this->review_certificate ? date('m', strtotime($this->review_certificate['created_at'])) : date('m'));
					$this->certificate_dataset['today_month_name'] =  ($this->review_certificate ? $this->monthText(date('Y-m-d', strtotime($this->review_certificate['created_at']))) : $this->monthText(date('Y-m-d')));
					$this->certificate_dataset['today_year_number'] = ($this->review_certificate ? date('Y', strtotime($this->review_certificate['created_at'])) : date('Y'));
				}
			}
		}
	}

	/**
	 * Get month translated text
	 */
	private function monthText($date)
	{
		$month = strtolower(date('F', strtotime($date)));
		return __('plugins.generic.exportReviewerCertificate.pdf.month.' . $month);
	}

	/**
	 * Save reviewer certificate PDF to OJS file system
	 * @param $request Request
	 * @param $submission Submission
	 * @param $reviewer User
	 * @param $pdfContent string Binary PDF content
	 * @return SubmissionFile|null
	 */
	private function saveReviewerCertificatePDF($request, $submission, $reviewer, $pdfContent)
	{
		try {
			// Usar el repositorio en lugar del DAO
			$reviewAssignments = Repo::reviewAssignment()
				->getCollector()
				->filterBySubmissionIds([$submission->getId()])
				->filterByReviewerIds([$reviewer->getId()])
				->filterByCompleted(true)
				->getMany();
			
			$reviewAssignment = null;
			foreach ($reviewAssignments as $ra) {
				if ($ra->getData('dateCompleted')) {
					$reviewAssignment = $ra;
					break;
				}
			}
			
			if (!$reviewAssignment) {
				error_log('ExportReviewerCertificate: No completed review assignment found for reviewer ' . $reviewer->getId());
				return null;
			}
			
			$reviewerName = str_replace(' ', '_', $reviewer->getFullName());
			$fileName = 'certificado_revisor_' . $reviewerName . '_' . date('YmdHis') . '.pdf';
			
			$tempFilePath = tempnam(sys_get_temp_dir(), 'cert');
			file_put_contents($tempFilePath, $pdfContent);
			
			$context = $request->getContext();
			$submissionDir = 'contexts/' . $context->getId() . '/submissions/' . $submission->getId() . '/';
			$relativePath = $submissionDir . uniqid() . '.pdf';
			
			$fileService = app(PKPFileService::class);
			$fileId = $fileService->add($tempFilePath, $relativePath);
			
			unlink($tempFilePath);
			
			// Crear el objeto SubmissionFile
			$submissionFile = Repo::submissionFile()->newDataObject();
			$submissionFile->setData('fileId', $fileId);
			$submissionFile->setData('fileStage', SubmissionFile::SUBMISSION_FILE_REVIEW_ATTACHMENT);
			$submissionFile->setData('submissionId', $submission->getId());
			$submissionFile->setData('uploaderUserId', $reviewer->getId());
			$submissionFile->setData('assocType', PKPApplication::ASSOC_TYPE_REVIEW_ASSIGNMENT);
			$submissionFile->setData('assocId', $reviewAssignment->getId());
			$submissionFile->setData('name', $fileName, Locale::getLocale());
			$submissionFile->setData('createdAt', Core::getCurrentDate());
			$submissionFile->setData('updatedAt', Core::getCurrentDate());
			
			$submissionFileId = Repo::submissionFile()->add($submissionFile);
			
			error_log('ExportReviewerCertificate: File saved successfully with ID ' . $submissionFileId);
			
			return Repo::submissionFile()->get($submissionFileId);
			
		} catch (\Exception $e) {
			error_log('ExportReviewerCertificate: Error saving PDF - ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
			return null;
		}
	}
}
