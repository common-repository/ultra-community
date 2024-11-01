<?php

namespace UltraCommunity\Modules\Forms\ForgotPasswordForm;

use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Modules\Forms\BaseForm\BaseFormAdminModule;
use UltraCommunity\Controllers\PostTypeController;

class ForgotPasswordFormAdminModule extends BaseFormAdminModule
{

	protected function __construct()
	{
		parent::__construct();
	}

	public function getDefaultOptions()
	{
		$arrDefaultOptions = parent::getDefaultOptions();
		$arrDefaultOptions[parent::OPTION_FORM_TITLE]['Value']               = __('Forgot Password Form', 'ultra-community');
		$arrDefaultOptions[parent::OPTION_FORM_HEADER_TITLE]['Value']        = __('Forgot Password', 'ultra-community');
		$arrDefaultOptions[parent::OPTION_FORM_PRIMARY_BUTTON_TEXT]['Value'] = __('Submit', 'ultra-community');

		unset($arrDefaultOptions[parent::OPTION_USER_ROLE_CUSTOM_POST_ID]);
		unset($arrDefaultOptions[parent::OPTION_FORM_FORGOT_PASSWORD_LABEL]);
		unset($arrDefaultOptions[parent::OPTION_FORM_REMEMBER_ME_LABEL]);

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



}