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

use Application;
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
		$locale = json_decode(json_encode(Application::get()->getRequest()->getContext()->_data))->primaryLocale;
		dd($locale);

		// Certificate design settings
		$this
			->addGroup([
				"id" => "designDocumentSettings",
				"label" => __("plugins.generic.exportReviewerCertificate.designDocumentSettings.label"),
				"description" => __("plugins.generic.exportReviewerCertificate.designDocumentSettings.label")
			])
			->addField(new FieldUploadImage('certificateWatermark', [
				'groupId' => 'designDocumentSettings',
				'label' => __("plugins.generic.exportReviewerCertificate.designDocumentSettings.watermark.label"),
				"description" => __("plugins.generic.exportReviewerCertificate.designDocumentSettings.watermark.description"),
				'value' => json_decode($context->getData('certificateWatermark')),
				'isMultilingual' => false,
				'isRequired' => false,
				'baseUrl' => $baseUrl,
				'options' => [
					'url' => $temporaryFileApiUrl
				]
			]))
			->addField(new FieldUploadImage('certificateHeader', [
				'groupId' => 'designDocumentSettings',
				'label' => __('plugins.generic.exportReviewerCertificate.designDocumentSettings.header.label'),
				"description" => __("plugins.generic.exportReviewerCertificate.designDocumentSettings.header.description"),
				'value' => json_decode($context->getData('certificateHeader')),
				'isMultilingual' => false,
				'isRequired' => false,
				'baseUrl' => $baseUrl,
				'options' => [
					'url' => $temporaryFileApiUrl
				]
			]));
		// Certificate content settings
		$this
			->addGroup([
				"id" => "contentDocumentSettings",
				"label" => __("plugins.generic.exportReviewerCertificate.contentDocumentSettings.label"),
				"description" => __("plugins.generic.exportReviewerCertificate.contentDocumentSettings.description")
			])
			->addField(new FieldRichTextarea('certificateGretting', [
				'groupId' => 'contentDocumentSettings',
				'label' => __("plugins.generic.exportReviewerCertificate.contentDocumentSettings.certificateGretting.label"),
				"description" => __("plugins.generic.exportReviewerCertificate.contentDocumentSettings.certificateGretting.description"),
				'size' => 'short',
				'isRequired' => true,
				'isMultilingual' => false,
				'value' => $context->getData('certificateGretting')
			]))
			->addField(new FieldRichTextarea('certificateContent', [
				'groupId' => 'contentDocumentSettings',
				'label' => __("plugins.generic.exportReviewerCertificate.contentDocumentSettings.certificateContent.label"),
				"description" => __("plugins.generic.exportReviewerCertificate.contentDocumentSettings.certificateContent.description"),
				"tooltip" => "Lorem ipsum dolor {{reviewer_gender}} {{reviewer_title}} amet {{reviewer_fullname}}{{reviewer_institution}}, consectetur adipiscing elit. Pellentesque ut magna quis {{publication_title}} faucibus pulvinar. In hac habitasse platea dictumst. Mauris commodo placerat urna, a iaculis sapien laoreet eget. Nunc bibendum quis sem a ullamcorper. In nec interdum justo. Sed fringilla volutpat ante vel malesuada. Curabitur erat ipsum, condimentum vel.",
				'size' => 'small',
				'isRequired' => true,
				'isMultilingual' => false,
				'value' => $context->getData('certificateContent')
			]))
			->addField(new FieldRichTextarea('certificateInstitutionDescription', [
				'groupId' => 'contentDocumentSettings',
				'label' => __("plugins.generic.exportReviewerCertificate.contentDocumentSettings.certificateInstitutionDescription.label"),
				'description' => __("plugins.generic.exportReviewerCertificate.contentDocumentSettings.certificateInstitutionDescription.description"),
				'size' => 'large',
				'isRequired' => false,
				'isMultilingual' => false,
				'value' => $context->getData('certificateInstitutionDescription')
			]))
			->addField(new FieldRichTextarea('certificateDate', [
				'groupId' => 'contentDocumentSettings',
				'label' => __("plugins.generic.exportReviewerCertificate.contentDocumentSettings.certificateDate.label"),
				'description' => __("plugins.generic.exportReviewerCertificate.contentDocumentSettings.certificateDate.description"),
				"tooltip" => "Lorem ipsum dolor {{day_number}} amet {{month_name}} yet {{year_number}}.",
				'size' => 'short',
				'isRequired' => true,
				'isMultilingual' => false,
				'value' => $context->getData('certificateDate')
			]))
			->addField(new FieldRichTextarea('certificateGoodbye', [
				'groupId' => 'contentDocumentSettings',
				'label' => __("plugins.generic.exportReviewerCertificate.contentDocumentSettings.certificateGoodbye.label"),
				'description' => __("plugins.generic.exportReviewerCertificate.contentDocumentSettings.certificateGoodbye.description"),
				'size' => 'short',
				'isRequired' => true,
				'isMultilingual' => false,
				'value' => $context->getData('certificateGoodbye')
			]));

		// Certificate signature settings
		$this
			->addGroup([
				"id" => "certificateSignatureSettings",
				"label" => __("plugins.generic.exportReviewerCertificate.certificateSignatureSettings.label"),
				"description" => __("plugins.generic.exportReviewerCertificate.certificateSignatureSettings.description")
			])
			->addField(new FieldUploadImage('certificateEditorSign', [
				'groupId' => 'certificateSignatureSettings',
				'label' => __('plugins.generic.exportReviewerCertificate.certificateSignatureSettings.certificateEditorSign.label'),
				"description" => __("plugins.generic.exportReviewerCertificate.certificateSignatureSettings.certificateEditorSign.description"),
				'value' => json_decode($context->getData('certificateEditorSign')),
				'isMultilingual' => false,
				'isRequired' => true,
				'baseUrl' => $baseUrl,
				'options' => [
					'url' => $temporaryFileApiUrl
				]
			]))
			->addField(new FieldText('certificateEditorName', [
				'groupId' => 'certificateSignatureSettings',
				'label' => __('plugins.generic.exportReviewerCertificate.certificateSignatureSettings.certificateEditorName.label'),
				'size' => 'large',
				'isRequired' => true,
				'isMultilingual' => false,
				'value' => $context->getData('certificateEditorName')
			]))
			->addField(new FieldText('certificateEditorInstitution', [
				'groupId' => 'certificateSignatureSettings',
				'label' => __('plugins.generic.exportReviewerCertificate.certificateSignatureSettings.certificateEditorInstitution.label'),
				'size' => 'large',
				'isRequired' => false,
				'isMultilingual' => false,
				'value' => $context->getData('certificateEditorInstitution')
			]))
			->addField(new FieldText('certificateEditorEmail', [
				'groupId' => 'certificateSignatureSettings',
				'label' => __('plugins.generic.exportReviewerCertificate.certificateSignatureSettings.certificateEditorEmail.label'),
				'size' => 'large',
				'isRequired' => false,
				'isMultilingual' => false,
				'value' => $context->getData('certificateEditorEmail')
			]));
	}
}
