<?php
import('classes.handler.Handler');

class ExportReviewerCertificatePdfHandler extends Handler
{
	private $_request;
	private $locale;
	private $html;
	private $certificate_dataset;

	public function __construct()
	{
		// Allow just reviewer roles to download certificates
		$this->addRoleAssignment([ROLE_ID_REVIEWER],['reviewer','download']);
		// Set global variables
		$this->locale = json_decode(json_encode(Application::get()->getRequest()->getContext()->_data))->primaryLocale;
		$this->html = file_get_contents(dirname(__FILE__) . "/../../src/html/pdftemplate.php");
		$this->certificate_dataset = (object)[
			"reviewer_first_name" => NULL,
			"reviewer_last_name" => NULL,
			"reviewer_affiliation" => NULL,
			"journal_name" => NULL,
			"journal_issn" => NULL,
			"journal_eissn" => NULL,
			"journal_description" => NULL,
			"journal_image_header" => NULL,
			"journal_certificate_journal_info" => NULL,
			"journal_url" => NULL,
			"journal_email" => NULL,
			"journal_editor_name" => NULL,
			"journal_editor_signature" => NULL,
			"journal_editorial_header_image" => NULL,
			"journal_editorial_address" => NULL,
			"journal_editorial_phone" => NULL,
			"journal_institution_name" => NULL,
			"journal_academic_unit_name_1" => NULL,
			"journal_academic_unit_name_2" => NULL,
			"journal_educational_program_name" => NULL,
			"publication_title" => NULL,
			"publication_day" => NULL,
			"publication_month" => NULL,
			"publication_year" => NULL
		];
	}

	/**
	 * Authorize reviewer roles to download evaluation completion certificate
	 */
	public function authorize($request, &$args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.PolicySet');
		$rolePolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);

		import('lib.pkp.classes.security.authorization.RoleBasedHandlerOperationPolicy');
		foreach($roleAssignments as $role => $operations) {
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
		if (!$currentUser) {
			return new JSONMessage("Error", "User is not logged in");
		}
		$this->_request = $request;
		$params = $request->_requestVars;
		$this->reviewer();
		$this->journal();
		$this->submission($params['submission']);

		return (new PDFLib($this->html, $this->certificate_dataset))->stream();
	}

	/**
	 *  Get reviewer data
	 */ 
	private function reviewer()
	{
		if (Application::get()->getRequest()->getUser()) {
			if ($reviewer = Application::get()->getRequest()->getUser()) {
				$locale = $this->locale;
				$reviewer = json_decode(json_encode($reviewer->_data, JSON_UNESCAPED_UNICODE));
				$this->certificate_dataset->reviewer_first_name = $reviewer->givenName->$locale;
				$this->certificate_dataset->reviewer_last_name = $reviewer->familyName->$locale;
				$this->certificate_dataset->reviewer_affiliation = $reviewer->affiliation->$locale;
			}
		}
	}

	/**
	 * Get journal data
	 */
	private function journal()
	{
		if (Application::get()->getRequest()->getUser()) {
			if ($journal = Application::get()->getRequest()->getContext()) {
				$locale = $this->locale;
				$journal = json_decode(json_encode($journal->_data, JSON_UNESCAPED_UNICODE));
				$protocol = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://');
				$urlPath = $protocol . $_SERVER['HTTP_HOST'] . $this->_request->getBasePath() . "/index.php/" . $journal->urlPath;
				$basePath = $protocol . $_SERVER['HTTP_HOST'] . $this->_request->getBasePath() . "/public/journals/" . $journal->id . "/";
				
				$this->certificate_dataset->journal_name = $journal->name->$locale;
				$this->certificate_dataset->journal_issn = $journal->printIssn;
				$this->certificate_dataset->journal_eissn = $journal->onlineIssn;
				$this->certificate_dataset->journal_description = strip_tags($journal->description->$locale);
				$this->certificate_dataset->journal_certificate_journal_info = strip_tags($journal->certificateJournalInfo);

				$this->certificate_dataset->journal_editor_name = $journal->editorName;
				$this->certificate_dataset->journal_editor_signature = $basePath . (json_decode($journal->editorSignature)->uploadName);

				$this->certificate_dataset->journal_editorial_header_image = $basePath . (json_decode($journal->journalCertificateHeader)->uploadName);
				$this->certificate_dataset->journal_editorial_address = $journal->editorialAddress ? $journal->editorialAddress : "";
				$this->certificate_dataset->journal_editorial_phone = $journal->editorialPhone ? $journal->editorialPhone : "";

				$this->certificate_dataset->journal_institution_name = $journal->institutionName ? $journal->institutionName : "";
				$this->certificate_dataset->journal_academic_unit_name_1 = $journal->academicUnitName1 ? $journal->academicUnitName1 : "";
				$this->certificate_dataset->journal_academic_unit_name_2 = $journal->academicUnitName2 ? $journal->academicUnitName2 : "";
				$this->certificate_dataset->journal_educational_program_name = $journal->educationalProgramName ? $journal->educationalProgramName : "";

				$this->certificate_dataset->journal_url = $urlPath;
				$this->certificate_dataset->journal_email = $journal->contactEmail;
			}
		}
	}

	/**
	 * Get submmission data
	 */
	private function submission($submissionId)
	{
		if (Application::get()->getRequest()->getContext()) {
			if ($submission = DAORegistry::getDAO('SubmissionDAO')->getById($submissionId)) {
				$submission = json_decode(json_encode($submission->_data, JSON_UNESCAPED_UNICODE));
				if ($publication = $submission->publications[0]) {
					$locale = $this->locale;
					$publication = json_decode(json_encode($publication->_data));
					$this->certificate_dataset->publication_title = $publication->title->$locale;
					$this->certificate_dataset->publication_day = date('d', strtotime($publication->lastModified));
					$this->certificate_dataset->publication_month =  $this->monthText($publication->lastModified);
					$this->certificate_dataset->publication_year = date('Y', strtotime($publication->lastModified));
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
