<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Modules\Forms\UserProfileForm;

use UltraCommunity\MchLib\Utils\MchHtmlUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Modules\Forms\BaseForm\BaseFormAdminModule;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Modules\UserRole\UserRoleAdminModule;

class UserProfileFormAdminModule extends BaseFormAdminModule
{



	protected function __construct()
	{
		parent::__construct();
	}

	public function getDefaultOptions()
	{
		$arrDefaultOptions = parent::getDefaultOptions();
		$arrDefaultOptions[parent::OPTION_FORM_TITLE]['Value']               = __('New Profile Form', 'ultra-community');
		$arrDefaultOptions[parent::OPTION_FORM_PRIMARY_BUTTON_TEXT]['Value'] = __('Save Changes', 'ultra-community');

		unset($arrDefaultOptions[parent::OPTION_FORM_FORGOT_PASSWORD_LABEL]);
		unset($arrDefaultOptions[parent::OPTION_FORM_REMEMBER_ME_LABEL]);
		unset($arrDefaultOptions[parent::OPTION_FORM_HEADER_TITLE]);
		unset($arrDefaultOptions[parent::OPTION_FORM_PRIMARY_BUTTON_TEXT]);
		unset($arrDefaultOptions[parent::OPTION_FORM_MAX_WIDTH]);



		unset($arrDefaultOptions[parent::OPTION_USER_ROLE_CUSTOM_POST_ID]);

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
//			$this->registerErrorMessage(__('Please provide the Maximum Width for this form !', 'ultra-community'));
//			return $this->getAllSavedOptions();
//		}

//		if(empty($arrSettingOptions[self::OPTION_FORM_PRIMARY_BUTTON_TEXT]))
//		{
//			$this->registerErrorMessage(__('Please provide the Primary Button Text for this form !', 'ultra-community'));
//			return $this->getAllSavedOptions();
//		}

		$customPostType = (null !== $this->getCustomPostType()) ? $this->getCustomPostType() : PostTypeController::getPostTypeInstance(PostTypeController::POST_TYPE_REGISTER_FORM);

		$customPostType->PostTitle = $arrSettingOptions[self::OPTION_FORM_TITLE];

		PostTypeController::publishPostType($customPostType);

		$this->setCustomPostType($customPostType);

		return parent::validateModuleSettingsFields($arrSettingOptions);

	}


	public function renderModuleSettingsField(array $arrSettingsField)
	{
		$fieldKey   = key( $arrSettingsField );
		$fieldValue = $this->getOption( $fieldKey );
		$actualCustomPostId = $this->getCustomPostTypeId();

		MchWpUtils::addFilterHook($this->getFieldAttributesFilterName($fieldKey), function( $arrFieldAttributes ) use ($fieldKey, $fieldValue, $actualCustomPostId){

			if($fieldKey === UserProfileFormAdminModule::OPTION_ASSIGNED_USER_ROLES )
			{
				$arrFieldAttributes['multiple'] = 'multiple';
				$arrFieldAttributes['class'][]  = 'uc-select2-multiple';
				$arrFieldAttributes['value']    = $fieldValue;
				$arrFieldAttributes['options']  = array();

				foreach ((array)PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_ROLE) as $customPostUserRole)
				{

					$adminModule = PostTypeController::getAssociatedAdminModuleInstance($customPostUserRole);
					if (!$adminModule instanceof UserRoleAdminModule)
						continue;

					$arrFieldAttributes['options'][$customPostUserRole->PostId] = $adminModule->getOption(UserRoleAdminModule::OPTION_ROLE_TITLE);
				}

				$arrUsedUserRoles = array();
				foreach ((array)PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_PROFILE_FORM) as $customPostProfileForm)
				{
					if($customPostProfileForm->PostId == $actualCustomPostId)
						continue;

					$adminModule = PostTypeController::getAssociatedAdminModuleInstance($customPostProfileForm);

					$arrUsedUserRoles = array_merge($arrUsedUserRoles, (array)$adminModule->getOption(UserProfileFormAdminModule::OPTION_ASSIGNED_USER_ROLES));
				}

				foreach($arrUsedUserRoles as $alreadyUsedRoleId)
				{
					unset($arrFieldAttributes['options'][$alreadyUsedRoleId]);
				}

			}


			return $arrFieldAttributes;

		});

		return parent::renderModuleSettingsField($arrSettingsField);

	}



}