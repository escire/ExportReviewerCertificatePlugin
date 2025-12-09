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

use APP\core\Application;
use APP\facades\Repo;
use APP\handler\Handler;
use APP\i18n\AppLocale;
use APP\plugins\generic\exportReviewerCertificate\PDFLib;
use APP\plugins\generic\exportReviewerCertificate\repositories\ReviewerCertificateRepository;
use PKP\core\Core;
use PKP\core\JSONMessage;
use PKP\core\PKPApplication;
use PKP\db\DAORegistry;
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
		$this->locale = AppLocale::getLocale();
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
		import('lib.pkp.classes.security.authorization.PolicySet');
		$rolePolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);

		import('lib.pkp.classes.security.authorization.RoleBasedHandlerOperationPolicy');
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
			$request->getSession()->setSessionVar(
				'notification', 
				__('plugins.generic.exportReviewerCertificate.certificate.alreadyDownloaded')
			);
			$request->redirect(null, 'reviewer', 'submission', $params['submission']);
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
		import('plugins.generic.exportReviewerCertificate.src.PDFLib');
		$pdfLib = new PDFLib($this->certificate_dataset);
		$pdfContent = $pdfLib->output();
		
		// Save file and register in activity log
		if ($submission) {
			$submissionFile = $this->saveReviewerCertificatePDF($request, $submission, $currentUser, $pdfContent);
			
			if ($submissionFile) {
				$reviewerName = str_replace(' ', '_', $currentUser->getFullName());
				$fileName = 'certificado_revisor_' . $reviewerName . '_' . date('YmdHis') . '.pdf';
				
				$eventLog = Repo::eventLog()->newDataObject([
					'assocType' => PKPApplication::ASSOC_TYPE_SUBMISSION,
					'assocId' => $submission->getId(),
					'eventType' => SUBMISSION_LOG_REVIEWER_CERTIFICATE_DOWNLOAD,
					'userId' => $currentUser->getId(),
					'message' => 'plugins.generic.exportReviewerCertificate.log.certificateDownloaded',
					'isTranslated' => false,
					'dateLogged' => Core::getCurrentDate(),
					'reviewerName' => $currentUser->getFullName(),
					'username' => $currentUser->getUsername(),
					'filename' => $fileName
				]);
				Repo::eventLog()->add($eventLog);
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
				$journal = json_decode(json_encode($journal->_data, JSON_UNESCAPED_UNICODE));
				$protocol = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://');
				$basePath = $protocol . $_SERVER['HTTP_HOST'] . $this->_request->getBasePath() . "/public/journals/" . $journal->id . "/";

				$this->certificate_dataset['certificate_watermark'] = $basePath . (json_decode($journal->certificateWatermark)->uploadName);
				$this->certificate_dataset['certificate_header'] = $basePath . (json_decode($journal->certificateHeader)->uploadName);

				$this->certificate_dataset["certificate_greeting"] = $journal->certificateGreeting->$locale;
				$this->certificate_dataset["certificate_content"] = $journal->certificateContent->$locale;
				$this->certificate_dataset["institution_description"] = $journal->certificateInstitutionDescription->$locale ?? NULL;
				$this->certificate_dataset["certificate_date"] = $journal->certificateDate->$locale;
				$this->certificate_dataset["certificate_goodbye"] = $journal->certificateGoodbye->$locale;

				$this->certificate_dataset['certificate_editor_sign'] = $basePath . (json_decode($journal->certificateEditorSignature)->uploadName);
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
				$submission = json_decode(json_encode($submission->_data, JSON_UNESCAPED_UNICODE));
				if ($publication = $submission->publications->$submissionId) {
					$locale = $this->locale;
					$publication = $publication->_data;
					$this->certificate_dataset['publication_title'] = $publication->title->$locale;
					$this->certificate_dataset['day_number'] = date('d', strtotime($publication->lastModified));
					$this->certificate_dataset['month_name'] =  $this->monthText($publication->lastModified);
					$this->certificate_dataset['year_number'] = date('Y', strtotime($publication->lastModified));
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
			$reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
			$reviewAssignments = $reviewAssignmentDao->getBySubmissionId($submission->getId());
			
			$reviewAssignment = null;
			foreach ($reviewAssignments as $ra) {
				if ($ra->getReviewerId() == $reviewer->getId() && $ra->getDateCompleted()) {
					$reviewAssignment = $ra;
					break;
				}
			}
			
			if (!$reviewAssignment) {
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
			
			$submissionFile = Repo::submissionFile()->newDataObject([
				'fileId' => $fileId,
				'fileStage' => SubmissionFile::SUBMISSION_FILE_REVIEW_ATTACHMENT,
				'submissionId' => $submission->getId(),
				'uploaderUserId' => $reviewer->getId(),
				'assocType' => PKPApplication::ASSOC_TYPE_REVIEW_ASSIGNMENT,
				'assocId' => $reviewAssignment->getId(),
				'name' => ['en' => $fileName, 'es' => $fileName]
			]);
			
			$submissionFileId = Repo::submissionFile()->add($submissionFile);
			
			return Repo::submissionFile()->get($submissionFileId);
			
		} catch (\Exception $e) {
			return null;
		}
	}
}
