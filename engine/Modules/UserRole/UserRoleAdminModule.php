<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Modules\UserRole;


use UltraCommunity\Controllers\ModulesController;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Controllers\TasksController;
use UltraCommunity\Controllers\UserRoleController;
use UltraCommunity\MchLib\Utils\MchHtmlUtils;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\Repository\WpUserRepository;
use UltraCommunity\Modules\BaseAdminModule;
use UltraCommunity\UltraCommException;
use UltraCommunity\Tasks\UserRolesMapperTask;

class UserRoleAdminModule extends BaseAdminModule
{

	CONST OPTION_ROLE_TITLE    = 'Title';
	CONST OPTION_ROLE_SLUG     = 'RoleSlug';
	CONST OPTION_ROLE_PRIORITY = 'Priority';

	CONST OPTION_CAN_EDIT_OWN_PROFILE         = UserRoleController::USER_CAP_EDIT_OWN_PROFILE;
	CONST OPTION_CAN_EDIT_USER_PROFILES       = UserRoleController::USER_CAP_EDIT_OTHER_PROFILES;

	CONST OPTION_CAN_VIEW_OTHER_USER_PROFILES = UserRoleController::USER_CAP_VIEW_OTHER_PROFILES;
	CONST OPTION_CAN_VIEW_PRIVATE_PROFILES    = UserRoleController::USER_CAP_VIEW_PRIVATE_PROFILES;
	CONST OPTION_CAN_VIEW_ADMIN_TOOLBAR       = UserRoleController::USER_CAP_VIEW_ADMIN_TOOLBAR;

	CONST OPTION_CAN_SETUP_PROFILE_PRIVACY    = UserRoleController::USER_CAP_SETUP_PROFILE_PRIVACY;

	CONST OPTION_CAN_ACCESS_WP_DASHBOARD      = UserRoleController::USER_CAP_ACCESS_WP_DASHBOARD;

	CONST OPTION_CAN_DELETE_ACCOUNT           = UserRoleController::USER_CAP_DELETE_ACCOUNT;
	CONST OPTION_CAN_CHANGE_PASSWORD          = UserRoleController::USER_CAP_CHANGE_PASSWORD;

	CONST OPTION_CAN_CHANGE_PROFILE_AVATAR    = UserRoleController::USER_CAP_CHANGE_PROFILE_AVATAR;
	CONST OPTION_CAN_CHANGE_PROFILE_COVER     = UserRoleController::USER_CAP_CHANGE_PROFILE_COVER;

	CONST OPTION_CAN_CREATE_USER_GROUPS       = UserRoleController::USER_CAP_CREATE_USER_GROUPS;
	CONST OPTION_CAN_CONTROL_GROUP_PRIVACY    = UserRoleController::USER_CAP_CONTROL_GROUP_PRIVACY;


	CONST OPTION_CAN_DELETE_ACTIVITY_POST     = UserRoleController::USER_CAP_DELETE_ACTIVITY_POST;

	CONST OPTION_CAN_CREATE_NEW_POSTS      = UserRoleController::USER_CAP_CREATE_NEW_POSTS;
	CONST OPTION_CAN_EDIT_OWN_POSTS        = UserRoleController::USER_CAP_EDIT_OWN_POSTS;
	CONST OPTION_CAN_DELETE_OWN_POSTS      = UserRoleController::USER_CAP_DELETE_OWN_POSTS;
	CONST OPTION_CAN_ACCESS_MEDIA_LIBRARY  = UserRoleController::USER_CAP_ACCESS_MEDIA_LIBRARY;


	CONST OPTION_AFTER_REGISTRATION_ACTION       = 'RegAction';

	CONST OPTION_AFTER_REGISTRATION_REDIRECT_URL = 'ARR';
	CONST OPTION_AFTER_LOGIN_REDIRECT_URL        = 'ALR';


	CONST REGISTRATION_ACTION_AUTO_APPROVE  = 1;
	CONST REGISTRATION_ACTION_SEND_EMAIL    = 2;
	CONST REGISTRATION_ACTION_ADMIN_REVIEW  = 3;



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

			self::OPTION_ROLE_TITLE => array(
				'Value'      => __('New Community Role', 'ultra-community'),
				'LabelText'  => __('User Role Name', 'ultra-community'),
				'HelpText'   => __('The description of this user role', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
			),

			self::OPTION_ROLE_PRIORITY => array(
					'Value'      => 0,
					'LabelText'  => __('User Role Hierarchy Priority', 'ultra-community'),
					'HelpText'   => __('Set the role\'s priority position in the hierarchy. The higher the priority, the higher the role will be in the roles hierarchy!', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
			),

			self::OPTION_CAN_CREATE_USER_GROUPS  => array(
				'Value'      => true,
				'LabelText'  => __('Users can create user groups', 'ultra-community'),
				'HelpText'   => __('If this option is enabled, an user that has this role will be able to create groups', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),

			self::OPTION_CAN_CONTROL_GROUP_PRIVACY  => array(
				'Value'      => true,
				'LabelText'  => __('Users can control group privacy', 'ultra-community'),
				'HelpText'   => __('If this option is enabled, users that have this role will be able to control their own groups privacy', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),

			self::OPTION_CAN_EDIT_OWN_PROFILE  => array(
				'Value'      => true,
				'LabelText'  => __('Users can edit their own profile', 'ultra-community'),
				'HelpText'   => __('By disabling this option, the user will not be able to edit the profile', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),

//			self::OPTION_CAN_EDIT_DISPLAY_NAME  => array(
//					'Value'      => null,
//					'LabelText'  => __('Users can edit Display Name', 'ultra-community'),
//					'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
//			),

//			self::OPTION_CAN_DELETE_OWN_PROFILE  => array(
//				'Value'      => true,
//				'LabelText'  => __('Users can delete their own profile', 'ultra-community'),
//				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
//			),

			self::OPTION_CAN_EDIT_USER_PROFILES  => array(
				'Value'      => NULL,
				'LabelText'  => __('Users can edit other user profiles', 'ultra-community'),
				'HelpText'   => __('If this option is enabled, an user that has this role can login and edit other user profiles', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),

//			self::OPTION_CAN_DELETE_USER_PROFILES  => array(
//				'Value'      => NULL,
//				'LabelText'  => __('Users can delete other user profiles', 'ultra-community'),
//				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
//			),

			self::OPTION_CAN_VIEW_OTHER_USER_PROFILES  => array(
				'Value'      => true,
				'LabelText'  => __('Users can view other user profiles', 'ultra-community'),
				'HelpText'   => __('If this option is enabled, the user cannot see any other user profiles except its own profile', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),


//			self::OPTION_CAN_VIEW_JUST_THESE_USER_PROFILES  => array(
//				'Value'      => NULL,
//				'LabelText'  => __('Users can view following roles', 'ultra-community'),
//				'HelpText'   => __('You can select which roles this user role can view. If this option is empty, the users can view any of them', 'ultra-community'),
//				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT
//			),


//			self::OPTION_CAN_SETUP_ACCOUNT_PRIVACY => array(
//				'Value'      => true,
//				'LabelText'  => __('Users can setup account privacy', 'ultra-community'),
//				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
//			),

//			self::OPTION_CAN_VIEW_PRIVATE_PROFILES  => array(
//				'Value'      => NULL,
//				'LabelText'  => __('Users can view private profiles', 'ultra-community'),
//				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
//			),



			self::OPTION_CAN_DELETE_ACTIVITY_POST  => array(
					'Value'      => NULL,
					'LabelText'  => __('Users can delete their activity posts', 'ultra-community'),
					'HelpText'   => __('If this option is enabled, users with this role will be allowed to delete their own activity posts', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),

			self::OPTION_CAN_ACCESS_WP_DASHBOARD => array(
				'Value'      => NULL,
				'LabelText'  => __('Users have WP Dashboard access', 'ultra-community'),
				'HelpText'   => __('If this option is enabled, users with this role will be allowed to access the admin dashboard', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),

			self::OPTION_CAN_VIEW_ADMIN_TOOLBAR => array(
				'Value'      => NULL,
				'LabelText'  => __('Users can view admin toolbar', 'ultra-community'),
				'HelpText'   => __('Controls whether or not users with this role can view the admin toolbar', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),


			self::OPTION_CAN_CHANGE_PROFILE_AVATAR => array(
					'Value'      => true,
					'LabelText'  => __('Users can change avatar picture', 'ultra-community'),
					'HelpText'   => __('Controls whether or not users with this role can change their profile avatar picture', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),

			self::OPTION_CAN_CHANGE_PROFILE_COVER => array(
					'Value'      => true,
					'LabelText'  => __('Users can change cover picture', 'ultra-community'),
					'HelpText'   => __('Controls whether or not users with this role can change their profile cover picture', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),

			self::OPTION_CAN_CHANGE_PASSWORD => array(
					'Value'      => true,
					'LabelText'  => __('Users can change password', 'ultra-community'),
					'HelpText'   => __('Controls whether or not users with this role can change their password', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),


			self::OPTION_CAN_DELETE_ACCOUNT => array(
					'Value'      => NULL,
					'LabelText'  => __('Users can delete account', 'ultra-community'),
					'HelpText'   => __('Controls whether or not users with this role can delete their account', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),



			self::OPTION_CAN_CREATE_NEW_POSTS => array(
				'Value'      => NULL,
				'LabelText'  => __('Users can create new blog posts', 'ultra-community'),
				'HelpText'   => __('Controls whether or not users with this role can create new blog posts', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),

			self::OPTION_CAN_EDIT_OWN_POSTS => array(
				'Value'      => NULL,
				'LabelText'  => __('Users can edit their blog posts', 'ultra-community'),
				'HelpText'   => __('Controls whether or not users with this role can edit their published or scheduled for publishing posts', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),

			self::OPTION_CAN_DELETE_OWN_POSTS => array(
				'Value'      => NULL,
				'LabelText'  => __('Users can delete their blog posts', 'ultra-community'),
				'HelpText'   => __('Controls whether or not users with this role can delete their already published blog posts', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),

			self::OPTION_CAN_ACCESS_MEDIA_LIBRARY => array(
				'Value'      => NULL,
				'LabelText'  => __('Users can access media library', 'ultra-community'),
				'HelpText'   => __('Controls whether or not users with this role can access media library to upload their own files', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),


			self::OPTION_AFTER_REGISTRATION_ACTION => array(
				'Value'      => self::REGISTRATION_ACTION_AUTO_APPROVE,
				'LabelText'  => __('After user registration', 'ultra-community'),
				'HelpText'   => __('Allows you to control what happens to users with this role when they register on your site', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT
			),

			self::OPTION_AFTER_REGISTRATION_REDIRECT_URL => array(
				'Value'      => NULL,
				'LabelText'  => __('After registration redirect URL', 'ultra-community'),
				'HelpText'   => __('If the registration is set to “Automatically Approve”, the users with this role will be redirected to this URL after they successfully registered', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
			),

			self::OPTION_AFTER_LOGIN_REDIRECT_URL => array(
				'Value'      => NULL,
				'LabelText'  => __('After login redirect URL', 'ultra-community'),
				'HelpText'   => __('Allows you to specify the URL where the users will be redirected after login. If this option is left blank, the users will be redirected to their user profile page', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
			),

//			self::OPTION_IS_DEFAULT_ADMIN_ROLE => array(
//					'Value'      => NULL,
//					'LabelText'  => null,
//					'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_HIDDEN
//			),

//			self::OPTION_IS_DEFAULT_USER_ROLE => array(
//					'Value'      => NULL,
//					'LabelText'  => null,
//					'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_HIDDEN
//			),

		);


		if(!ModulesController::isModuleRegistered(ModulesController::MODULE_POST_SUBMISSIONS))
		{
			foreach (array(self::OPTION_CAN_CREATE_NEW_POSTS, self::OPTION_CAN_EDIT_OWN_POSTS, self::OPTION_CAN_DELETE_OWN_POSTS, self::OPTION_CAN_ACCESS_MEDIA_LIBRARY) as $postSubmissionsOption){
				unset($arrDefaultSettingOptions[$postSubmissionsOption]);
			}
		}


		return $arrDefaultSettingOptions;

	}

	public  function validateModuleSettingsFields($arrSettingOptions)
	{

		$arrSettingOptions = $this->sanitizeModuleSettings($arrSettingOptions);


		if( !isset($arrSettingOptions[self::OPTION_ROLE_PRIORITY]) || !MchValidator::isInteger($arrSettingOptions[self::OPTION_ROLE_PRIORITY]) || $arrSettingOptions[self::OPTION_ROLE_PRIORITY] < 0 || $arrSettingOptions[self::OPTION_ROLE_PRIORITY] > 100)
		{
			$this->registerErrorMessage(__('The Role Hierarchy Priority should be an integer between 0 and 100', 'ultra-community'));
			return $this->getAllSavedOptions();
		}


		if(!empty($arrSettingOptions[self::OPTION_AFTER_LOGIN_REDIRECT_URL])){
			if(!MchValidator::isURL($arrSettingOptions[self::OPTION_AFTER_LOGIN_REDIRECT_URL])){
				$this->registerErrorMessage(__('Please provide a valid URL for login redirect', 'ultra-community'));
				return $this->getAllSavedOptions();
			}
		}

		if(!empty($arrSettingOptions[self::OPTION_AFTER_REGISTRATION_REDIRECT_URL])){
			if(!MchValidator::isURL($arrSettingOptions[self::OPTION_AFTER_REGISTRATION_REDIRECT_URL])){
				$this->registerErrorMessage(__('Please provide a valid URL for registration redirect', 'ultra-community'));
				return $this->getAllSavedOptions();
			}
		}

		$userRoleSlug = $this->getOption(self::OPTION_ROLE_SLUG);
		if(MchValidator::isNullOrEmpty($userRoleSlug)){
			$userRoleSlug = UserRoleController::generateUserRoleKeyFromDescription($this->getCustomPostTypeId());
		}


		if(empty($userRoleSlug)){
			$this->registerErrorMessage(__('There was an error while trying to save these settings', 'ultra-community'));
			return $this->getAllSavedOptions();
		}

		$arrSettingOptions[self::OPTION_ROLE_SLUG] = $userRoleSlug;

		$customPostType = (null !== $this->getCustomPostType()) ? $this->getCustomPostType() : PostTypeController::getPostTypeInstance(PostTypeController::POST_TYPE_USER_ROLE);

		if(empty($arrSettingOptions[self::OPTION_ROLE_TITLE]))
		{
			$arrSettingOptions[self::OPTION_ROLE_TITLE] = WpUserRepository::getUserRoleDescription($userRoleSlug);
		}

		if(empty($arrSettingOptions[self::OPTION_ROLE_TITLE]))
		{
			$this->registerErrorMessage(__('Please provide a Name for this user role!', 'ultra-community'));
			return $this->getAllSavedOptions();
		}

		$customPostType->PostTitle = $arrSettingOptions[self::OPTION_ROLE_TITLE];

		PostTypeController::publishPostType($customPostType);

		$this->setCustomPostType($customPostType);

		$this->registerSuccessMessage(__('Your changes were successfully saved!', 'ultra-community'));


		return $arrSettingOptions;
	}

	public function canBeDeleted()
	{
		if( defined( 'WP_UNINSTALL_PLUGIN' ) && current_user_can('delete_plugins') )
			return true;
		
		return UserRoleController::isUltraCommUserRole($this->getOption(self::OPTION_ROLE_SLUG));

		//return !UserRoleController::isBuiltInUserRole();
	}


	public function deleteAllSettingOptions($forceBlogOption = true)
	{
		if(!$this->canBeDeleted()) {
			throw new UltraCommException( __( 'This is user role cannot be deleted through UltraCommunity !', 'ultra-community' ) );
		}

		return parent::deleteAllSettingOptions($forceBlogOption);
	}



	public function renderModuleSettingsField(array $arrSettingsField)
	{

		$fieldKey   = key( $arrSettingsField );
		$fieldValue = $this->getOption( $fieldKey );
		$selfInstance = $this;
		MchWpUtils::addFilterHook($this->getFieldAttributesFilterName($fieldKey), function( $arrFieldAttributes ) use ( $fieldValue, $fieldKey, $selfInstance) {

			if($fieldKey === UserRoleAdminModule::OPTION_ROLE_TITLE){
				!empty($fieldValue) ?: $arrFieldAttributes['value']   = UserRoleAdminModule::getInstance()->getDefaultOptionValue(UserRoleAdminModule::OPTION_ROLE_TITLE);
			}

			if($fieldKey === UserRoleAdminModule::OPTION_AFTER_REGISTRATION_ACTION)
			{
				$arrFieldAttributes['class'][] = 'uc-select2';
				$arrFieldAttributes['value']   = $fieldValue;
				$arrFieldAttributes['options'] = array(

					UserRoleAdminModule::REGISTRATION_ACTION_AUTO_APPROVE => __('Automatically Approve', 'ultra-community'),
					UserRoleAdminModule::REGISTRATION_ACTION_SEND_EMAIL   => __('Send Activation Email', 'ultra-community'),
					UserRoleAdminModule::REGISTRATION_ACTION_ADMIN_REVIEW => __('Hold for Admin Review', 'ultra-community')

				);

				return $arrFieldAttributes;

			}

			if($fieldKey === UserRoleAdminModule::OPTION_ROLE_TITLE)
			{
				$arrFieldAttributes['class'][] = 'uc-users-role-title';

				if(!UserRoleController::isUltraCommUserRole($this->getOption(self::OPTION_ROLE_SLUG)))
				{
					$arrFieldAttributes['disabled'] = 'disabled';
				}

				return $arrFieldAttributes;
			}

			return $arrFieldAttributes;

		});

		return parent::renderModuleSettingsField($arrSettingsField);

	}


	public  function renderModuleSettingsSectionHeader(array $arrSectionInfo)
	{


		MchWpUtils::addFilterHook(self::FILTER_FIELD_SETTINGS_TABLE_ROW_OUTPUT, function ($fieldTableRowOutput, $fieldKey){

			if($fieldKey === UserRoleAdminModule::OPTION_AFTER_REGISTRATION_ACTION)
			{
				return $this->addSettingsSectionDivider(esc_html__('User Role Actions', 'ultra-community'), $fieldTableRowOutput, true);
			}

			if($fieldKey === UserRoleAdminModule::OPTION_ROLE_PRIORITY)
			{
				return  $fieldTableRowOutput . BaseAdminModule::getFieldSettingsSectionDivider(esc_html__('User Role Capabilities', 'ultra-community'));
			}

			return 	$fieldTableRowOutput;
		}, 10, 2);



		$title = $this->getOption(self::OPTION_ROLE_TITLE) . ' - ' . __('User Role Settings', 'ultra-community');

		$title = esc_html($title);

		$customPostId   = $this->getCustomPostTypeId();
		$customPostType = $this->getCustomPostType()->PostType;
		$moduleKey      = $this->getSettingKey();


		$deleteOutput = <<<DeleteOutput
				<li>
					<button id = "btn-uc-user-role-action-delete-user-role-$moduleKey" data-modulekey = "$moduleKey" data-custompostid = "$customPostId" data-customposttype = "$customPostType" class = "uc-button uc-button-danger"><i class="fa fa-trash"></i> Delete</button>
				</li>
DeleteOutput;


		if(!$this->canBeDeleted())
		{
			$deleteOutput = null;
		}

		$headerOutput = <<<SH
			<div class = "uc-settings-section-header uc-clear">
				<h3>$title</h3>
				<ul class = "uc-settings-module-actions">

					<li>
						<button id = "btn-uc-user-role-action-add-new-user-role-$moduleKey" data-modulekey = "$moduleKey" data-custompostid = "$customPostId" data-customposttype = "$customPostType" class = "uc-button uc-button-primary"><i class="fa fa-plus"></i> Add New</button>
					</li>
					$deleteOutput
				</ul>
			</div>
SH;



		echo $headerOutput;
	}


}