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

import('classes.handler.Handler');
import('plugins.generic.exportReviewerCertificate.classes.ExportReviewerCertificateDAO');
import('lib.pkp.classes.log.SubmissionLog');
import('lib.pkp.classes.log.SubmissionFileLog');
import('classes.log.SubmissionEventLogEntry');
import('lib.pkp.classes.log.SubmissionFileEventLogEntry');
import('lib.pkp.classes.file.FileManager');
import('lib.pkp.classes.submission.SubmissionFile');

//Constante para el evento de descarga de certificado de revisor
define('SUBMISSION_LOG_REVIEWER_CERTIFICATE_DOWNLOAD', 0x40000020);

/**
 * @class ExportReviewerCertificatePdfHandler
 * @brief Class implemeting the export reviewer certificate in PDF format handler.
 */
class ExportReviewerCertificatePdfHandler extends Handler
{
	private $_request;
	private $locale;
	private $certificate_dataset;
	private $exportReviewerCertificateDAO;
	private $exportReviewerCertificate;
	public function __construct()
	{
		// Allow just reviewer roles to download certificates
		$this->addRoleAssignment([ROLE_ID_REVIEWER], ['reviewer', 'download']);
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
		
		// Inicializar el DAO
		import('plugins.generic.exportReviewerCertificate.classes.ExportReviewerCertificateDAO');
		$this->exportReviewerCertificateDAO = new ExportReviewerCertificateDAO();
		
		// Validations
		if (!$currentUser) {
			return new JSONMessage(false, __('plugins.generic.exportReviewerCertificate.error.notLoggedIn'));
		}
		if (!isset($params['submission'])) {
			return new JSONMessage(false, __('plugins.generic.exportReviewerCertificate.error.submissionNotSet'));
		}
		
		// Verificar si el certificado ya fue descargado anteriormente
		$this->exportReviewerCertificate = $this->exportReviewerCertificateDAO->getByUserAndSubmission(
			$currentUser->_data['id'], 
			$params['submission']
		);
		
		if ($this->exportReviewerCertificate) {
			// El certificado ya fue descargado, mostrar mensaje y redirigir
			$request->getSession()->setSessionVar('notification', __('plugins.generic.exportReviewerCertificate.certificate.alreadyDownloaded'));
			$request->redirect(null, 'reviewer', 'submission', $params['submission']);
			return;
		}
		
		// Si no existe registro, permitir la descarga
		$this->certificate_dataset["reviewer_title"] = isset($params['reviewer_title']) ? $params['reviewer_title'] : "C.";
		
		// Set reviewer data into certificate dataset
		$this->reviewer();
		// Set journal data into certificate dataset
		$this->journal();
		// Set submission data into certificate dataset
		$this->submission($params['submission']);
		
		// Crear el registro ANTES de generar el PDF 
		$this->exportReviewerCertificateDAO->insert($currentUser->_data['id'], $params['submission']);
		
		// Obtener el submission
		$submissionDao = DAORegistry::getDAO('SubmissionDAO');
		$submission = $submissionDao->getById($params['submission']);
		
		// Generar el PDF y obtener el contenido
		$pdfLib = new PDFLib($this->certificate_dataset);
		$pdfContent = $pdfLib->output();
		
		if ($submission) {
			// Guardar el PDF en el sistema de archivos de OJS
			$submissionFile = $this->saveReviewerCertificatePDF($request, $submission, $currentUser, $pdfContent);
			
			if ($submissionFile) {
				// Registrar solo en el log general del submission
				SubmissionLog::logEvent(
					$request, 
					$submission, 
					SUBMISSION_LOG_REVIEWER_CERTIFICATE_DOWNLOAD, 
					'plugins.generic.exportReviewerCertificate.log.certificateDownloaded',
					array(
						'reviewerName' => $currentUser->getFullName(),
						'username' => $currentUser->getUsername(),
						'filename' => $submissionFile->getLocalizedData('name')
					)
				);
			}
		}
		
		// Descargar el PDF al navegador usando el contenido ya generado
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
			if ($submission = DAORegistry::getDAO('SubmissionDAO')->getById($submissionId)) {
				$currentUser = Application::get()->getRequest()->getUser();
				
				// Obtener el reviewAssignment del revisor actual para obtener la fecha de finalización
				$reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
				$reviewAssignments = $reviewAssignmentDao->getBySubmissionId($submissionId);
				
				$reviewCompletedDate = null;
				foreach ($reviewAssignments as $ra) {
					if ($ra->getReviewerId() == $currentUser->getId() && $ra->getDateCompleted()) {
						$reviewCompletedDate = $ra->getDateCompleted();
						break;
					}
				}
				
				$submission = json_decode(json_encode($submission->_data, JSON_UNESCAPED_UNICODE));
				if ($publication = $submission->publications[0]) {
					$locale = $this->locale;
					$publication = json_decode(json_encode($publication->_data));
					$this->certificate_dataset['publication_title'] = $publication->title->$locale;
					
					// Usar la fecha de finalización de la revisión en lugar de lastModified
					$dateToUse = $reviewCompletedDate ? $reviewCompletedDate : date('Y-m-d H:i:s');
					$this->certificate_dataset['day_number'] = date('d', strtotime($dateToUse));
					$this->certificate_dataset['month_name'] =  $this->monthText($dateToUse);
					$this->certificate_dataset['year_number'] = date('Y', strtotime($dateToUse));
					
					$this->certificate_dataset['today_day_number'] =  ($this->exportReviewerCertificate ? date('d', strtotime($this->exportReviewerCertificate->getCreatedAt())) : date('d'));
					$this->certificate_dataset['today_month_number'] = ($this->exportReviewerCertificate ? date('m', strtotime($this->exportReviewerCertificate->getCreatedAt())) : date('m'));
					$this->certificate_dataset['today_month_name'] =  ($this->exportReviewerCertificate ? $this->monthText(date('Y-m-d', strtotime($this->exportReviewerCertificate->getCreatedAt()))) : $this->monthText(date('Y-m-d')));
					$this->certificate_dataset['today_year_number'] = ($this->exportReviewerCertificate ? date('Y', strtotime($this->exportReviewerCertificate->getCreatedAt())) : date('Y'));
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
	 * Guardar el PDF del certificado en el sistema de archivos de OJS
	 * @param $request Request
	 * @param $submission Submission
	 * @param $reviewer User
	 * @param $pdfContent string Contenido binario del PDF
	 * @return SubmissionFile|null
	 */
	private function saveReviewerCertificatePDF($request, $submission, $reviewer, $pdfContent)
	{
		try {
			// Obtener el reviewAssignment más reciente de este revisor para este envio
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
				error_log('No se encontró reviewAssignment completado para el revisor');
				return null;
			}
			
			// Crear nombre de archivo
			$reviewerName = str_replace(' ', '_', $reviewer->getFullName());
			$fileName = 'certificado_revisor_' . $reviewerName . '_' . date('YmdHis') . '.pdf';
			
			// Guardar temporalmente el archivo
			$fileManager = new FileManager();
			$tempFilePath = tempnam(sys_get_temp_dir(), 'cert');
			file_put_contents($tempFilePath, $pdfContent);
			
			// Obtener el directorio del envio
			$context = $request->getContext();
			$submissionDir = Services::get('submissionFile')->getSubmissionDir($context->getId(), $submission->getId());
			
			// Guardar el archivo en el sistema de archivos de OJS
			$newFilePath = $submissionDir . '/' . uniqid() . '.pdf';
			$fileId = Services::get('file')->add($tempFilePath, $newFilePath);
			
			// Limpiar archivo temporal
			unlink($tempFilePath);
			
			// Crear el objeto SubmissionFile
			$submissionFile = DAORegistry::getDAO('SubmissionFileDAO')->newDataObject();
			$submissionFile->setData('fileId', $fileId);
			$submissionFile->setData('fileStage', SUBMISSION_FILE_REVIEW_ATTACHMENT);
			$submissionFile->setData('submissionId', $submission->getId());
			$submissionFile->setData('uploaderUserId', $reviewer->getId());
			$submissionFile->setData('assocType', ASSOC_TYPE_REVIEW_ASSIGNMENT);
			$submissionFile->setData('assocId', $reviewAssignment->getId());
			$submissionFile->setData('createdAt', Core::getCurrentDate());
			$submissionFile->setData('updatedAt', Core::getCurrentDate());
			$submissionFile->setData('name', $fileName, $this->locale);
			
			// Guardar en la base de datos usando el servicio
			$submissionFile = Services::get('submissionFile')->add($submissionFile, $request);
			
			return $submissionFile;
			
		} catch (Exception $e) {
			error_log('Error al guardar certificado de revisor: ' . $e->getMessage());
			return null;
		}
	}
}
