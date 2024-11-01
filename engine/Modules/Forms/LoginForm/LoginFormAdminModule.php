<?php

namespace UltraCommunity\Modules\Forms\LoginForm;

use UltraCommunity\MchLib\Utils\MchHtmlUtils;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Modules\Forms\BaseForm\BaseFormAdminModule;
use UltraCommunity\Controllers\PostTypeController;

class LoginFormAdminModule extends BaseFormAdminModule
{
	CONST OPTION_REGISTRATION_PAGE_ID = "RegistrationPageId";
	CONST OPTION_REGISTRATION_TEXT    = "RegistrationText";

	protected function __construct()
	{
		parent::__construct();
	}

	public function getDefaultOptions()
	{
		$arrDefaultOptions = parent::getDefaultOptions();
		$arrDefaultOptions[parent::OPTION_FORM_TITLE]['Value']               = __('New Login Form', 'ultra-community');
		$arrDefaultOptions[parent::OPTION_FORM_HEADER_TITLE]['Value']        = __('Sign in', 'ultra-community');
		$arrDefaultOptions[parent::OPTION_FORM_PRIMARY_BUTTON_TEXT]['Value'] = __('Sign in', 'ultra-community');


		$arrDefaultOptions = MchUtils::addArrayKeyValueAfterSpecificKey(parent::OPTION_FORM_FORGOT_PASSWORD_LABEL, $arrDefaultOptions, self::OPTION_REGISTRATION_PAGE_ID,  array(
				'Value'      => null,
				'LabelText'  => __('Registration Form Page', 'ultra-community'),
				'HelpText'   => __('Select Registration Form Page if you\'d like to add registration url to the login form', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT
		));

		$arrDefaultOptions = MchUtils::addArrayKeyValueAfterSpecificKey(self::OPTION_REGISTRATION_PAGE_ID, $arrDefaultOptions, self::OPTION_REGISTRATION_TEXT,  array(
				'Value'      => __("Don't have an account? Register with us", 'ultra-community'),
				'LabelText'  => __('Registration Link Text', 'ultra-community'),
				'HelpText'   => __('Registration link text', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
		));

		unset($arrDefaultOptions[parent::OPTION_USER_ROLE_CUSTOM_POST_ID]);
		unset($arrDefaultOptions[parent::OPTION_FORM_MAX_WIDTH]);
		unset($arrDefaultOptions[parent::OPTION_ASSIGNED_USER_ROLES]);

		return $arrDefaultOptions;
	}

	public  function validateModuleSettingsFields($arrSettingOptions)
	{
		$arrSettingOptions = $this->sanitizeModuleSettings($arrSettingOptions);

		if(empty($arrSettingOptions[self::OPTION_FORM_TITLE]))
		{
			$this->registerErrorMessage(__('Please provide a Name for this form!', 'ultra-community'));
			return $this->getAllSavedOptions();
		}

//		if(empty($arrSettingOptions[self::OPTION_FORM_MAX_WIDTH]))
//		{
//			$this->registerErrorMessage(__('Please provide the Maximum Width of this form !', 'ultra-community'));
//			return $this->getAllSavedOptions();
//		}

		if(empty($arrSettingOptions[self::OPTION_FORM_PRIMARY_BUTTON_TEXT]))
		{
			$this->registerErrorMessage(__('Please provide the Primary Button Text for this form !', 'ultra-community'));
			return $this->getAllSavedOptions();
		}

		$loginCustomPostType = (null !== $this->getCustomPostType()) ? $this->getCustomPostType() : PostTypeController::getPostTypeInstance(PostTypeController::POST_TYPE_LOGIN_FORM);

		$loginCustomPostType->PostTitle = $arrSettingOptions[self::OPTION_FORM_TITLE];

		PostTypeController::publishPostType($loginCustomPostType);

		$this->setCustomPostType($loginCustomPostType);

		return parent::validateModuleSettingsFields($arrSettingOptions);

	}


	public function renderModuleSettingsField(array $arrSettingsField)
	{
		$fieldKey = key($arrSettingsField);
		$fieldAttrsFilterHook = $this->getFieldAttributesFilterName($arrSettingsField);

		$fieldValue = $this->getOption($fieldKey);


		if($fieldKey === self::OPTION_REGISTRATION_PAGE_ID)
		{
			MchWpUtils::addFilterHook($fieldAttrsFilterHook, function($arrFieldAttributes) use($fieldValue) {
				$arrFieldAttributes['value'] = $fieldValue;
				$arrFieldAttributes['options'] = array();
				$arrFieldAttributes['class'] = array('uc-select2');


				foreach(get_pages() as $wpPost)
				{
					$arrFieldAttributes['options'][$wpPost->ID] = esc_html($wpPost->post_title) . ' ( ID : ' . $wpPost->ID . ' )';
				}


				if(empty($arrFieldAttributes['options'][$fieldValue]))
				{
					$arrFieldAttributes['options'] = array( 0 => __('Select registration page', 'ultra-community')) + $arrFieldAttributes['options'];
				}

				return $arrFieldAttributes;

			});

		}

		return parent::renderModuleSettingsField($arrSettingsField);

	}



}