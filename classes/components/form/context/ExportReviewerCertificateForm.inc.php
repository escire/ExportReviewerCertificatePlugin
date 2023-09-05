<?php

/**
 * @file classes/components/form/context/PKPMastheadForm.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PKPMastheadForm
 * @ingroup classes_controllers_form
 *
 * @brief A preset form for configuring a context's masthead details.
 */

namespace PKP\components\forms\context;


use PKP\components\forms\FieldRichTextarea;
use PKP\components\forms\FormComponent;
use PKP\components\forms\FieldText;
use PKP\components\forms\FieldUploadImage;


define('FORM_EXPORT_REVIEWER_CERTIFICATE', 'exportReviewerCertificateSettings');

class ExportReviewerCertificateForm extends FormComponent
{
	/** @copydoc FormComponent::$id */
	public $id = FORM_EXPORT_REVIEWER_CERTIFICATE;

	/** @copydoc FormComponent::$method */
	public $method = 'PUT';

	/**
	 * Constructor
	 *
	 * @param $action string URL to submit the form to
	 * @param $locales array Supported locales
	 * @param $context Context Journal or Press to change settings for
	 * @param $imageUploadUrl string The API endpoint for images uploaded through the rich text field
	 */
	public function __construct($action, $locales, $context, $baseUrl, $temporaryFileApiUrl)
	{
		$this->action = $action;
		$this->locales = $locales;

		// Editor group
		$this
			->addGroup([
				"id" => "editorialsettings",
				"label" => __("plugins.generic.exportReviewerCertificate.editorialSettings.label"),
				"description" => __("plugins.generic.exportReviewerCertificate.editorialSettings.description")
			])
			->addField(new FieldText('editorName', [
				'groupId' => 'editorialsettings',
				'label' => __('plugins.generic.exportReviewerCertificate.settings.editorName'),
				'size' => 'large',
				'isRequired' => true,
				'isMultilingual' => false,
				'value' => $context->getData('editorName')
			]))->addField(new FieldUploadImage('editorSignature', [
				'groupId' => 'editorialsettings',
				'label' => __('plugins.generic.exportReviewerCertificate.settings.editorSignature'),
				"description" => __("plugins.generic.exportReviewerCertificate.settings.editorSignature.description"),
				'value' => json_decode($context->getData('editorSignature')),
				'isMultilingual' => false,
				'isRequired' => true,
				'baseUrl' => $baseUrl,
				'options' => [
					'url' => $temporaryFileApiUrl
				]
			]))
			->addField(new FieldText('editorialAddress', [
				'groupId' => 'editorialsettings',
				'label' => __('plugins.generic.exportReviewerCertificate.settings.editorialAddress'),
				'size' => 'large',
				'isRequired' => true,
				'isMultilingual' => false,
				'value' => $context->getData('editorialAddress')
			]))->addField(new FieldText('editorialPhone', [
				'groupId' => 'editorialsettings',
				'label' => __('plugins.generic.exportReviewerCertificate.settings.editorialPhone'),
				'size' => 'large',
				'isRequired' => false,
				'isMultilingual' => false,
				'value' => $context->getData('editorialPhone')
			]))
			// Certificate settings
			->addGroup([
				"id" => "journalCertificateSettings",
				"label" => __("plugins.generic.exportReviewerCertificate.journalCertificateSettings.label"),
				"description" => __("plugins.generic.exportReviewerCertificate.journalCertificateSettings.description")
			])->addField(new FieldUploadImage('journalCertificateHeader', [
				'groupId' => 'journalCertificateSettings',
				'label' => __('plugins.generic.exportReviewerCertificate.settings.journalCertificateHeader'),
				"description" => __("plugins.generic.exportReviewerCertificate.settings.journalCertificateHeader.description"),
				'value' => json_decode($context->getData('journalCertificateHeader')),
				'isMultilingual' => false,
				'isRequired' => false,
				'baseUrl' => $baseUrl,
				'options' => [
					'url' => $temporaryFileApiUrl
				]
			]))->addField(new FieldRichTextarea('certificateJournalInfo', [
				'groupId' => 'journalCertificateSettings',
				'label' => __('plugins.generic.exportReviewerCertificate.settings.certificateJournalInfo'),
				'size' => 'large',
				'isRequired' => false,
				'isMultilingual' => false,
				'value' => $context->getData('certificateJournalInfo')
			]))
			// Institution settings
			->addGroup([
				"id" => "institutionSettings",
				"label" => __("plugins.generic.exportReviewerCertificate.institutionSettings.label"),
				"description" => __("plugins.generic.exportReviewerCertificate.institutionSettings.description")
			])
			->addField(new FieldText('institutionName', [
				'groupId' => 'institutionSettings',
				'label' => __('plugins.generic.exportReviewerCertificate.settings.institutionName'),
				'size' => 'large',
				'isRequired' => false,
				'isMultilingual' => false,
				'value' => $context->getData('institutionName')
			]))->addField(new FieldText('academicUnitName1', [
				'groupId' => 'institutionSettings',
				'label' => __('plugins.generic.exportReviewerCertificate.settings.academicUnitName1'),
				'size' => 'large',
				'isRequired' => false,
				'isMultilingual' => false,
				'value' => $context->getData('academicUnitName1')
			]))->addField(new FieldText('academicUnitName2', [
				'groupId' => 'institutionSettings',
				'label' => __('plugins.generic.exportReviewerCertificate.settings.academicUnitName2'),
				'size' => 'large',
				'isRequired' => false,
				'isMultilingual' => false,
				'value' => $context->getData('academicUnitName2')
			]))->addField(new FieldText('educationalProgramName', [
				'groupId' => 'institutionSettings',
				'label' => __('plugins.generic.exportReviewerCertificate.settings.educationalProgramName'),
				'size' => 'large',
				'isRequired' => false,
				'isMultilingual' => false,
				'value' => $context->getData('educationalProgramName')
			]));
	}
}
