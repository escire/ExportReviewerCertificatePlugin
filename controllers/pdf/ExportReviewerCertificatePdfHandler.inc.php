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
use PKP\core\JSONMessage;
use PKP\security\authorization\PolicySet;
use PKP\security\Role;
use PKP\security\authorization\RoleBasedHandlerOperationPolicy;

/**
 * @class ExportReviewerCertificatePdfHandler
 * @brief Class implemeting the export reviewer certificate in PDF format handler.
 */
class ExportReviewerCertificatePdfHandler extends Handler
{
	private $_request;
	private $locale;
	private $certificate_dataset;

	public function __construct()
	{
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
			"reviewer_gender" => NULL,
			"reviewer_title" => NULL,
			"reviewer_fullname" => NULL,
			"reviewer_institution" => NULL,
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
			return new JSONMessage("Error", "User is not logged in");
		}
		if (!isset($params['submission'])) {
			return new JSONMessage("Error", "Submission not setted");
		}
		if (!isset($params["reviewer_gender"])) {
			return new JSONMessage("Error", "Reviewer gender not setted");
		}
		// Set certificate dataset from request params data
		if ($params["reviewer_gender"] == "male") {
			$this->certificate_dataset["reviewer_gender"] = __("plugins.generic.exportReviewerCertificate.pdf.reviewer_gender.male");
		}
		if ($params["reviewer_gender"] == "female") {
			$this->certificate_dataset["reviewer_gender"] = __("plugins.generic.exportReviewerCertificate.pdf.reviewer_gender.female");
		}
		$this->certificate_dataset["reviewer_title"] = isset($params['reviewer_title']) ? $params['reviewer_title'] : "c.";
		$this->certificate_dataset["reviewer_institution"] = (isset($params['reviewer_institution']) && $params['reviewer_institution'] != "" ? $params['reviewer_institution'] : __('plugins.generic.exportReviewerCertificate.pdf.independent_reviewer'));
		// Set reviewer data into certificate dataset
		$this->reviewer();
		// Set journal data into certificate dataset
		$this->journal();
		// Set submission data into certificate dataset
		$this->submission($params['submission']);
		// dd($this->certificate_dataset);
		// 
		import('plugins.generic.exportReviewerCertificate.src.PDFLib');
		return (new PDFLib($this->certificate_dataset))->stream();
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
					$this->certificate_dataset['today_day_number'] = date('d');
					$this->certificate_dataset['today_month_number'] = date('m');
					$this->certificate_dataset['today_month_name'] =  $this->monthText(date('Y-m-d'));
					$this->certificate_dataset['today_year_number'] = date('Y');
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
}
