<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Modules\GeneralSettings\User;

use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Controllers\UserRoleController;
use UltraCommunity\MchLib\Utils\MchHtmlUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Modules\BaseAdminModule;
use UltraCommunity\Modules\UserRole\UserRoleAdminModule;
use UltraCommunity\UltraCommHelper;

class UserSettingsAdminModule extends BaseAdminModule
{
	CONST OPTION_DEFAULT_USER_ROLE  = 'DUR';
	CONST OPTION_DISPLAY_NAME       = 'DN';

	CONST OPTION_DEFAULT_AVATAR_URL = 'DPU';
	CONST OPTION_DEFAULT_COVER_URL  = 'DCU';

	CONST OPTION_REDIRECT_AUTHOR_USER_URL  = 'RAURL';
	CONST OPTION_REDIRECT_COMMENT_USER_URL = 'RCURL';
	
	CONST OPTION_ENABLE_GRAVATAR_URL = 'EGURL';
	
	CONST USER_DISPLAY_NAME_DEFAULT         = 1;
	CONST USER_DISPLAY_NAME_FIRST_NAME      = 2;
	CONST USER_DISPLAY_NAME_FIRST_LAST_NAME = 3;
	CONST USER_DISPLAY_NAME_LAST_FIRST_NAME = 4;





	protected function __construct()
	{
		parent::__construct();
	}

	public function getDefaultOptions()
	{
		static $arrDefaultSettingOptions = null;
		if(null !== $arrDefaultSettingOptions)
			return $arrDefaultSettingOptions;

		$arrDefaultSettingOptions = array(

			self::OPTION_DEFAULT_USER_ROLE => array(
				'Value'      => NULL,
				'LabelText'  => __('Default User Role', 'ultra-community'),
				'HelpText'   => __('Choose the Community Default User Role', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT
			),

			self::OPTION_DISPLAY_NAME => array(
				'Value'      => self::USER_DISPLAY_NAME_DEFAULT,
				'LabelText'  => __('Display Name', 'ultra-community'),
				'HelpText'   => __('Select how the UserName will be displayed', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT
			),
			
			self::OPTION_ENABLE_GRAVATAR_URL => array(
				'Value'      => true,
				'LabelText'  => __('Enable User Gravatar URL', 'ultra-community'),
				'HelpText'   => __('Controls whether to use gravatar url for profile photo if user does not upload one. If the user do not have a gravatar URL the default photo will be displayed!', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),
			
			
			self::OPTION_REDIRECT_AUTHOR_USER_URL => array(
				'Value'      => true,
				'LabelText'  => __('Redirect Author User URL to profile', 'ultra-community'),
				'HelpText'   => __('Controls if author pages will be automatically redirected to the author\'s profile page', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),

			self::OPTION_REDIRECT_AUTHOR_USER_URL => array(
					'Value'      => true,
					'LabelText'  => __('Redirect Author User URL to profile', 'ultra-community'),
					'HelpText'   => __('Controls if author pages will be automatically redirected to the author\'s profile page', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),

			self::OPTION_REDIRECT_COMMENT_USER_URL => array(
					'Value'      => true,
					'LabelText'  => __('Redirect Comment User URL to profile', 'ultra-community'),
					'HelpText'   => __('Controls if comment user url will be automatically redirected to their profile page', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),

			self::OPTION_DEFAULT_AVATAR_URL => array(
					'Value'      => UltraCommHelper::getUserDefaultAvatarUrl(),
					'LabelText'  => __('Default User Profile Picture URL', 'ultra-community'),
					'HelpText'   => __('Set the default user profile avatar url. If left blank the built in default avatar url will be used', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
			),

			self::OPTION_DEFAULT_COVER_URL => array(
					'Value'      => UltraCommHelper::getUserDefaultCoverUrl(),
					'LabelText'  => __('Default User Profile Cover URL', 'ultra-community'),
					'HelpText'   => __('Set the default user profile cover url. If left blank the built in default cover url will be used', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
			),

		);

		return $arrDefaultSettingOptions;

	}

	public  function validateModuleSettingsFields($arrSettingOptions)
	{
		return $arrSettingOptions;
	}

	public function renderModuleSettingsField(array $arrSettingsField)
	{

		$fieldKey = key($arrSettingsField);
		$fieldAttrsFilterHook = $this->getFieldAttributesFilterName($arrSettingsField);

		$fieldInfoOption = $this->getOption($fieldKey);

		if($fieldKey === self::OPTION_DEFAULT_USER_ROLE)
		{
			MchWpUtils::addFilterHook($fieldAttrsFilterHook, function($arrFieldAttributes) use($fieldInfoOption) {

				$arrFieldAttributes['class'][] = 'uc-select2';
				$arrFieldAttributes['value'] = $fieldInfoOption;
				$arrFieldAttributes['options'] = array();

				foreach((array)PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_ROLE) as $customPostUserRole)
				{
					$adminModule = PostTypeController::getAssociatedAdminModuleInstance($customPostUserRole);
					if( ! $adminModule instanceof UserRoleAdminModule)
						continue;

					if(UserRoleController::isDefaultAdminRole($adminModule->getOption(UserRoleAdminModule::OPTION_ROLE_SLUG)))
						continue;
					
					$arrFieldAttributes['options'][$customPostUserRole->PostId] = $adminModule->getOption(UserRoleAdminModule::OPTION_ROLE_TITLE);
				}

				return $arrFieldAttributes;

			});
		}


		if($fieldKey === self::OPTION_DISPLAY_NAME)
		{

			MchWpUtils::addFilterHook($fieldAttrsFilterHook, function($arrFieldAttributes) use($fieldInfoOption) {

				$arrFieldAttributes['class'][] = 'uc-select2';
				$arrFieldAttributes['value'] = $fieldInfoOption;

				$arrFieldAttributes['options'] = array(
					UserSettingsAdminModule::USER_DISPLAY_NAME_DEFAULT         => __('Default WP Settings', 'ultracomm'),
					UserSettingsAdminModule::USER_DISPLAY_NAME_FIRST_LAST_NAME => __('First Name & Last Name', 'ultracomm'),
					UserSettingsAdminModule::USER_DISPLAY_NAME_LAST_FIRST_NAME => __('Last Name & First Name', 'ultracomm'),
					UserSettingsAdminModule::USER_DISPLAY_NAME_FIRST_NAME      => __('First Name', 'ultracomm'),
				);


				return $arrFieldAttributes;

			});

		}


		return parent::renderModuleSettingsField($arrSettingsField);
	}

}