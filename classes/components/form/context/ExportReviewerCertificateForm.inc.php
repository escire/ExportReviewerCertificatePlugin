<?php

/**
 * @file plugins/generic/exportReviewerCertificate/classes/components/form/context/ExportReviewerCertificateForm.inc.php
 *
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ExportReviewerCertificateForm
 * @brief A preset form for configuring a context's export reviewer certificate.
 * 
 * @owner: eScire 
 * @co_authors: eScire, Epsom Enrique Segura Jaramillo, Araceli Hernández Morales y Joel Torres Hernández
 * @email: contacto@escire.lat
 * @github: https://github.com/escire-ojs-plugins/exportReviewerCertificate
 */

namespace PKP\components\forms\context;

use PKP\components\forms\FieldRichTextarea;
use PKP\components\forms\FormComponent;
use PKP\components\forms\FieldText;
use PKP\components\forms\FieldUploadImage;


define('FORM_EXPORT_REVIEWER_CERTIFICATE', 'exportReviewerCertificateSettings');

/**
 * @class ExportReviewerCertificateForm
 * @brief Class implemeting a preset form for configuring a context's export reviewer certificate.
 */
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
	 * @param $baseUrl string The server endpoint for images uploaded through the image file field
	 * @param $temporaryFileApiUrl string The API endpoint for images uploaded through the rich text field
	 */
	public function __construct($action, $locales, $context, $baseUrl, $temporaryFileApiUrl)
	{
		$this->action = $action;
		$this->locales = $locales;
		// Certificate design settings group
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
		// Certificate content settings group
		$this
			->addGroup([
				"id" => "contentDocumentSettings",
				"label" => __("plugins.generic.exportReviewerCertificate.contentDocumentSettings.label"),
				"description" => __("plugins.generic.exportReviewerCertificate.contentDocumentSettings.description")
			])
			->addField(new FieldRichTextarea('certificateGreeting', [
				'groupId' => 'contentDocumentSettings',
				'label' => __("plugins.generic.exportReviewerCertificate.contentDocumentSettings.certificateGreeting.label"),
				"description" => __("plugins.generic.exportReviewerCertificate.contentDocumentSettings.certificateGreeting.description"),
				'size' => 'short',
				'isMultilingual' => true,
				'value' => $context->getData('certificateGreeting')
			]))
			->addField(new FieldRichTextarea('certificateContent', [
				'groupId' => 'contentDocumentSettings',
				'label' => __("plugins.generic.exportReviewerCertificate.contentDocumentSettings.certificateContent.label"),
				"description" => __("plugins.generic.exportReviewerCertificate.contentDocumentSettings.certificateContent.description"),
				"tooltip" => "Lorem ipsum dolor {{reviewer_gender}} {{reviewer_title}} amet {{reviewer_fullname}}{{reviewer_institution}}, consectetur adipiscing elit. Pellentesque ut magna quis {{publication_title}} faucibus pulvinar. In hac habitasse platea dictumst. Mauris commodo placerat urna, a iaculis sapien laoreet eget. Nunc bibendum quis sem a ullamcorper. In nec interdum justo. Sed fringilla volutpat ante vel malesuada. Curabitur erat ipsum, condimentum vel.",
				'size' => 'small',
				'isRequired' => true,
				'isMultilingual' => true,
				'value' => $context->getData('certificateContent')
			]))
			->addField(new FieldRichTextarea('certificateInstitutionDescription', [
				'groupId' => 'contentDocumentSettings',
				'label' => __("plugins.generic.exportReviewerCertificate.contentDocumentSettings.certificateInstitutionDescription.label"),
				'description' => __("plugins.generic.exportReviewerCertificate.contentDocumentSettings.certificateInstitutionDescription.description"),
				'size' => 'large',
				'isMultilingual' => true,
				'value' => $context->getData('certificateInstitutionDescription')
			]))
			->addField(new FieldRichTextarea('certificateDate', [
				'groupId' => 'contentDocumentSettings',
				'label' => __("plugins.generic.exportReviewerCertificate.contentDocumentSettings.certificateDate.label"),
				'description' => __("plugins.generic.exportReviewerCertificate.contentDocumentSettings.certificateDate.description"),
				"tooltip" => "Lorem ipsum dolor {{day_number}} amet {{month_name}} yet {{year_number}}.",
				'size' => 'short',
				'isRequired' => true,
				'isMultilingual' => true,
				'value' => $context->getData('certificateDate')
			]))
			->addField(new FieldRichTextarea('certificateGoodbye', [
				'groupId' => 'contentDocumentSettings',
				'label' => __("plugins.generic.exportReviewerCertificate.contentDocumentSettings.certificateGoodbye.label"),
				'description' => __("plugins.generic.exportReviewerCertificate.contentDocumentSettings.certificateGoodbye.description"),
				'size' => 'short',
				'isRequired' => true,
				'isMultilingual' => true,
				'value' => $context->getData('certificateGoodbye')
			]));

		// Certificate signature settings group
		$this
			->addGroup([
				"id" => "certificateSignatureSettings",
				"label" => __("plugins.generic.exportReviewerCertificate.certificateSignatureSettings.label"),
				"description" => __("plugins.generic.exportReviewerCertificate.certificateSignatureSettings.description")
			])
			->addField(new FieldUploadImage('certificateEditorSignature', [
				'groupId' => 'certificateSignatureSettings',
				'label' => __('plugins.generic.exportReviewerCertificate.certificateSignatureSettings.certificateEditorSignature.label'),
				"description" => __("plugins.generic.exportReviewerCertificate.certificateSignatureSettings.certificateEditorSignature.description"),
				'value' => json_decode($context->getData('certificateEditorSignature')),
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
