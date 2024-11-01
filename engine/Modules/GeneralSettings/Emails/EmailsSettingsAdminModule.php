<?php
namespace UltraCommunity\Modules\GeneralSettings\Emails;

use UltraCommunity\MchLib\Utils\MchHtmlUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;

class EmailsSettingsAdminModule extends \UltraCommunity\Modules\BaseAdminModule
{

	CONST OPTION_SENDER_NAME = 'SN';
	CONST OPTION_SENDER_EMAIL_ADDRESS = 'SEA';
	CONST OPTION_HTML_TEMPLATES_ENABLED = 'HTE';

	CONST OPTION_WELCOME_EMAIL_ENABLED = 'WEA';
	CONST OPTION_WELCOME_EMAIL_SUBJECT = 'WES';
	CONST OPTION_WELCOME_EMAIL_MESSAGE = 'WEM';

	CONST OPTION_ACCOUNT_AWAITING_EMAIL_CONFIRMATION_ENABLED = 'AECE';
	CONST OPTION_ACCOUNT_AWAITING_EMAIL_CONFIRMATION_SUBJECT = 'AECS';
	CONST OPTION_ACCOUNT_AWAITING_EMAIL_CONFIRMATION_MESSAGE = 'AECM';

	CONST OPTION_ACCOUNT_AWAITING_REVIEW_ENABLED = 'AARE';
	CONST OPTION_ACCOUNT_AWAITING_REVIEW_SUBJECT = 'AARS';
	CONST OPTION_ACCOUNT_AWAITING_REVIEW_MESSAGE = 'AARM';

	CONST OPTION_ACCOUNT_APPROVED_ENABLED = 'AAE';
	CONST OPTION_ACCOUNT_APPROVED_SUBJECT = 'AAS';
	CONST OPTION_ACCOUNT_APPROVED_MESSAGE = 'AAM';

	CONST OPTION_ACCOUNT_REJECTED_ENABLED = 'ARE';
	CONST OPTION_ACCOUNT_REJECTED_SUBJECT = 'ARS';
	CONST OPTION_ACCOUNT_REJECTED_MESSAGE = 'ARM';

	CONST OPTION_PASSWORD_RESET_REQUEST_ENABLED = 'PRRE';
	CONST OPTION_PASSWORD_RESET_REQUEST_SUBJECT = 'PRRS';
	CONST OPTION_PASSWORD_RESET_REQUEST_MESSAGE = 'PRRM';

	CONST OPTION_PASSWORD_CHANGED_ENABLED = 'PCE';
	CONST OPTION_PASSWORD_CHANGED_SUBJECT = 'PCS';
	CONST OPTION_PASSWORD_CHANGED_MESSAGE = 'PCM';

	public function __construct()
	{
		parent::__construct();
	}

	public function getDefaultOptions()
	{
		static $arrDefaultSettingOptions = null;
		if(null !== $arrDefaultSettingOptions)
			return $arrDefaultSettingOptions;

		$arrDefaultSettingOptions = array(

			self::OPTION_SENDER_NAME => array(
				'Value'      => MchWpUtils::getCurrentBlogName(),
				'LabelText'  => __('Sender Name', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
			),

			self::OPTION_SENDER_EMAIL_ADDRESS => array(
				'Value'      => MchWpUtils::getAdminEmailAddress(),
				'LabelText'  => __('Sender Email', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
			),

			self::OPTION_WELCOME_EMAIL_ENABLED => array(
				'Value'      => true,
				'LabelText'  => __('Enable User Welcome Email', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),

			self::OPTION_WELCOME_EMAIL_SUBJECT => array(
				'Value'      => sprintf(__('Welcome to %s', 'ultra-community'), '{site_name}'),
				'LabelText'  => __('Welcome Email Subject', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
			),
			
			self::OPTION_WELCOME_EMAIL_MESSAGE => array(
				'Value'      => "Hello {first_name},\n\nWelcome to {site_name}!\n\nHere is some important information about your new account." .
					"You should save this email, so you can refer to it later.\n\nYour Profile link:\n{user_profile_url}\n\nYour username:\n{username}\n\nSign in link:\n{login_url}\n\n\nThanks,\n{site_name}",

				'LabelText'  => __('Welcome Email Message', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXTAREA
			),
			
			self::OPTION_ACCOUNT_AWAITING_EMAIL_CONFIRMATION_ENABLED => array(
				'Value'      => true,
				'LabelText'  => __('Enable Account Awaiting Confirmation Email', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),

			self::OPTION_ACCOUNT_AWAITING_EMAIL_CONFIRMATION_SUBJECT => array(
				'Value'      => __('Activate your account', 'ultra-community'),
				'LabelText'  => __('Account Awaiting Confirmation Subject', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
			),

			self::OPTION_ACCOUNT_AWAITING_EMAIL_CONFIRMATION_MESSAGE => array(
					'Value'      => "Hello {first_name},\n\nWelcome to {site_name}!\n\nThere is one final step to complete your registration process." .
										"Please click on the link below to activate your account.\n{activate_account_url}\n\n\nThanks,\n{site_name}",
					
					'LabelText'  => __('Account Awaiting Confirmation Message', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXTAREA
			),

			self::OPTION_ACCOUNT_AWAITING_REVIEW_ENABLED => array(
				'Value'      => true,
				'LabelText'  => __('Enable Account Awaiting Review Email', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),

			self::OPTION_ACCOUNT_AWAITING_REVIEW_SUBJECT => array(
				'Value'      => __('Your registration is awaiting review', 'ultra-community'),
				'LabelText'  => __('Account Awaiting Review Subject', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
			),

			self::OPTION_ACCOUNT_AWAITING_REVIEW_MESSAGE => array(
					'Value'      => "Hello {first_name},\n\nWelcome to {site_name}!\n\nOnce approved you will be notified and then be able to login and participate.\n\n\nThanks,\n{site_name}",
					'LabelText'  => __('Account Awaiting Review Message', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXTAREA
			),

			self::OPTION_ACCOUNT_APPROVED_ENABLED => array(
				'Value'      => true,
				'LabelText'  => __('Enable Approved Account Email', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),

			self::OPTION_ACCOUNT_APPROVED_SUBJECT => array(
				'Value'      => __('Your registration has been approved', 'ultra-community'),
				'LabelText'  => __('Approved Account Subject', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
			),

			self::OPTION_ACCOUNT_APPROVED_MESSAGE => array(
					'Value'      => "Hello {first_name},\n\nCongratulations, your account has been approved.\n\nWelcome to {site_name}!\n\nHere is some important information about your new account." .
										"You should save this email, so you can refer to it later.\n\nYour Profile link:\n{user_profile_url}Your username:\n{username}\n\nSign in link:\n{login_url}\n\n\nThanks,\n{site_name}",
					'LabelText'  => __('Approved Account Message', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXTAREA
			),

			self::OPTION_ACCOUNT_REJECTED_ENABLED => array(
				'Value'      => true,
				'LabelText'  => __('Enable Rejected Account Email', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),

			self::OPTION_ACCOUNT_REJECTED_SUBJECT => array(
				'Value'      => __('Your registration has been denied', 'ultra-community'),
				'LabelText'  => __('Rejected Account Subject', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
			),

			self::OPTION_ACCOUNT_REJECTED_MESSAGE => array(
					'Value'      => "Hello {first_name},\n\nUnfortunately, your account has not been approved.\n\n\nThanks,\n{site_name}",
					'LabelText'  => __('Rejected Account Message', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXTAREA
			),

			self::OPTION_PASSWORD_RESET_REQUEST_ENABLED => array(
				'Value'      => true,
				'LabelText'  => __('Enable Password Reset Request Email', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),

			self::OPTION_PASSWORD_RESET_REQUEST_SUBJECT => array(
				'Value'      => __('Password reset request', 'ultra-community'),
				'LabelText'  => __('Password Reset Email Subject', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
			),

			self::OPTION_PASSWORD_RESET_REQUEST_MESSAGE => array(
					'Value'      => "Hello {first_name},\n\nWe received a request to reset your password.If you didn't make the request, just ignore this email. Otherwise, you can reset your password by clicking on the below link.\n" .
										"{password_reset_url}\n\n\nThanks,\n{site_name}",
					
					'LabelText'  => __('Password Reset Email Message', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXTAREA
			),

			self::OPTION_PASSWORD_CHANGED_ENABLED => array(
				'Value'      => true,
				'LabelText'  => __('Enable Password Changed Email', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),

			self::OPTION_PASSWORD_CHANGED_SUBJECT => array(
				'Value'      => __('Password successfully changed'),
				'LabelText'  => __('Password Changed Email Subject', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
			),

			self::OPTION_PASSWORD_CHANGED_MESSAGE=> array(
					'Value'      => "Hello {first_name},\n\nYour password was successfully changed.\n\n\nThanks,\n{site_name}",
					'LabelText'  => __('Password Changed Email Message', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXTAREA
			),

		);

		return $arrDefaultSettingOptions;

	}

	public function validateModuleSettingsFields($arrSettingOptions)
	{
		$arrSettingOptions = $this->sanitizeModuleSettings($arrSettingOptions);

		return $arrSettingOptions;
	}


	public function renderModuleSettingsField(array $arrSettingsField)
	{
		$fieldKey   = key( $arrSettingsField );
		$fieldValue = $this->getOption( $fieldKey );

		MchWpUtils::addFilterHook($this->getFieldAttributesFilterName($fieldKey), function( $arrFieldAttributes ) use ( $fieldValue, $fieldKey) {

			//!empty($fieldValue) ?: $arrFieldAttributes['value'] = EmailsSettingsAdminModule::getInstance()->getDefaultOptionValue($fieldKey);

			return $arrFieldAttributes;
		});

		return parent::renderModuleSettingsField($arrSettingsField);

	}
}