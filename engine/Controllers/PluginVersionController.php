<?php
/**
 * Copyright (c) 2019 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Controllers;

use UltraCommunity\Entities\UserMetaEntity;
use UltraCommunity\Entities\UserPrivacyEntity;
use UltraCommunity\MchLib\Plugin\MchPluginUpdater;
use UltraCommunity\MchLib\Utils\MchHttpRequest;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\Repository\WpPostRepository;
use UltraCommunity\MchLib\WordPress\Repository\WpUserRepository;
use UltraCommunity\Modules\Appearance\Directories\DirectoriesAppearanceAdminModule;
use UltraCommunity\Modules\Appearance\General\GeneralAppearanceAdminModule;
use UltraCommunity\Modules\Appearance\GroupProfile\GroupProfileAppearanceAdminModule;
use UltraCommunity\Modules\Appearance\UserProfile\UserProfileAppearanceAdminModule;
use UltraCommunity\Modules\Forms\BaseForm\BaseFormAdminModule;
use UltraCommunity\Modules\Forms\ForgotPasswordForm\ForgotPasswordFormAdminModule;
use UltraCommunity\Modules\Forms\FormFields\CountryField;
use UltraCommunity\Modules\Forms\FormFields\ProfileSectionField;
use UltraCommunity\Modules\Forms\FormFields\SocialNetworkUrlField;
use UltraCommunity\Modules\Forms\FormFields\TextAreaField;
use UltraCommunity\Modules\Forms\FormFields\TextField;
use UltraCommunity\Modules\Forms\FormFields\UserBioField;
use UltraCommunity\Modules\Forms\FormFields\UserEmailField;
use UltraCommunity\Modules\Forms\FormFields\UserFirstNameField;
use UltraCommunity\Modules\Forms\FormFields\UserLastNameField;
use UltraCommunity\Modules\Forms\FormFields\UserNameField;
use UltraCommunity\Modules\Forms\FormFields\UserNameOrEmailField;
use UltraCommunity\Modules\Forms\FormFields\UserPasswordField;
use UltraCommunity\Modules\Forms\FormFields\UserRegistrationDateField;
use UltraCommunity\Modules\Forms\FormFields\UserWebUrlField;
use UltraCommunity\Modules\Forms\LoginForm\LoginFormAdminModule;
use UltraCommunity\Modules\Forms\RegisterForm\RegisterFormAdminModule;
use UltraCommunity\Modules\Forms\UserProfileForm\UserProfileFormAdminModule;
use UltraCommunity\Modules\GeneralSettings\Emails\EmailsSettingsAdminModule;
use UltraCommunity\Modules\GeneralSettings\FrontPage\FrontPageSettingsAdminModule;
use UltraCommunity\Modules\GeneralSettings\Licenses\LicensesPublicModule;
use UltraCommunity\Modules\GeneralSettings\Plugin\PluginSettingsAdminModule;
use UltraCommunity\Modules\GeneralSettings\Plugin\PluginSettingsPublicModule;
use UltraCommunity\Modules\GeneralSettings\User\UserSettingsAdminModule;
use UltraCommunity\Modules\GroupsDirectory\GroupsDirectoryAdminModule;
use UltraCommunity\Modules\MembersDirectory\MembersDirectoryAdminModule;
use UltraCommunity\Modules\UserRole\UserRoleAdminModule;
use UltraCommunity\Repository\ActivityRepository;
use UltraCommunity\Repository\BaseRepository;
use UltraCommunity\Repository\GroupRepository;
use UltraCommunity\Repository\UserRelationsRepository;
use UltraCommunity\Repository\UserRepository;

use UltraCommunity\Tasks\UserRolesMapperTask;
use UltraCommunity\UltraCommException;
use UltraCommunity\UltraCommHelper;

final class PluginVersionController
{


	public static function handleVersionChanges()
	{
		$savedPluginVersion = PluginSettingsPublicModule::getInstance()->getOption(PluginSettingsAdminModule::PLUGIN_ACTIVE_VERSION);
		
		if( 0 === version_compare($savedPluginVersion, \UltraCommunity::PLUGIN_VERSION) )
			return;

		if ( -1  === version_compare($savedPluginVersion, '2.0') )
		{
			GroupRepository::createGroupUsersTable();
			ActivityRepository::createActivityTable();
			UserRelationsRepository::createUserRelationsTable();
			ActivityRepository::createActivityRepostsTable();
			
			self::publishRegisteredCustomPostTypes();
			self::setDefaultUserSettings();
			self::publishDefaultFrontPages();
			self::setFormsDefaultUserRole();

			self::setEmailsDefaultValues();
			self::setAppearanceDefaultSettings();

			self::setDirectoriesAssignedPages();

			self::updateAdminUsersStatus();
			self::setDefaultFormsFields();

		}


		if ( -1  === version_compare($savedPluginVersion, '2.0.7') )
		{
			foreach (PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_PROFILE_FORM) as $profileFormPostType)
			{
				if(null === ($profileFormAdminInstance = PostTypeController::getAssociatedAdminModuleInstance($profileFormPostType)))
					continue;

				$assignedUserRolePostId = $profileFormAdminInstance->getOption(UserProfileFormAdminModule::OPTION_USER_ROLE_CUSTOM_POST_ID);
				empty($assignedUserRolePostId) ?: $profileFormAdminInstance->saveOption(UserProfileFormAdminModule::OPTION_ASSIGNED_USER_ROLES, array($assignedUserRolePostId));
			}

		}

		if ( -1  === version_compare($savedPluginVersion, '2.0.10') )
		{
			$arrGroupProfileSections = (array)GroupProfileAppearanceAdminModule::getInstance()->getOption(GroupProfileAppearanceAdminModule::OPTION_GROUP_PROFILE_SECTIONS);
			if(false !== ($homeSectionKey = array_search('home', $arrGroupProfileSections))){
				$arrGroupProfileSections[$homeSectionKey] = GroupProfileAppearanceAdminModule::PROFILE_SECTION_ACTIVITY;
			}
			$arrGroupProfileSections = array_unique($arrGroupProfileSections);

			empty($arrGroupProfileSections) ?: GroupProfileAppearanceAdminModule::getInstance()->saveOption(GroupProfileAppearanceAdminModule::OPTION_GROUP_PROFILE_SECTIONS, $arrGroupProfileSections);

		}

		if ( -1  === version_compare($savedPluginVersion, '2.0.16') )
		{
			FrontPageSettingsAdminModule::getInstance()->saveOption(FrontPageSettingsAdminModule::USE_GLOBAL_PAGE_TEMPLATE, TRUE);
		}

		if ( -1  === version_compare($savedPluginVersion, '2.0.26') )
		{
			ActivityRepository::createActivityRepostsTable();
		}


		BaseRepository::handlePluginVersionChanges($savedPluginVersion);
		
		PluginSettingsAdminModule::getInstance()->saveOption(PluginSettingsAdminModule::PLUGIN_ACTIVE_VERSION, \UltraCommunity::PLUGIN_VERSION);
		PluginSettingsAdminModule::getInstance()->saveOption(PluginSettingsAdminModule::SETTINGS_LAST_UPDATED, MchHttpRequest::getServerRequestTime());

	}


	private static function setDirectoriesAssignedPages()
	{
		$membersDirectoryPageId   = FrontPageSettingsAdminModule::getInstance()->getOption(FrontPageSettingsAdminModule::MEMBERS_DIRECTORY_PAGE_ID);
		if(!empty($membersDirectoryPageId))
		{
			foreach(PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_MEMBERS_DIRECTORY, true) as $directoryPostType)
			{
				if(null === ($directoryPublicModuleInstance =  PostTypeController::getAssociatedAdminModuleInstance($directoryPostType)) || !$directoryPublicModuleInstance->getOption(MembersDirectoryAdminModule::OPTION_IS_DEFAULT_MEMBERS_DIRECTORY))
					continue;

				$directoryPublicModuleInstance->saveOption(MembersDirectoryAdminModule::OPTION_ASSIGNED_PAGE_ID, (int)$membersDirectoryPageId);

				$directoryPublicModuleInstance->saveOption(MembersDirectoryAdminModule::OPTION_USER_CARD_TAGLINE_FIELDS, array(MchUtils::getClassShortNameFromNameSpace(new UserNameField())));
				$directoryPublicModuleInstance->saveOption(MembersDirectoryAdminModule::OPTION_USER_CARD_BELOW_TAGLINE_FIELDS, array(MchUtils::getClassShortNameFromNameSpace(new UserRegistrationDateField())));
				
				break;
			}
		}
	}

	private static function updateAdminUsersStatus()
	{
		foreach((array)get_super_admins() as $adminUserName)
		{
			if(null === ($userEntity = UserRepository::getUserEntityBy($adminUserName)))
				continue;

			$userEntity->UserMetaEntity->UserStatus = UserMetaEntity::USER_STATUS_APPROVED;

			UserController::saveUserInfo($userEntity);
		}

		foreach(WpUserRepository::getUsersByRole('administrator') as $adminUserName)
		{
			if(null === ($userEntity = UserRepository::getUserEntityBy($adminUserName)))
				continue;

			$userEntity->UserMetaEntity->UserStatus = UserMetaEntity::USER_STATUS_APPROVED;

			UserController::saveUserInfo($userEntity);
		}

	}


	private static function setAppearanceDefaultSettings()
	{
		foreach(GeneralAppearanceAdminModule::getInstance()->getDefaultOptionsValues() as $optionName => $defaultOptionsValue)
		{
			if(!isset($defaultOptionsValue))
				continue;
			if(!MchUtils::isNullOrEmpty(GeneralAppearanceAdminModule::getInstance()->getOption($optionName)))
				continue;

			GeneralAppearanceAdminModule::getInstance()->saveOption($optionName, $defaultOptionsValue);
		}


		foreach(UserProfileAppearanceAdminModule::getInstance()->getDefaultOptionsValues() as $optionName => $defaultOptionsValue){
			if(!isset($defaultOptionsValue))
				continue;
			if(!MchUtils::isNullOrEmpty(UserProfileAppearanceAdminModule::getInstance()->getOption($optionName)))
				continue;
			
			UserProfileAppearanceAdminModule::getInstance()->saveOption($optionName, $defaultOptionsValue);
		}

		foreach(GroupProfileAppearanceAdminModule::getInstance()->getDefaultOptionsValues() as $optionName => $defaultOptionsValue){
			if(!isset($defaultOptionsValue))
				continue;
			if(!MchUtils::isNullOrEmpty(GroupProfileAppearanceAdminModule::getInstance()->getOption($optionName)))
				continue;
			GroupProfileAppearanceAdminModule::getInstance()->saveOption($optionName, $defaultOptionsValue);
		}

		foreach(DirectoriesAppearanceAdminModule::getInstance()->getDefaultOptionsValues() as $optionName => $defaultOptionsValue){
			if(!isset($defaultOptionsValue))
				continue;

			if(!MchUtils::isNullOrEmpty(DirectoriesAppearanceAdminModule::getInstance()->getOption($optionName)))
				continue;

			DirectoriesAppearanceAdminModule::getInstance()->saveOption($optionName, $defaultOptionsValue);
		}


		foreach(UserProfileAppearanceAdminModule::getInstance()->getDefaultOptionsValues() as $optionName => $defaultOptionsValue){
			if(!isset($defaultOptionsValue))
				continue;

			if(!MchUtils::isNullOrEmpty(UserProfileAppearanceAdminModule::getInstance()->getOption($optionName)))
				continue;

			UserProfileAppearanceAdminModule::getInstance()->saveOption($optionName, $defaultOptionsValue);
		}

		foreach(GroupProfileAppearanceAdminModule::getInstance()->getDefaultOptionsValues() as $optionName => $defaultOptionsValue){
			if(!isset($defaultOptionsValue))
				continue;

			if(!MchUtils::isNullOrEmpty(GroupProfileAppearanceAdminModule::getInstance()->getOption($optionName)))
				continue;

			GroupProfileAppearanceAdminModule::getInstance()->saveOption($optionName, $defaultOptionsValue);
		}


	}

	private static function setEmailsDefaultValues()
	{
		foreach(EmailsSettingsAdminModule::getInstance()->getDefaultOptionsValues() as $optionName => $defaultOptionsValue){
			if(!isset($defaultOptionsValue) || !is_scalar($defaultOptionsValue))
				continue;
			if(!MchUtils::isNullOrEmpty(EmailsSettingsAdminModule::getInstance()->getOption($optionName)))
				continue;
			EmailsSettingsAdminModule::getInstance()->saveOption($optionName, $defaultOptionsValue);
		}

	}


	private static function setFormsDefaultUserRole()
	{
		PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_ROLE, true); // fresh cache

		if( null === $userRolePublicModuleInstance = PostTypeController::getAssociatedPublicModuleInstance(UserRoleController::getDefaultUserRolePostType()) ) {
			return;
		}

		foreach(PostTypeController::getAllPostTypeKeys() as $postTypeKey)
		{
			if($postTypeKey !== PostTypeController::POST_TYPE_REGISTER_FORM){
				continue;
			}

			foreach (PostTypeController::getPublishedPosts($postTypeKey, true) as $publishedPostType) {

				$adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($publishedPostType);
				if (!$adminModuleInstance instanceof BaseFormAdminModule)
					continue;

				if ($adminModuleInstance->getOption(BaseFormAdminModule::OPTION_IS_DEFAULT_REGISTRATION_FORM)) {
					//if( ! $adminModuleInstance->getOption(BaseFormAdminModule::OPTION_USER_ROLE_CUSTOM_POST_ID) ){
					$adminModuleInstance->saveOption(BaseFormAdminModule::OPTION_USER_ROLE_CUSTOM_POST_ID, $userRolePublicModuleInstance->getCustomPostTypeId());
					continue;
					//}
				}
			}
		}
	}


	private static function publishDefaultFrontPages()
	{
		FrontPageSettingsAdminModule::getInstance()->saveOption(FrontPageSettingsAdminModule::USE_GLOBAL_PAGE_TEMPLATE, true);

		foreach(PostTypeController::getAllPostTypeKeys() as $postTypeKey)
		{
			if($postTypeKey === PostTypeController::POST_TYPE_USER_ROLE)
				continue;

			if($postTypeKey === PostTypeController::POST_TYPE_ACTIVITY)
				continue;

			foreach ((array)PostTypeController::getPublishedPosts($postTypeKey, true) as $customPostType)
			{
				$adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($customPostType);

				if($adminModuleInstance instanceof RegisterFormAdminModule)
				{

					$pageId   = $adminModuleInstance->getOption(RegisterFormAdminModule::OPTION_ASSIGNED_PAGE_ID);
					$pageSlug = $adminModuleInstance->getOption(RegisterFormAdminModule::OPTION_ASSIGNED_PAGE_SLUG);

					if($pageId && $pageSlug)
						continue;

					if(empty($pageId)){
						$pageId = FrontPageController::publishPage(FrontPageController::PAGE_REGISTRATION, $adminModuleInstance);
					}

					if( ! ($wpPost = WpPostRepository::findByPostId($pageId)) )
						continue;

					$adminModuleInstance->saveOption(RegisterFormAdminModule::OPTION_ASSIGNED_PAGE_ID, $pageId);
					$adminModuleInstance->saveOption(RegisterFormAdminModule::OPTION_ASSIGNED_PAGE_SLUG, $wpPost->post_name);

					FrontPageSettingsAdminModule::getInstance()->saveOption(FrontPageSettingsAdminModule::REGISTER_PAGE_ID, $pageId);

					continue;

				}

				if($adminModuleInstance instanceof LoginFormAdminModule)
				{
					$pageId   = FrontPageSettingsAdminModule::getInstance()->getOption(FrontPageSettingsAdminModule::LOGIN_PAGE_ID);

					if($pageId)
						continue;

					if(empty($pageId)){
						$pageId = FrontPageController::publishPage(FrontPageController::PAGE_LOGIN, $adminModuleInstance);
					}

					if( ! ($wpPost = WpPostRepository::findByPostId($pageId)) )
						continue;

					FrontPageSettingsAdminModule::getInstance()->saveOption(FrontPageSettingsAdminModule::LOGIN_PAGE_ID, $pageId);
//					FrontPageSettingsAdminModule::getInstance()->saveOption(FrontPageSettingsAdminModule::LOGIN_PAGE_SLUG, $wpPost->post_name);

					continue;
				}

				if($adminModuleInstance instanceof UserProfileFormAdminModule)
				{
					$pageId   = FrontPageSettingsAdminModule::getInstance()->getOption(FrontPageSettingsAdminModule::USER_PROFILE_PAGE_ID);
					//$pageSlug = FrontPageSettingsAdminModule::getInstance()->getOption(FrontPageSettingsAdminModule::USER_PROFILE_PAGE_SLUG);

					if($pageId)
						continue;

					if(empty($pageId)){
						$pageId = FrontPageController::publishPage(FrontPageController::PAGE_USER_PROFILE, $adminModuleInstance);
					}

					if( ! ($wpPost = WpPostRepository::findByPostId($pageId)) )
						continue;

					FrontPageSettingsAdminModule::getInstance()->saveOption(FrontPageSettingsAdminModule::USER_PROFILE_PAGE_ID, $pageId);
					//FrontPageSettingsAdminModule::getInstance()->saveOption(FrontPageSettingsAdminModule::USER_PROFILE_PAGE_SLUG, $wpPost->post_name);

					continue;
				}



				if($adminModuleInstance instanceof ForgotPasswordFormAdminModule)
				{
					$pageId   = FrontPageSettingsAdminModule::getInstance()->getOption(FrontPageSettingsAdminModule::FORGOT_PASSWORD_PAGE_ID);
					//$pageSlug = FrontPageSettingsAdminModule::getInstance()->getOption(FrontPageSettingsAdminModule::FORGOT_PASSWORD_PAGE_SLUG);

					if($pageId)
						continue;

					if(empty($pageId)){
						$pageId = FrontPageController::publishPage(FrontPageController::PAGE_FORGOT_PASSWORD, $adminModuleInstance);
					}

					if( ! ($wpPost = WpPostRepository::findByPostId($pageId)) )
						continue;

					FrontPageSettingsAdminModule::getInstance()->saveOption(FrontPageSettingsAdminModule::FORGOT_PASSWORD_PAGE_ID, $pageId);
					//FrontPageSettingsAdminModule::getInstance()->saveOption(FrontPageSettingsAdminModule::FORGOT_PASSWORD_PAGE_SLUG, $wpPost->post_name);

					continue;

				}

				if($adminModuleInstance instanceof MembersDirectoryAdminModule)
				{
					$pageId   = FrontPageSettingsAdminModule::getInstance()->getOption(FrontPageSettingsAdminModule::MEMBERS_DIRECTORY_PAGE_ID);
					if($pageId &&  ($wpPost = WpPostRepository::findByPostId($pageId))) {
						continue;
					}

					$pageId = FrontPageController::publishPage(FrontPageController::PAGE_MEMBERS_DIRECTORY, $adminModuleInstance);

					if( ! ($wpPost = WpPostRepository::findByPostId($pageId)) )
						continue;

					FrontPageSettingsAdminModule::getInstance()->saveOption(FrontPageSettingsAdminModule::MEMBERS_DIRECTORY_PAGE_ID, $pageId);
					continue;

				}

				if($adminModuleInstance instanceof GroupsDirectoryAdminModule)
				{
					$pageId   = FrontPageSettingsAdminModule::getInstance()->getOption(FrontPageSettingsAdminModule::GROUPS_DIRECTORY_PAGE_ID);
					if($pageId &&  ($wpPost = WpPostRepository::findByPostId($pageId))) {
						continue;
					}

					$pageId = FrontPageController::publishPage(FrontPageController::PAGE_GROUPS_DIRECTORY, $adminModuleInstance);

					if( ! ($wpPost = WpPostRepository::findByPostId($pageId)) )
						continue;

					FrontPageSettingsAdminModule::getInstance()->saveOption(FrontPageSettingsAdminModule::GROUPS_DIRECTORY_PAGE_ID, $pageId);

					continue;

				}

			}

		}

		PluginSettingsAdminModule::getInstance()->saveOption(PluginSettingsAdminModule::SETTINGS_LAST_UPDATED, MchHttpRequest::getServerRequestTime());

	}

	private static function setDefaultUserSettings()
	{
		if(!ModulesController::isModuleRegistered(ModulesController::MODULE_USER_SETTINGS))
			return;

		if(! UserSettingsAdminModule::getInstance()->getOption(UserSettingsAdminModule::OPTION_DEFAULT_USER_ROLE)) {

			foreach (PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_ROLE, true) as $publishedPost) {

				if (MchUtils::isNullOrEmpty($adminModulesInstance = PostTypeController::getAssociatedAdminModuleInstance($publishedPost)))
					continue;

				if (!UserRoleController::isDefaultMemberRole($adminModulesInstance->getOption(UserRoleAdminModule::OPTION_ROLE_SLUG)))
					continue;

				UserSettingsAdminModule::getInstance()->saveOption(UserSettingsAdminModule::OPTION_DEFAULT_USER_ROLE, $publishedPost->PostId);
				break;
			}
		}

		if(! UserSettingsAdminModule::getInstance()->getOption(UserSettingsAdminModule::OPTION_DISPLAY_NAME))
		{
			UserSettingsAdminModule::getInstance()->saveOption(UserSettingsAdminModule::OPTION_DISPLAY_NAME, UserSettingsAdminModule::USER_DISPLAY_NAME_FIRST_LAST_NAME);
			UserSettingsAdminModule::getInstance()->saveOption(UserSettingsAdminModule::OPTION_REDIRECT_COMMENT_USER_URL, true);
			UserSettingsAdminModule::getInstance()->saveOption(UserSettingsAdminModule::OPTION_REDIRECT_AUTHOR_USER_URL, true);

		}



	}


	private static function publishRegisteredCustomPostTypes()
	{

		UserRoleController::handleUserRoles();


		foreach(PostTypeController::getAllPostTypeKeys() as $postTypeKey)
		{

			if(in_array($postTypeKey, array(
					PostTypeController::POST_TYPE_ACTIVITY, PostTypeController::POST_TYPE_GROUP
			))){
				continue;
			}

			$customPostType = PostTypeController::getPostTypeInstance($postTypeKey);

			if($postTypeKey === PostTypeController::POST_TYPE_MEMBERS_DIRECTORY && ModulesController::isModuleRegistered(ModulesController::MODULE_MEMBERS_DIRECTORY) )
			{
				$customPostType->PostTitle = 'UltraComm Members Directory';

				$shouldPublish = true;
				foreach(PostTypeController::getPublishedPosts($postTypeKey, true) as $publishedPost){
					if(null !== ($adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($publishedPost))){
						if($adminModuleInstance->getOption(MembersDirectoryAdminModule::OPTION_IS_DEFAULT_MEMBERS_DIRECTORY)){
							$shouldPublish = false; break;
						}
					}
				}
				if($shouldPublish) {
					$customPostType->PostId = PostTypeController::publishPostType( $customPostType );
					$adminModuleInstance = MembersDirectoryAdminModule::getInstance(true);
					$adminModuleInstance->setCustomPostType($customPostType);
					$adminModuleInstance->saveDefaultOptions(false);
					$adminModuleInstance->saveOption(MembersDirectoryAdminModule::OPTION_IS_DEFAULT_MEMBERS_DIRECTORY, true);
					$adminModuleInstance->saveOption(MembersDirectoryAdminModule::OPTION_DIRECTORY_TITLE, __('Members Directory', 'ultra-community'));

				}

				continue;

			}

			if($postTypeKey === PostTypeController::POST_TYPE_GROUPS_DIRECTORY && ModulesController::isModuleRegistered(ModulesController::MODULE_GROUPS_DIRECTORY) )
			{
				$customPostType->PostTitle = 'UltraComm Groups Directory';
				$shouldPublish = true;
				foreach(PostTypeController::getPublishedPosts($postTypeKey, true) as $publishedPost){
					if(null !== ($adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($publishedPost))){
						if($adminModuleInstance->getOption(GroupsDirectoryAdminModule::OPTION_IS_DEFAULT_GROUPS_DIRECTORY)){
							$shouldPublish = false; break;
						}
					}
				}
				if($shouldPublish)
				{
					$customPostType->PostId = PostTypeController::publishPostType( $customPostType );

					$adminModuleInstance = GroupsDirectoryAdminModule::getInstance(true);
					$adminModuleInstance->setCustomPostType($customPostType);
					$adminModuleInstance->saveDefaultOptions(false);
					$adminModuleInstance->saveOption(GroupsDirectoryAdminModule::OPTION_IS_DEFAULT_GROUPS_DIRECTORY, true);
					$adminModuleInstance->saveOption(GroupsDirectoryAdminModule::OPTION_DIRECTORY_TITLE, __('Groups Directory', 'ultra-community'));
					

				}

				continue;

			}

			if($postTypeKey === PostTypeController::POST_TYPE_FORGOT_PASSWORD_FORM && ModulesController::isModuleRegistered(ModulesController::MODULE_FORGOT_PASSWORD_FORM) )
			{
				$customPostType->PostTitle = 'UltraComm Forgot Password Form';

				$shouldPublish = true;
				foreach(PostTypeController::getPublishedPosts($postTypeKey, true) as $publishedPost){
					if(null !== ($adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($publishedPost))){
						if($adminModuleInstance->getOption(ForgotPasswordFormAdminModule::OPTION_IS_DEFAULT_FORGOT_PASSWORD_FORM)){
							$shouldPublish = false; break;
						}
					}
				}
				if($shouldPublish) {
					$customPostType->PostId = PostTypeController::publishPostType( $customPostType );
					$adminModuleInstance = ForgotPasswordFormAdminModule::getInstance(true);
					$adminModuleInstance->setCustomPostType($customPostType);
					$adminModuleInstance->saveDefaultOptions();
					$adminModuleInstance->saveOption(ForgotPasswordFormAdminModule::OPTION_IS_DEFAULT_FORGOT_PASSWORD_FORM, true);
					$adminModuleInstance->saveOption(ForgotPasswordFormAdminModule::OPTION_FORM_TITLE, __('Default Forgot Password', 'ultra-community'));

				}

				continue;

			}


			if($postTypeKey === PostTypeController::POST_TYPE_LOGIN_FORM && ModulesController::isModuleRegistered(ModulesController::MODULE_LOGIN_FORM) )
			{
				$customPostType->PostTitle = 'UltraComm Login Form';

				$shouldPublish = true;
				foreach(PostTypeController::getPublishedPosts($postTypeKey, true) as $publishedPost){
					if(null !== ($adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($publishedPost))){
						if($adminModuleInstance->getOption(LoginFormAdminModule::OPTION_IS_DEFAULT_LOGIN_FORM)){
							$shouldPublish = false; break;
						}
					}
				}
				if($shouldPublish) {
					$customPostType->PostId = PostTypeController::publishPostType( $customPostType );
					$adminModuleInstance = LoginFormAdminModule::getInstance(true);
					$adminModuleInstance->setCustomPostType($customPostType);
					$adminModuleInstance->saveDefaultOptions();
					$adminModuleInstance->saveOption(LoginFormAdminModule::OPTION_IS_DEFAULT_LOGIN_FORM, true);
					$adminModuleInstance->saveOption(LoginFormAdminModule::OPTION_FORM_TITLE, __('Default Login', 'ultra-community'));

				}

				continue;
			}

			if($postTypeKey === PostTypeController::POST_TYPE_REGISTER_FORM )
			{
				$customPostType->PostTitle = 'UltraComm Register Form';

				$shouldPublish = true;
				foreach(PostTypeController::getPublishedPosts($postTypeKey, true) as $publishedPost){
					if(null !== ($adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($publishedPost))){
						if($adminModuleInstance->getOption(RegisterFormAdminModule::OPTION_IS_DEFAULT_REGISTRATION_FORM)){
							$shouldPublish = false; break;
						}
					}
				}

				if($shouldPublish)
				{
					$customPostType->PostId = PostTypeController::publishPostType( $customPostType );
					$adminModuleInstance = RegisterFormAdminModule::getInstance(true);
					$adminModuleInstance->setCustomPostType($customPostType);
					$adminModuleInstance->saveDefaultOptions();
					$adminModuleInstance->saveOption(RegisterFormAdminModule::OPTION_IS_DEFAULT_REGISTRATION_FORM, true);
					$adminModuleInstance->saveOption(RegisterFormAdminModule::OPTION_FORM_TITLE, __('Default Registration', 'ultra-community'));

					$defaultUserRolePOstType = UserRoleController::getDefaultUserRolePostType();
					empty($defaultUserRolePOstType->PostId) ?: $adminModuleInstance->saveOption(RegisterFormAdminModule::OPTION_USER_ROLE_CUSTOM_POST_ID, $defaultUserRolePOstType->PostId);
				}

				continue;
			}

			if($postTypeKey === PostTypeController::POST_TYPE_USER_PROFILE_FORM)
			{
				$customPostType->PostTitle = 'UltraComm User Profile Form';

				$shouldPublish = true;
				foreach(PostTypeController::getPublishedPosts($postTypeKey, true) as $publishedPost){
					if(null !== ($adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($publishedPost))){
						if($adminModuleInstance->getOption(UserProfileFormAdminModule::OPTION_IS_DEFAULT_USER_PROFILE_FORM)){
							$shouldPublish = false; break;
						}
					}
				}

				if($shouldPublish)
				{
					$customPostType->PostId = PostTypeController::publishPostType( $customPostType );
					$adminModuleInstance = UserProfileFormAdminModule::getInstance(true);
					$adminModuleInstance->setCustomPostType($customPostType);
					$adminModuleInstance->saveDefaultOptions();
					$adminModuleInstance->saveOption(UserProfileFormAdminModule::OPTION_IS_DEFAULT_USER_PROFILE_FORM, true);
					$adminModuleInstance->saveOption(UserProfileFormAdminModule::OPTION_ASSIGNED_USER_ROLES, array_map(function($userRolePostType){return $userRolePostType->PostId;}, PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_ROLE)));
					$adminModuleInstance->saveOption(UserProfileFormAdminModule::OPTION_FORM_TITLE, __('Default User Profile', 'ultra-community'));
				}

				continue;
			}

//			if($postTypeKey === PostTypeController::POST_TYPE_USER_ROLE && ModulesController::isModuleRegistered(ModulesController::MODULE_USER_ROLE) )
//			{
//				$arrDefaultUserRoles = UserRoleController::getDefaultUserRoles();
//
//				$customPostType->PostId    = null;
//				$customPostType->PostTitle = $arrDefaultUserRoles[UserRoleController::ROLE_ADMIN_SLUG];
//
//				$shouldPublish = true;
//				foreach(PostTypeController::getPublishedPosts($postTypeKey, true) as $publishedPost){
//					if(null !== ($adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($publishedPost))){
//						if($adminModuleInstance->getOption(UserRoleAdminModule::OPTION_ROLE_SLUG) === UserRoleController::ROLE_ADMIN_SLUG){
//							$shouldPublish = false;
//						}
//					}
//				}
//
//
//				if($shouldPublish)
//				{
//					$customPostType->PostId = PostTypeController::publishPostType($customPostType);
//					$userRoleAdminModuleInstance = UserRoleAdminModule::getInstance(true);
//					$userRoleAdminModuleInstance->setCustomPostType($customPostType);
//
//
//
//					$userRoleAdminModuleInstance->saveOption(UserRoleAdminModule::OPTION_ROLE_TITLE, $customPostType->PostTitle);
//					$userRoleAdminModuleInstance->saveOption(UserRoleAdminModule::OPTION_ROLE_SLUG, UserRoleController::ROLE_ADMIN_SLUG);
//					$userRoleAdminModuleInstance->saveOption(UserRoleAdminModule::OPTION_AFTER_REGISTRATION_ACTION, UserRoleAdminModule::REGISTRATION_ACTION_ADMIN_REVIEW);
//
//					foreach (UserRoleController::getDefaultUltraCommCapabilitiesByRole(UserRoleController::ROLE_ADMIN_SLUG) as $adminCapability => $enabled)
//					{
//						$userRoleAdminModuleInstance->saveOption($adminCapability, true);
//					}
//
//					UserRoleController::handleUserRoles();
//
//					//UserController::getLoggedInUser()->UserMetaEntity->UserRoleCustomPostId = $customPostType->PostId;
//					UserController::saveUserInfo(UserController::getLoggedInUser());
//				}
//
//				$customPostType->PostId    = null;
//				$customPostType->PostTitle = $arrDefaultUserRoles[UserRoleController::ROLE_MEMBER_SLUG];
//
//				$shouldPublish = true;
//				foreach(PostTypeController::getPublishedPosts($postTypeKey, true) as $publishedPost){
//					if(null !== ($adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($publishedPost))){
//						if($adminModuleInstance->getOption(UserRoleAdminModule::OPTION_ROLE_SLUG) === UserRoleController::ROLE_MEMBER_SLUG){
//							$shouldPublish = false;
//						}
//					}
//				}
//
//				if( $shouldPublish ) //!WpPostRepository::findByPostSlug(MchWpUtils::formatUrlPath($customPostType->PostTitle), $postTypeKey)
//				{
//					$customPostType->PostId = PostTypeController::publishPostType($customPostType);
//					$userRoleAdminModuleInstance = UserRoleAdminModule::getInstance(true);
//					$userRoleAdminModuleInstance->setCustomPostType($customPostType);
//
//					$userRoleAdminModuleInstance->saveOption(UserRoleAdminModule::OPTION_ROLE_TITLE, $customPostType->PostTitle);
//					$userRoleAdminModuleInstance->saveOption(UserRoleAdminModule::OPTION_ROLE_SLUG, UserRoleController::ROLE_MEMBER_SLUG);
//					$userRoleAdminModuleInstance->saveOption(UserRoleAdminModule::OPTION_AFTER_REGISTRATION_ACTION, UserRoleAdminModule::REGISTRATION_ACTION_AUTO_APPROVE);
//
//					foreach (UserRoleController::getDefaultUltraCommCapabilitiesByRole(UserRoleController::ROLE_MEMBER_SLUG) as $memberCapability => $enabled)
//					{
//						(!$enabled) ?: $userRoleAdminModuleInstance->saveOption($memberCapability, true);
//					}
//
//					UserRoleController::handleUserRoles();
//
//					//UserController::getLoggedInUser()->UserMetaEntity->UserRoleCustomPostId = $customPostType->PostId;
//					UserController::saveUserInfo(UserController::getLoggedInUser());
//
//				}
//
//				continue;
//			}

		}

	}


	private static function setDefaultFormsFields()
	{

		$savedPluginVersion = PluginSettingsPublicModule::getInstance()->getOption(PluginSettingsAdminModule::PLUGIN_ACTIVE_VERSION);
		if(!empty($savedPluginVersion))
			return;

		foreach(PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_REGISTER_FORM, true) as $publishedPost)
		{
			if(null === ($adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($publishedPost)) || !$adminModuleInstance->getOption(RegisterFormAdminModule::OPTION_IS_DEFAULT_REGISTRATION_FORM))
				continue;

			$arrFields = array();

			$fieldInstance = new UserFirstNameField();
			$fieldInstance->Name = 'UserFirstName';
			$fieldInstance->FontAwesomeIcon = 'fa-user-o';
			$arrFields[] = $fieldInstance;

			$fieldInstance = new UserLastNameField();
			$fieldInstance->Name = 'UserLastName';
			$fieldInstance->FontAwesomeIcon = 'fa-user-o';
			$arrFields[] = $fieldInstance;

			$fieldInstance = new UserNameField();
			$fieldInstance->Name = 'UserName';
			$fieldInstance->IsRequired = true;
			$fieldInstance->FontAwesomeIcon = 'fa-user';
			$fieldInstance->ErrorMessage = 'Please provide a valid username!';
			$arrFields[] = $fieldInstance;

			$fieldInstance = new UserEmailField();
			$fieldInstance->Name = 'UserEmail';
			$fieldInstance->IsRequired = true;
			$fieldInstance->FontAwesomeIcon = 'fa-envelope-o';
			$fieldInstance->ErrorMessage = 'Please provide your email address!';
			$arrFields[] = $fieldInstance;

			$fieldInstance = new UserPasswordField();
			$fieldInstance->Name = 'Password';
			$fieldInstance->IsRequired = true;
			$fieldInstance->FontAwesomeIcon = 'fa-lock';
			$fieldInstance->ErrorMessage = 'Please provide your password!';
			$arrFields[] = $fieldInstance;

			$fieldInstance = new UserPasswordField();
			$fieldInstance->Name = 'ConfirmPassword';
			$fieldInstance->Label = 'Confirm Password';
			$fieldInstance->IsRequired = true;
			$fieldInstance->FontAwesomeIcon = 'fa-lock';
			$fieldInstance->ErrorMessage = 'Please confirm your password!';
			$arrFields[] = $fieldInstance;
			//$arrFields[] = $fieldInstance;

			try
			{
				foreach ($arrFields as $formField)
				{
					$adminModuleInstance->saveFormFieldSettings($formField);
				}
			}
			catch (UltraCommException $ue)
			{}

		}


		foreach(PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_LOGIN_FORM, true) as $publishedPost)
		{
			if(null === ($adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($publishedPost)) || !$adminModuleInstance->getOption(RegisterFormAdminModule::OPTION_IS_DEFAULT_LOGIN_FORM))
				continue;

			$arrFields = array();

			$fieldInstance = new UserNameOrEmailField();
			$fieldInstance->Name = 'UserNameOREmail';
			$fieldInstance->IsRequired = true;
			$fieldInstance->FontAwesomeIcon = 'fa-user';
			$fieldInstance->ErrorMessage = 'Please provide your Username or Email Address!';

			$arrFields[] = $fieldInstance;

			$fieldInstance = new UserPasswordField();
			$fieldInstance->Name = 'Password';
			$fieldInstance->IsRequired = true;
			$fieldInstance->FontAwesomeIcon = 'fa-lock';
			$fieldInstance->ErrorMessage = 'Please enter your Password!';

			$arrFields[] = $fieldInstance;

			try
			{
				foreach ($arrFields as $formField)
				{
					$adminModuleInstance->saveFormFieldSettings($formField);
				}
				
				$defaultRegistrationPageId =  FrontPageSettingsAdminModule::getInstance()->getOption(FrontPageSettingsAdminModule::REGISTER_PAGE_ID);
				$defaultRegistrationPageId && $adminModuleInstance->saveOption(LoginFormAdminModule::OPTION_REGISTRATION_PAGE_ID, $defaultRegistrationPageId);
				
			}
			catch (UltraCommException $ue)
			{}
		}

		foreach(PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_FORGOT_PASSWORD_FORM, true) as $publishedPost)
		{
			if(null === ($adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($publishedPost)) || !$adminModuleInstance->getOption(RegisterFormAdminModule::OPTION_IS_DEFAULT_FORGOT_PASSWORD_FORM))
				continue;

			$arrFields = array();

			$fieldInstance = new UserNameOrEmailField();
			$fieldInstance->Name = 'UserNameOREmail';
			$fieldInstance->IsRequired = true;
			$fieldInstance->FontAwesomeIcon = 'fa-user';
			$fieldInstance->ErrorMessage = 'Please provide your Username or Email Address!';
			$arrFields[] = $fieldInstance;


			try
			{
				foreach ($arrFields as $formField)
				{
					$adminModuleInstance->saveFormFieldSettings($formField);
				}
			}
			catch (UltraCommException $ue)
			{}
		}



		foreach(PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_PROFILE_FORM, true) as $publishedPost)
		{
			if(null === ($adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($publishedPost)) || !$adminModuleInstance->getOption(UserProfileFormAdminModule::OPTION_IS_DEFAULT_USER_PROFILE_FORM))
				continue;

			$arrFields = array();

			$fieldInstance = new ProfileSectionField();
			$fieldInstance->Name  = 'Personal Information Section';
			$fieldInstance->Title = 'Personal Information';
			$fieldInstance->FontAwesomeIcon = 'info';
			$arrFields[] = $fieldInstance;



			$fieldInstance = new UserFirstNameField();
			$fieldInstance->Name  = 'UserFirstName';
			$fieldInstance->Label = 'First Name';
			$fieldInstance->FrontEndVisibility = true;
			$fieldInstance->IsEditable = true;
			$fieldInstance->IsRequired = true;
			$fieldInstance->IsVisibleOnEditProfile = true;
			$arrFields[] = $fieldInstance;


			$fieldInstance = new UserLastNameField();
			$fieldInstance->Name  = 'UserLastName';
			$fieldInstance->Label = 'Last Name';
			$fieldInstance->FrontEndVisibility = true;
			$fieldInstance->IsEditable = true;
			$fieldInstance->IsRequired = true;
			$fieldInstance->IsVisibleOnEditProfile = true;
			$arrFields[] = $fieldInstance;

			$fieldInstance = new TextField();
			$fieldInstance->Name  = 'UserCity';
			$fieldInstance->Label = 'City';
			$fieldInstance->FrontEndVisibility = true;
			$fieldInstance->IsEditable = true;
			$fieldInstance->IsVisibleOnEditProfile = true;
			$arrFields[] = $fieldInstance;


			$fieldInstance = new CountryField();
			$fieldInstance->Name  = 'UserCountry';
			$fieldInstance->Label = 'Country';
			$fieldInstance->FrontEndVisibility = true;
			$fieldInstance->IsEditable = true;
			$fieldInstance->IsVisibleOnEditProfile = true;
			$arrFields[] = $fieldInstance;


			$fieldInstance = new UserWebUrlField();
			$fieldInstance->Name  = 'UserWebsiteUrl';
			$fieldInstance->Label = 'Website Url';
			$fieldInstance->FrontEndVisibility = true;
			$fieldInstance->IsEditable = true;
			$fieldInstance->IsVisibleOnEditProfile = true;
			$arrFields[] = $fieldInstance;


			$fieldInstance = new UserBioField();
			$fieldInstance->Name  = 'UserAboutMe';
			$fieldInstance->Label = 'About Me';
			$fieldInstance->FrontEndVisibility = true;
			$fieldInstance->IsEditable = true;
			$fieldInstance->IsRequired = true;
			$fieldInstance->IsVisibleOnEditProfile = true;
			$fieldInstance->IsHtmlAllowed = true;
			$arrFields[] = $fieldInstance;


			$fieldInstance = new ProfileSectionField();
			$fieldInstance->Name  = 'Social Networks Section';
			$fieldInstance->Title = 'Social Networks';
			$fieldInstance->FontAwesomeIcon = 'fa-share-alt';
			$arrFields[] = $fieldInstance;


			$fieldInstance = new SocialNetworkUrlField();
			$fieldInstance->Name  = 'UserFaceBookUrl';
			$fieldInstance->Label = 'Facebook';
			$fieldInstance->NetworkId = 5;
			$fieldInstance->FrontEndVisibility = true;
			$fieldInstance->IsEditable = true;
			$fieldInstance->IsVisibleOnEditProfile = true;
			$arrFields[] = $fieldInstance;

			$fieldInstance = new SocialNetworkUrlField();
			$fieldInstance->Name  = 'UserGoogleUrl';
			$fieldInstance->Label = 'Google+';
			$fieldInstance->NetworkId = 9;
			$fieldInstance->FrontEndVisibility = true;
			$fieldInstance->IsEditable = true;
			$fieldInstance->IsVisibleOnEditProfile = true;
			$arrFields[] = $fieldInstance;

			$fieldInstance = new SocialNetworkUrlField();
			$fieldInstance->Name  = 'UserTwitterUrl';
			$fieldInstance->Label = 'Twitter';
			$fieldInstance->NetworkId = 21;
			$fieldInstance->FrontEndVisibility = true;
			$fieldInstance->IsEditable = true;
			$fieldInstance->IsVisibleOnEditProfile = true;
			$arrFields[] = $fieldInstance;


			$fieldInstance = new SocialNetworkUrlField();
			$fieldInstance->Name  = 'UserInstagramUrl';
			$fieldInstance->Label = 'Instagram';
			$fieldInstance->NetworkId = 10;
			$fieldInstance->FrontEndVisibility = true;
			$fieldInstance->IsEditable = true;
			$fieldInstance->IsVisibleOnEditProfile = true;
			$arrFields[] = $fieldInstance;


			$fieldInstance = new SocialNetworkUrlField();
			$fieldInstance->Name  = 'UserSlackUrl';
			$fieldInstance->Label = 'Slack';
			$fieldInstance->NetworkId = 29;
			$fieldInstance->FrontEndVisibility = true;
			$fieldInstance->IsEditable = true;
			$fieldInstance->IsVisibleOnEditProfile = true;
			$arrFields[] = $fieldInstance;

			try
			{
				foreach ($arrFields as $formField)
				{
					$adminModuleInstance->saveFormFieldSettings($formField);
				}
			}
			catch (UltraCommException $ue)
			{
				//echo $ue->getMessage();exit;
			}
		}



	}


	public static function uninstall()
	{
		delete_option('ultracomm-online-users');

		foreach(PostTypeController::getAllPostTypeKeys() as $postTypeKey)
		{
			
			if($postTypeKey  === PostTypeController::POST_TYPE_ACTIVITY)
				continue;
			
			try
			{
				foreach (PostTypeController::getPublishedPosts($postTypeKey, true) as $publishedPostType)
				{
					PostTypeController::deletePostType($publishedPostType);
				}
			}
			catch(\Exception $ue)
			{
//				echo $ue->getMessage();
			}
		}

		
		foreach (array_keys(ModulesController::getRegisteredModules()) as $registeredModule)
		{
			if(null === ($adminModuleInstance = ModulesController::getAdminModuleInstance($registeredModule)))
				continue;

			if($adminModuleInstance instanceof FrontPageSettingsAdminModule)
			{
				WpPostRepository::delete($adminModuleInstance->getOption(FrontPageSettingsAdminModule::LOGIN_PAGE_ID));
				WpPostRepository::delete($adminModuleInstance->getOption(FrontPageSettingsAdminModule::REGISTER_PAGE_ID));
				WpPostRepository::delete($adminModuleInstance->getOption(FrontPageSettingsAdminModule::USER_PROFILE_PAGE_ID));
				WpPostRepository::delete($adminModuleInstance->getOption(FrontPageSettingsAdminModule::FORGOT_PASSWORD_PAGE_ID));
				WpPostRepository::delete($adminModuleInstance->getOption(FrontPageSettingsAdminModule::MEMBERS_DIRECTORY_PAGE_ID));
				WpPostRepository::delete($adminModuleInstance->getOption(FrontPageSettingsAdminModule::GROUPS_DIRECTORY_PAGE_ID));
			}

			delete_site_option($adminModuleInstance->getSettingKey()) ;
			delete_option($adminModuleInstance->getSettingKey());
		}

		
		foreach ($posts = get_posts(['post_type' => PostTypeController::POST_TYPE_ACTIVITY]) as $activityPost)
		{
			empty($activityPost->ID) ?: WpPostRepository::delete($activityPost->ID);
		}
		
		
		BaseRepository::deleteCreatedTablesOnUninstall();
		
		delete_metadata('user', 0, 'ultracomm-user-info', null, true);
		
		delete_metadata('user', 0, UserPrivacyEntity::META_KEY_HIDE_IN_DIRECTORIES, null, true);
		delete_metadata('user', 0, UserPrivacyEntity::META_KEY_HIDE_IN_SEARCHES,    null, true);
		delete_metadata('user', 0, UserPrivacyEntity::META_KEY_HIDE_ONLINE_STATUS,  null, true);
		delete_metadata('user', 0, UserPrivacyEntity::META_KEY_PROFILE_VISIBILITY,  null, true);
		
		
	}


	public static function checkExtensionsUpdates()
	{
//		wp_next_scheduled( 'ultracomm_check_extensions_updates' ) ?: wp_schedule_event( time() + 86400, 'daily', 'ultracomm_check_extensions_updates' );
//		add_action( 'ultracomm_check_extensions_updates', function(){
//
//			foreach();
//
//
//		});

		//set_site_transient( 'update_plugins', null );

		foreach(ModulesController::getLicensedModuleNames() as $licensedModuleName)
		{
			if(!ModulesController::isModuleRegistered($licensedModuleName) || !ModulesController::getModuleIdByName($licensedModuleName) || !class_exists( $moduleClassName = ModulesController::getModuleStandAloneClassName($licensedModuleName)) || !defined("$moduleClassName::MODULE_VERSION"))
				continue;

			$licenseKey = LicensesPublicModule::getInstance()->getOption($licensedModuleName);
			$classReflector = new \ReflectionClass($moduleClassName);

			new MchPluginUpdater(\UltraCommunity::PLUGIN_SITE_URL, $classReflector->getFileName(), array(
					'version' => constant("$moduleClassName::MODULE_VERSION"),
					'item_id' => ModulesController::getModuleIdByName($licensedModuleName),
					'license' => $licenseKey,
					'author'  => 'MihChe',
					'url'     => home_url(),
			));

			add_action( 'in_plugin_update_message-' . plugin_basename( $classReflector->getFileName() ), function($pluginData, $versionInfo) use($licenseKey){

				if(empty($licenseKey))
				{
					echo '&nbsp;<strong><a href="' . esc_url( admin_url( 'admin.php?page=uc-general-settings-generalsettingsadminpage&modulekey=uc-licenses-settings' ) ) . '">' . __( 'Enter valid license key for automatic updates.', 'ultra-community' ) . '</a></strong>';
				}

			}, 10, 2 );

		}

	}

	private function __construct()
	{}
}