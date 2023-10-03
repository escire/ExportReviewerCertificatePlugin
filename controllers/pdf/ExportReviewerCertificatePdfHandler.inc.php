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
		$this->addRoleAssignment([ROLE_ID_REVIEWER], ['reviewer', 'download']);
		// Set global variables
		$this->locale = json_decode(json_encode(Application::get()->getRequest()->getContext()->_data))->primaryLocale;
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
		if (!$currentUser) {
			return new JSONMessage("Error", "User is not logged in");
		}
		$this->_request = $request;
		$params = $request->_requestVars;

		$this->certificate_dataset["certificate_watermark"] = "https://upload.wikimedia.org/wikipedia/commons/thumb/a/a0/Logo_de_la_UPTC.svg/1200px-Logo_de_la_UPTC.svg.png";
		$this->certificate_dataset["certificate_greeting"] = "<p>To whom it may concern:</p>";

		$this->certificate_dataset["certificate_content"] = "<p>It is certified that {{reviewer_gender}} {{reviewer_title}} <strong>{{reviewer_fullname}}</strong>{{reviewer_institution}}, evaluated the manuscript titled “<strong>{{publication_title}}</strong>” where a review of compliance with the parameters of the manuscript was carried out, and the information contained in the manuscript and its originality were verified.</p>";
		$this->certificate_dataset["institution_description"] = "<p>The Science in Development Magazine is a magazine edited by the Research and Extension Center of the Faculty of Sciences (CIEC) of the Pedagogical and Technological University of Colombia UPTC, which seeks to contribute to the dissemination of new scientific knowledge and technological developments that are produced in the field of basic sciences (biology, physics, mathematics and chemistry) and is an open space that allows researchers and those interested in the subject direct access to the contents in a free way for the entire public, in such a way that it will allow a constant intersection of its publications worldwide in scientific knowledge, publishing in Spanish, English or Portuguese and is currently indexed in Publindex B (Colciencias-Colombia).</p>";
		$this->certificate_dataset["certificate_date"] = "<p>The issuance of this certificate is made at the request of the interested party on the {{day_number}} day of the month of {{month_name}} of {{year_number}}.</p>";
		$this->certificate_dataset["certificate_goodbye"] = "<p>Cordially, </p>";

		if($params["reviewer_gender"] == "male"){
			$this->certificate_dataset["reviewer_gender"] = __("plugins.generic.exportReviewerCertificate.pdf.reviewer_gender.male");
		}
		if($params["reviewer_gender"] == "female"){
			$this->certificate_dataset["reviewer_gender"] = __("plugins.generic.exportReviewerCertificate.pdf.reviewer_gender.female");
		}
		$this->certificate_dataset["reviewer_title"] = isset($params['reviewer_title']) ? $params['reviewer_title'] : "c.";
		$this->certificate_dataset["reviewer_institution"] = isset($params['submission']) ? $params['reviewer_institution'] : __('plugins.generic.exportReviewerCertificate.pdf.independent_reviewer');
		
		$this->reviewer();
		$this->journal();
		$this->submission($params['submission']);

		return (new PDFLib($this->certificate_dataset))->stream();
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
				$this->certificate_dataset['reviewer_fullname'] = $reviewer->givenName->$locale . ' ' . $reviewer->familyName->$locale;
				// $this->certificate_dataset['reviewer_institution'] = $reviewer->affiliation->$locale;
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

				$this->certificate_dataset['certificate_header'] = $basePath . (json_decode($journal->journalCertificateHeader)->uploadName);

				$this->certificate_dataset['certificate_editor_sign'] = $basePath . (json_decode($journal->editorSignature)->uploadName);
				$this->certificate_dataset['certificate_editor_name'] = $journal->editorName;
				$this->certificate_dataset['certificate_editor_institution'] = $journal->institutionName ? $journal->institutionName : "";
				$this->certificate_dataset['certificate_editor_email'] = $journal->contactEmail;

				// $this->certificate_dataset->journal_url = $urlPath;
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
					$this->certificate_dataset['publication_title'] = $publication->title->$locale;
					$this->certificate_dataset['day_number'] = date('d', strtotime($publication->lastModified));
					$this->certificate_dataset['month_name'] =  $this->monthText($publication->lastModified);
					$this->certificate_dataset['year_number'] = date('Y', strtotime($publication->lastModified));
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
