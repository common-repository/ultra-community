<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */
namespace UltraCommunity\Modules\GeneralSettings\FrontPage;

use UltraCommunity\Controllers\FrontPageController;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Controllers\UserRoleController;
use UltraCommunity\MchLib\Utils\MchHtmlUtils;
use UltraCommunity\MchLib\Utils\MchHttpRequest;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\Repository\WpPostRepository;
use UltraCommunity\MchLib\WordPress\Repository\WpUserRepository;
use UltraCommunity\Modules\BaseAdminModule;
use UltraCommunity\Modules\Forms\RegisterForm\RegisterFormAdminModule;
use UltraCommunity\Modules\GeneralSettings\Plugin\PluginSettingsAdminModule;
use UltraCommunity\Modules\UserRole\UserRoleAdminModule;
use UltraCommunity\UltraCommHelper;
use UltraCommunity\UltraCommUtils;

class FrontPageSettingsAdminModule extends BaseAdminModule
{
	CONST LOGIN_PAGE_ID  = 'LP';

	CONST REGISTER_PAGE_ID     = 'RP';

	CONST USER_PROFILE_PAGE_ID = 'UP';

	CONST FORGOT_PASSWORD_PAGE_ID = 'FP';

	CONST MEMBERS_DIRECTORY_PAGE_ID = 'MP';

	CONST GROUPS_DIRECTORY_PAGE_ID = 'GroupsPageId';

	CONST USE_GLOBAL_PAGE_TEMPLATE = 'UseGlobalTemplate';

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

			self::LOGIN_PAGE_ID => array(
				'Value'      => NULL,
				'LabelText'  => __('Default Login Page', 'ultra-community'),
				//'HelpText'   => __('Set the Default Login Page', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT
			),

//			self::LOGIN_PAGE_SLUG => array(
//				'Value'      => NULL,
//				'LabelText'  => __('Default Login Page URL', 'ultra-community'),
//				'HelpText'   => __('Set the page slug of the Default Login Page', 'ultra-community'),
//				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
//			),

			self::REGISTER_PAGE_ID => array(
				'Value'      => NULL,
				'LabelText'  => __('Default Registration Page', 'ultra-community'),
				//'HelpText'   => __('Set the Default Registration Page', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT
			),

//			self::REGISTER_PAGE_SLUG => array(
//				'Value'      => NULL,
//				'LabelText'  => __('Default Registration Page URL', 'ultra-community'),
//				'HelpText'   => __('Set the page slug of the Default Registration Page', 'ultra-community'),
//				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
//			),

			self::USER_PROFILE_PAGE_ID => array(
				'Value'      => NULL,
				'LabelText'  => __('Default User Profile Page', 'ultra-community'),
				//'HelpText'   => __('Set the Default User Profile Page', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT
			),

//			self::USER_PROFILE_PAGE_SLUG => array(
//				'Value'      => NULL,
//				'LabelText'  => __('Default User Profile Page URL', 'ultra-community'),
//				'HelpText'   => __('Set the page slug of the User Profile Page', 'ultra-community'),
//				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
//			),

			self::FORGOT_PASSWORD_PAGE_ID => array(
					'Value'      => NULL,
					'LabelText'  => __('Default Forgot Password Page', 'ultra-community'),
					//'HelpText'   => __('Set the Default Forgot Password Page', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT
			),

//			self::FORGOT_PASSWORD_PAGE_SLUG => array(
//					'Value'      => NULL,
//					'LabelText'  => __('Default Forgot Password Page URL', 'ultra-community'),
//					'HelpText'   => __('Set the page slug of the Forgot Password Page', 'ultra-community'),
//					'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
//			),


			self::MEMBERS_DIRECTORY_PAGE_ID => array(
				'Value'      => NULL,
				'LabelText'  => __('Default Members Directory Page', 'ultra-community'),
				//'HelpText'   => __('Set the Default Members Directory Page', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT
			),

			self::GROUPS_DIRECTORY_PAGE_ID => array(
				'Value'      => NULL,
				'LabelText'  => __('Default Groups Directory Page', 'ultra-community'),
				//'HelpText'   => __('Set the Default Members Directory Page', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT
			),

			self::USE_GLOBAL_PAGE_TEMPLATE => array(
					'Value'      => true,
					'LabelText'  => __('Use Built-In Global Page Template', 'ultra-community'),
					'HelpText'   => __('If this option is enabled, the plugin will use its internal page template. Disable this option if you prefer to handle page templates using theme options.', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),

		);

		return $arrDefaultSettingOptions;

	}



	public function validateModuleSettingsFields( $arrSettingOptions )
	{
		$arrSettingOptions = $this->sanitizeModuleSettings($arrSettingOptions);

		if(empty($arrSettingOptions[self::LOGIN_PAGE_ID])){
			$this->registerErrorMessage(__('Please provide the Login Page!', 'ultra-community'));
			return $this->getAllSavedOptions();
		}

		if(empty($arrSettingOptions[self::REGISTER_PAGE_ID])){
			$this->registerErrorMessage(__('Please provide the Registration Page!', 'ultra-community'));
			return $this->getAllSavedOptions();
		}

		if(empty($arrSettingOptions[self::USER_PROFILE_PAGE_ID])){
			$this->registerErrorMessage(__('Please provide the User Profile Page!', 'ultra-community'));
			return $this->getAllSavedOptions();
		}

		if(empty($arrSettingOptions[self::FORGOT_PASSWORD_PAGE_ID])){
			$this->registerErrorMessage(__('Please provide the Forgot Password Page!', 'ultra-community'));
			return $this->getAllSavedOptions();
		}

		if(empty($arrSettingOptions[self::MEMBERS_DIRECTORY_PAGE_ID])){
			$this->registerErrorMessage(__('Please provide the Members Directory Page!', 'ultra-community'));
			return $this->getAllSavedOptions();
		}

		if(empty($arrSettingOptions[self::GROUPS_DIRECTORY_PAGE_ID])){
			$this->registerErrorMessage(__('Please provide the Groups Directory Page!', 'ultra-community'));
			return $this->getAllSavedOptions();
		}

		$arrSettingOptions[self::LOGIN_PAGE_ID]             = absint($arrSettingOptions[self::LOGIN_PAGE_ID]);
		$arrSettingOptions[self::REGISTER_PAGE_ID]          = absint($arrSettingOptions[self::REGISTER_PAGE_ID]);
		$arrSettingOptions[self::USER_PROFILE_PAGE_ID]      = absint($arrSettingOptions[self::USER_PROFILE_PAGE_ID]);
		$arrSettingOptions[self::FORGOT_PASSWORD_PAGE_ID]   = absint($arrSettingOptions[self::FORGOT_PASSWORD_PAGE_ID]);
		$arrSettingOptions[self::MEMBERS_DIRECTORY_PAGE_ID] = absint($arrSettingOptions[self::MEMBERS_DIRECTORY_PAGE_ID]);
		$arrSettingOptions[self::GROUPS_DIRECTORY_PAGE_ID]  = absint($arrSettingOptions[self::GROUPS_DIRECTORY_PAGE_ID]);

//		if(empty($arrSettingOptions[self::LOGIN_PAGE_SLUG])){
//			$this->registerErrorMessage(__('Please provide a slug for Login Page!', 'ultra-community'));
//			return $this->getAllSavedOptions();
//		}
//
//		if(empty($arrSettingOptions[self::REGISTER_PAGE_SLUG])){
//			$this->registerErrorMessage(__('Please provide a slug for Registration Page!', 'ultra-community'));
//			return $this->getAllSavedOptions();
//		}
//
//		if(empty($arrSettingOptions[self::USER_PROFILE_PAGE_SLUG])){
//			$this->registerErrorMessage(__('Please provide a slug for User Profile Page!', 'ultra-community'));
//			return $this->getAllSavedOptions();
//		}
//
//		if(empty($arrSettingOptions[self::FORGOT_PASSWORD_PAGE_SLUG])){
//			$this->registerErrorMessage(__('Please provide a slug for Forgot Password Page!', 'ultra-community'));
//			return $this->getAllSavedOptions();
//		}
//
//		if(empty($arrSettingOptions[self::MEMBERS_DIRECTORY_PAGE_SLUG])){
//			$this->registerErrorMessage(__('Please provide a slug for Members Directory Page!', 'ultra-community'));
//			return $this->getAllSavedOptions();
//		}
//
//		$arrSettingOptions[self::LOGIN_PAGE_SLUG]             = MchWpUtils::formatUrlPath($arrSettingOptions[self::LOGIN_PAGE_SLUG]);
//		$arrSettingOptions[self::REGISTER_PAGE_SLUG]          = MchWpUtils::formatUrlPath($arrSettingOptions[self::REGISTER_PAGE_SLUG]);
//		$arrSettingOptions[self::USER_PROFILE_PAGE_SLUG]      = MchWpUtils::formatUrlPath($arrSettingOptions[self::USER_PROFILE_PAGE_SLUG]);
//		$arrSettingOptions[self::FORGOT_PASSWORD_PAGE_SLUG]   = MchWpUtils::formatUrlPath($arrSettingOptions[self::FORGOT_PASSWORD_PAGE_SLUG]);
//		$arrSettingOptions[self::MEMBERS_DIRECTORY_PAGE_SLUG] = MchWpUtils::formatUrlPath($arrSettingOptions[self::MEMBERS_DIRECTORY_PAGE_SLUG]);
//
//		if( ($wpPost = WpPostRepository::findByPostId($arrSettingOptions[self::LOGIN_PAGE_ID]) )){
//			if($wpPost->post_name !== $arrSettingOptions[self::LOGIN_PAGE_SLUG]){
//				$wpPost->post_name = $arrSettingOptions[self::LOGIN_PAGE_SLUG];
//				WpPostRepository::save($wpPost->to_array());
//				$wpPost = WpPostRepository::findByPostId($wpPost->ID);
//				$arrSettingOptions[self::LOGIN_PAGE_SLUG] = $wpPost->post_name;
//			}
//		}
//
//		if( ($wpPost = WpPostRepository::findByPostId($arrSettingOptions[self::REGISTER_PAGE_ID]) )){
//			if($wpPost->post_name !== $arrSettingOptions[self::REGISTER_PAGE_SLUG]){
//				$wpPost->post_name = $arrSettingOptions[self::REGISTER_PAGE_SLUG];
//				WpPostRepository::save($wpPost->to_array());
//				$wpPost = WpPostRepository::findByPostId($wpPost->ID);
//				$arrSettingOptions[self::REGISTER_PAGE_SLUG] = $wpPost->post_name;
//			}
//		}
//
//		if( ($wpPost = WpPostRepository::findByPostId($arrSettingOptions[self::USER_PROFILE_PAGE_ID]) )){
//			if($wpPost->post_name !== $arrSettingOptions[self::USER_PROFILE_PAGE_SLUG]){
//				$wpPost->post_name = $arrSettingOptions[self::USER_PROFILE_PAGE_SLUG];
//				WpPostRepository::save($wpPost->to_array());
//				$wpPost = WpPostRepository::findByPostId($wpPost->ID);
//				$arrSettingOptions[self::USER_PROFILE_PAGE_SLUG] = $wpPost->post_name;
//			}
//		}
//
//		if( ($wpPost = WpPostRepository::findByPostId($arrSettingOptions[self::FORGOT_PASSWORD_PAGE_ID]) )){
//			if($wpPost->post_name !== $arrSettingOptions[self::FORGOT_PASSWORD_PAGE_SLUG]){
//				$wpPost->post_name = $arrSettingOptions[self::FORGOT_PASSWORD_PAGE_SLUG];
//				WpPostRepository::save($wpPost->to_array());
//				$wpPost = WpPostRepository::findByPostId($wpPost->ID);
//				$arrSettingOptions[self::FORGOT_PASSWORD_PAGE_SLUG] = $wpPost->post_name;
//			}
//		}
//
//
//		if( ($wpPost = WpPostRepository::findByPostId($arrSettingOptions[self::MEMBERS_DIRECTORY_PAGE_ID]) )){
//			if($wpPost->post_name !== $arrSettingOptions[self::MEMBERS_DIRECTORY_PAGE_SLUG]){
//				$wpPost->post_name = $arrSettingOptions[self::MEMBERS_DIRECTORY_PAGE_SLUG];
//				WpPostRepository::save($wpPost->to_array());
//				$wpPost = WpPostRepository::findByPostId($wpPost->ID);
//				$arrSettingOptions[self::MEMBERS_DIRECTORY_PAGE_SLUG] = $wpPost->post_name;
//			}
//		}

		PluginSettingsAdminModule::getInstance()->saveOption(PluginSettingsAdminModule::SETTINGS_LAST_UPDATED, MchHttpRequest::getServerRequestTime());

		$this->registerSuccessMessage(__('Your changes were successfully saved!', 'ultra-community'));
		return $arrSettingOptions;

	}


	public function renderModuleSettingsField(array $arrSettingsField)
	{
		$fieldKey = key($arrSettingsField);
		$fieldAttrsFilterHook = $this->getFieldAttributesFilterName($arrSettingsField);

		$fieldValue = $this->getOption($fieldKey);

		if(in_array($fieldKey, array(self::REGISTER_PAGE_ID, self::LOGIN_PAGE_ID, self::USER_PROFILE_PAGE_ID, self::FORGOT_PASSWORD_PAGE_ID, self::MEMBERS_DIRECTORY_PAGE_ID, self::GROUPS_DIRECTORY_PAGE_ID)))
		{
			$arrLastPublishedPages = WpPostRepository::findByPostType('page', array('posts_per_page' => 100));

			MchWpUtils::addFilterHook($fieldAttrsFilterHook, function($arrFieldAttributes) use($fieldValue, $arrLastPublishedPages) {

				$arrFieldAttributes['value'] = $fieldValue;
				$arrFieldAttributes['options'] = array();
				$arrFieldAttributes['class'] = array('uc-select2');
				foreach($arrLastPublishedPages as $publishedPage){
					$arrFieldAttributes['options'][$publishedPage->ID] = $publishedPage->post_title . ' ( ID : ' . $publishedPage->ID . ' )';
				}

				return $arrFieldAttributes;

			});
		}


//		if($fieldKey === self::REGISTER_PAGE_ID)
//		{
//			MchWpUtils::addFilterHook($fieldAttrsFilterHook, function($arrFieldAttributes) use($fieldValue) {
//				$arrFieldAttributes['value'] = $fieldValue;
//				$arrFieldAttributes['options'] = array();
//				$arrFieldAttributes['class'] = array('uc-select2');
//
//				foreach(PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_REGISTER_FORM) as $registerPostType)
//				{
//					if(null === ($registerFormAdminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($registerPostType)))
//						continue;
//
//					if(MchUtils::isNullOrEmpty( $pageId = $registerFormAdminModuleInstance->getOption(RegisterFormAdminModule::OPTION_ASSIGNED_PAGE_ID)))
//						continue;
//
//					if( $wpPost = WpPostRepository::findByPostId($pageId) ){
//						$arrFieldAttributes['options'][$pageId] = $wpPost->post_title . ' ( ID : ' . $wpPost->ID . ' )';
//					}
//
//				}
//
//
//				return $arrFieldAttributes;
//
//			});
//
//		}


			$userRoleAdminInstance = PostTypeController::getAssociatedAdminModuleInstance(UserRoleController::getDefaultUserRolePostType());
			$roleKey = (null !== $userRoleAdminInstance) ? $userRoleAdminInstance->getOption(UserRoleAdminModule::OPTION_ROLE_SLUG) : null;
			MchWpUtils::addFilterHook($this->getFieldAttributesFilterName($fieldKey) .  '-output-html', function($fieldOutputHtml) use ($fieldKey, $fieldValue, $roleKey) {

			$previewUrl = UltraCommUtils::getPreviewRequestUrl($fieldValue);
			$previewText = null;

			switch ($fieldKey)
			{
				case self::LOGIN_PAGE_ID:
					$previewText = esc_html__('Preview Login Page', 'ultra-community');
					break;

				case self::USER_PROFILE_PAGE_ID :

					if(null === $roleKey)
						break;

//					$arrUsersWithDefaultRole = WpUserRepository::getUsersByRole($roleKey, array('number' => 1));
//					if(empty($arrUsersWithDefaultRole))
//					{
//						$arrUsersWithDefaultRole = WpUserRepository::getUsersByRole(null , array('number' => 1, 'order' => 'ASC', 'role__not_in' => array_keys(UserRoleController::getAllRegisteredUserRoles())));
//					}
//
//					if(empty($arrUsersWithDefaultRole))
//						break;


					$previewUrl = UltraCommHelper::getUserProfileUrl(get_current_user_id()); //UltraCommHelper::getUserProfileUrl($arrUsersWithDefaultRole[0]->ID);

					$previewText = __('Preview User Profile Page', 'ultra-community');
					break;

				case self::FORGOT_PASSWORD_PAGE_ID :
					$previewText = esc_html__('Preview Forgot Password Page', 'ultra-community');
					break;

				case self::MEMBERS_DIRECTORY_PAGE_ID :
					$previewText = esc_html__('Preview Members Directory Page', 'ultra-community');
					break;

				case self::GROUPS_DIRECTORY_PAGE_ID :
					$previewText = esc_html__('Preview Groups Directory Page', 'ultra-community');
					break;

				case self::REGISTER_PAGE_ID :
					$previewText = esc_html__('Preview Registration Page', 'ultra-community');
					break;

			}

			if(empty($previewText) || empty($previewUrl))
				return $fieldOutputHtml;

			return $fieldOutputHtml . '<a style = "display:inline-block;  margin-left:35px;" target="__blank" href="'. $previewUrl. '">' . $previewText . '</a>';

		});

		return parent::renderModuleSettingsField($arrSettingsField);

	}

}