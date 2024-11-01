<?php


namespace UltraCommunity\Controllers;

use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\Repository\WpUserRepository;
use UltraCommunity\Modules\GeneralSettings\User\UserSettingsAdminModule;
use UltraCommunity\Modules\GeneralSettings\User\UserSettingsPublicModule;
use UltraCommunity\Modules\UserRole\UserRoleAdminModule;
use UltraCommunity\Repository\BaseRepository;
use UltraCommunity\Repository\UserRepository;
use UltraCommunity\UltraCommException;
use UltraCommunity\PostsType\UserRolePostType;

class UserRoleController
{
//	CONST ROLE_ADMIN_SLUG  = 'ultracomm_admin';
//	CONST ROLE_MEMBER_SLUG = 'ultracomm_member';


	CONST ROLE_ADMIN_SLUG  = 'administrator';
	CONST ROLE_MEMBER_SLUG = 'subscriber';


	CONST USER_CAP_MANAGE_ULTRA_COMMUNITY = 'uc_manage_ultracomm'; // Just Admin

	CONST USER_CAP_EDIT_OWN_PROFILE       = 'uc_edit_own_profile';
	CONST USER_CAP_EDIT_OTHER_PROFILES    = 'uc_edit_other_profiles';

	CONST USER_CAP_DELETE_OTHER_PROFILES  = 'uc_delete_other_profiles';

	CONST USER_CAP_VIEW_OTHER_PROFILES    = 'uc_view_other_profiles';
	CONST USER_CAP_VIEW_PRIVATE_PROFILES  = 'uc_view_private_profiles';
	CONST USER_CAP_VIEW_ADMIN_TOOLBAR     = 'uc_view_admin_toolbar';

	CONST USER_CAP_SETUP_PROFILE_PRIVACY  = 'uc_setup_profile_privacy';

	CONST USER_CAP_CHANGE_PROFILE_AVATAR  = 'uc_change_profile_avatar';
	CONST USER_CAP_CHANGE_PROFILE_COVER   = 'uc_change_profile_cover';

	CONST USER_CAP_ACCESS_WP_DASHBOARD    = 'uc_access_wp_dashboard';

	CONST USER_CAP_CHANGE_PASSWORD        = 'uc_change_password';
	CONST USER_CAP_DELETE_ACCOUNT         = 'uc_delete_account';

	CONST USER_CAP_CREATE_USER_GROUPS     = 'uc_create_user_groups';
	CONST USER_CAP_CONTROL_GROUP_PRIVACY  = 'uc_control_group_privacy';


	CONST USER_CAP_DELETE_ACTIVITY_POST   = 'uc_delete_activity_post';


	CONST USER_CAP_CREATE_NEW_POSTS     = 'uc_create_new_posts';
	CONST USER_CAP_EDIT_OWN_POSTS       = 'uc_edit_own_posts';
	CONST USER_CAP_DELETE_OWN_POSTS     = 'uc_delete_own_posts';
	CONST USER_CAP_ACCESS_MEDIA_LIBRARY = 'uc_access_media_library';


	private function __construct()
	{}


	public static function currentUserCanManageUltraCommunity()
	{
		return \current_user_can(self::USER_CAP_MANAGE_ULTRA_COMMUNITY);
	}

	public static function currentUserCanDeleteAccount()
	{
		return \current_user_can(self::USER_CAP_DELETE_ACCOUNT);
	}

	public static function currentUserCanChangePassword()
	{
		return \current_user_can(self::USER_CAP_CHANGE_PASSWORD);
	}

	public static function currentUserCanEditOwnProfile()
	{
		return \current_user_can(self::USER_CAP_EDIT_OWN_PROFILE);
	}

	public static function currentUserCanEditOtherProfiles()
	{
		return \current_user_can(self::USER_CAP_EDIT_OTHER_PROFILES);
	}

	public static function currentUserCanChangeProfileAvatar()
	{
		return \current_user_can(self::USER_CAP_CHANGE_PROFILE_AVATAR);
	}

	public static function currentUserCanChangeProfileCover()
	{
		return \current_user_can(self::USER_CAP_CHANGE_PROFILE_COVER);
	}

	public static function currentUserCanDeleteOtherProfiles()
	{
		return \current_user_can(self::USER_CAP_DELETE_OTHER_PROFILES);
	}

	public static function currentUserCanViewOtherProfiles()
	{
		return \current_user_can(self::USER_CAP_VIEW_OTHER_PROFILES);
	}

	public static function currentUserCanViewPrivateProfiles()
	{
		return \current_user_can(self::USER_CAP_VIEW_PRIVATE_PROFILES);
	}

	public static function currentUserCanViewAdminToolbar()
	{
		return \current_user_can(self::USER_CAP_VIEW_ADMIN_TOOLBAR);
	}

	public static function currentUserCanSetupProfilePrivacy()
	{
		return \current_user_can(self::USER_CAP_SETUP_PROFILE_PRIVACY);
	}

	public static function currentUserCanAccessWPDashboard()
	{
		return \current_user_can(self::USER_CAP_ACCESS_WP_DASHBOARD);
	}

	public static function currentUserCanCreateUserGroups()
	{
		return \current_user_can(self::USER_CAP_CREATE_USER_GROUPS);
	}

	public static function userHasCapability($userKey, $capability)
	{
		$userId = BaseRepository::getUserIdFromKey($userKey);
		return empty($userId) ? false : \user_can($userId, $capability);
	}

	public static function isBuiltInUserRole($userRoleSlug)
	{
		return ($userRoleSlug === self::ROLE_MEMBER_SLUG || $userRoleSlug === self::ROLE_ADMIN_SLUG);
	}

	public static function isDefaultAdminRole($userRoleSlug)
	{
		return $userRoleSlug === self::ROLE_ADMIN_SLUG;
	}

	public static function isDefaultMemberRole($userRoleKey)
	{
		return $userRoleKey === self::ROLE_MEMBER_SLUG;
	}


	public static function getUserRoles($userKey)
	{
		$wpUserInfo = ($userKey instanceof \WP_User) ? $userKey : new \WP_User(UserRepository::getUserIdFromKey($userKey));
		if(empty($wpUserInfo->roles))
			return array();

		$arrUserRoles = array();
		$wpRolesObject = wp_roles();

		foreach ($wpUserInfo->roles as $userRoleSlug)
		{
			if(!isset($wpRolesObject->role_names[$userRoleSlug]))
				continue;

			//$arrUserRoles[$userRoleSlug] = $wpRolesObject->role_names[$userRoleSlug];

			$arrUserRoles[] = $userRoleSlug;
		}

		return $arrUserRoles;

	}

	public static function getRoleDescriptionBySlug($roleSlugKey)
	{
		$arrAllRoles = self::getAllRegisteredUserRoles();
		return isset($arrAllRoles[$roleSlugKey]) ? $arrAllRoles[$roleSlugKey] : null;
	}

	/**
	 * @return null|UserRolePostType
	 */
	public static function getDefaultUserRolePostType()
	{

		$defaultUserRoleId = UserSettingsPublicModule::getInstance()->getOption(UserSettingsAdminModule::OPTION_DEFAULT_USER_ROLE);

		if(null !== $defaultUserRoleId)
		{
			foreach ( PostTypeController::getPublishedPosts( PostTypeController::POST_TYPE_USER_ROLE ) as $customPostUserRole ){
				if($customPostUserRole->PostId == $defaultUserRoleId)
					return $customPostUserRole;
			}
		}

		foreach ( PostTypeController::getPublishedPosts( PostTypeController::POST_TYPE_USER_ROLE ) as $customPostUserRole )
		{
			if ( null === ( $publicModuleInstance = PostTypeController::getAssociatedPublicModuleInstance( $customPostUserRole ) ) ) {
				continue;
			}

			if(!UserRoleController::isDefaultMemberRole($publicModuleInstance->getOption( UserRoleAdminModule::OPTION_ROLE_SLUG )))
				continue;

			UserSettingsAdminModule::getInstance()->saveOption(UserSettingsAdminModule::OPTION_DEFAULT_USER_ROLE, $customPostUserRole->PostId);

			return $customPostUserRole;
		}

		return null;

	}

	public static function isValidUserRole($roleSlug)
	{
		return wp_roles()->is_role($roleSlug);

	}

	/**
	 * @param $userKey
	 *
	 * @return UserRolePostType[]
	 */
	public static function getUserRolePostTypes($userKey)
	{
		$wpUserInfo = ($userKey instanceof \WP_User) ? $userKey : new \WP_User(UserRepository::getUserIdFromKey($userKey));
		if(empty($wpUserInfo->roles))
			return array();

		$arrUserRoles = array();
		foreach(PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_ROLE) as $userRolePostType)
		{
			if(null === ($adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($userRolePostType)))
				continue;

			$userRolePostType->PostSlug = $adminModuleInstance->getOption(UserRoleAdminModule::OPTION_ROLE_SLUG);
			$userRolePostType->Priority = (int)$adminModuleInstance->getOption(UserRoleAdminModule::OPTION_ROLE_PRIORITY);

			if(!wp_roles()->is_role($userRolePostType->PostSlug))
				continue;

			if(!in_array($userRolePostType->PostSlug, $wpUserInfo->roles))
				continue;

			$arrUserRoles[$userRolePostType->PostId] = $userRolePostType;
		}


		return $arrUserRoles;

	}


	public static function userHasUltraCommRole($userKey)
	{
		foreach(self::getUserRoles($userKey) as $userRole){
			if(self::isUltraCommUserRole($userRole))
				return true;
		}

		return false;
	}

	public static function getUserUltraCommRoles($userKey)
	{
		$arrUltraCommRoles = array();
		foreach(self::getUserRoles($userKey) as $userRole)
		{
			!self::isUltraCommUserRole($userRole) ?: $arrUltraCommRoles[] = $userRole;
		}

		return $arrUltraCommRoles;

	}


	private static function getDefaultUserRolesPriorities()
	{
		return array(
				'administrator' => 100,
				'editor'        => 80,
				'author'        => 60,
				'contributor'   => 40,
				'subscriber'    => 0
		);
	}

	public static function getAllRegisteredUserRoles()
	{
		return wp_roles()->get_names();

//		$arrUserRoles = wp_roles()->get_names();

//		if(!ModulesController::isModuleRegistered(ModulesController::MODULE_BBPRESS))
//		{
//			return array_diff_key($arrUserRoles, array_flip(array('bbp_keymaster','bbp_spectator', 'bbp_blocked', 'bbp_moderator', 'bbp_participant',)));
//		}
//
//		if(!ModulesController::isModuleRegistered(ModulesController::MODULE_WOOCOMMERCE))
//		{
//			return array_diff_key($arrUserRoles, array_flip(array('customer', 'shop_manager')));
//		}

//		return $arrUserRoles;
	}


	public static function handleUserRoles()
	{

		if(!post_type_exists(PostTypeController::POST_TYPE_USER_ROLE) || MchWpUtils::isAjaxRequest() || wp_doing_cron() || !(wp_roles() instanceof \WP_Roles)){
			return;
		}
		
		$arrPublishedUserRolesPostType = array_map(function($postObject){return clone $postObject;}, PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_ROLE, true));
		
		$arrRolesDefaultPriorities = self::getDefaultUserRolesPriorities();

		foreach ($arrPublishedUserRolesPostType as $index => $userRolePostType)
		{
			if(null === ($adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($userRolePostType)))
				continue;

			$roleSlug = $adminModuleInstance->getOption(UserRoleAdminModule::OPTION_ROLE_SLUG);

			$userRolePostType->PostSlug = $roleSlug;

			if(wp_roles()->is_role($roleSlug))
				continue;

			if(self::isUltraCommUserRole($roleSlug))
				continue;

			try
			{
				PostTypeController::deletePostType($userRolePostType);
				unset($arrPublishedUserRolesPostType[$index]);
			}
			catch (UltraCommException $ue)
			{
			
			}

		}

		
		foreach (self::getAllRegisteredUserRoles() as $roleSlug => $roleDescription)
		{

			$arrPublishedPostType = array_filter($arrPublishedUserRolesPostType, function ($userRolePostType) use ($roleSlug){
				return $userRolePostType->PostSlug === $roleSlug;
			});

			if(!empty($arrPublishedPostType))
				continue;

			$customPostType = PostTypeController::getPostTypeInstance(PostTypeController::POST_TYPE_USER_ROLE);
			$customPostType->PostTitle = $roleDescription;
			$customPostType->PostId = PostTypeController::publishPostType($customPostType);

			if(empty($customPostType->PostId)) {
				continue;
			}
			
			if(null === ($adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($customPostType))){
				continue;
			}
			
			$customPostType->PostSlug = $roleSlug;
			
			$arrPublishedUserRolesPostType[$customPostType->PostId] = $customPostType;
			
			$afterRegistrationActionId = self::isDefaultMemberRole($roleSlug) ? UserRoleAdminModule::REGISTRATION_ACTION_AUTO_APPROVE : UserRoleAdminModule::REGISTRATION_ACTION_SEND_EMAIL;
			if(self::isDefaultAdminRole($roleSlug)){
				$afterRegistrationActionId = UserRoleAdminModule::REGISTRATION_ACTION_ADMIN_REVIEW;
			}

			$adminModuleInstance->saveOption(UserRoleAdminModule::OPTION_ROLE_SLUG, $roleSlug);
			$adminModuleInstance->saveOption(UserRoleAdminModule::OPTION_ROLE_TITLE, $roleDescription);
			$adminModuleInstance->saveOption(UserRoleAdminModule::OPTION_AFTER_REGISTRATION_ACTION, $afterRegistrationActionId);
			$adminModuleInstance->saveOption(UserRoleAdminModule::OPTION_ROLE_PRIORITY, empty($arrRolesDefaultPriorities[$roleSlug]) ? 0 : $arrRolesDefaultPriorities[$roleSlug]);

			foreach (UserRoleController::getDefaultUltraCommCapabilitiesByRole($roleSlug) as $memberCapability => $enabled) {
				(!$enabled) ?: $adminModuleInstance->saveOption($memberCapability, true);
			}

		}

		foreach (PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_ROLE, true) as $userRolePostType)
		{
			if(null === ($adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($userRolePostType)))
				continue;

			$roleKey          = $adminModuleInstance->getOption(UserRoleAdminModule::OPTION_ROLE_SLUG);
			$roleDescription  = $adminModuleInstance->getOption(UserRoleAdminModule::OPTION_ROLE_TITLE);

			if(empty($roleKey) || empty($roleDescription))
				continue;

			$arrRoleCapabilities = array();
			foreach (self::getUltraCommCapabilityList() as $capabilityKey){
				$arrRoleCapabilities[$capabilityKey] = (bool)$adminModuleInstance->getOption($capabilityKey);
			}

			$arrRoleCapabilities = wp_parse_args($arrRoleCapabilities, self::getDefaultUltraCommCapabilitiesByRole($roleKey));


			if(self::isDefaultAdminRole($roleKey))
			{ // just to be safe
				$arrRoleCapabilities[self::USER_CAP_MANAGE_ULTRA_COMMUNITY] = TRUE;
				$arrRoleCapabilities[self::USER_CAP_ACCESS_WP_DASHBOARD]    = TRUE;
				$arrRoleCapabilities[self::USER_CAP_DELETE_ACCOUNT]         = FALSE;
				$arrRoleCapabilities[self::USER_CAP_CHANGE_PASSWORD]        = FALSE;
			}

			$wpUserRole =  wp_roles()->get_role($roleKey);
			$savedRoleDescription = isset(wp_roles()->role_names[$roleKey]) ? wp_roles()->role_names[$roleKey] : null;

			if(null === $wpUserRole)
			{
				wp_roles()->add_role($roleKey, $roleDescription, array_filter($arrRoleCapabilities));
				continue;
			}

			if(self::isUltraCommUserRole($roleKey) && ($savedRoleDescription != $roleDescription))
			{
				wp_roles()->remove_role($roleKey);
				wp_roles()->add_role($roleKey, $roleDescription, array_filter($arrRoleCapabilities));
			}

			//print_r(array($roleKey => $arrRoleCapabilities));
			
			foreach ($arrRoleCapabilities as $capabilityKey => $enabled)
			{
				if($enabled && $wpUserRole->has_cap($capabilityKey))
					continue;

				if(!$enabled && !$wpUserRole->has_cap($capabilityKey))
					continue;

				if($enabled && !$wpUserRole->has_cap($capabilityKey))
				{
					$wpUserRole->add_cap($capabilityKey, $enabled);
				}

				if(!$enabled && $wpUserRole->has_cap($capabilityKey))
				{
					$wpUserRole->remove_cap($capabilityKey);
				}

			}

		}

		PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_ROLE, true); // refresh cache
		
		self::getDefaultUserRolePostType();


	}


	public static function getDefaultUltraCommCapabilitiesByRole($userRoleKey)
	{
		$arrCapabilities = array();

		foreach (self::getUltraCommCapabilityList() as $capability)
		{
			if(self::isDefaultAdminRole($userRoleKey))
			{

				$arrCapabilities[$capability] = TRUE;

				$arrRoleCapabilities[self::USER_CAP_MANAGE_ULTRA_COMMUNITY] = TRUE;
				$arrRoleCapabilities[self::USER_CAP_ACCESS_WP_DASHBOARD]    = TRUE;
				$arrRoleCapabilities[self::USER_CAP_CREATE_USER_GROUPS]     = TRUE;
				$arrRoleCapabilities[self::USER_CAP_CONTROL_GROUP_PRIVACY]  = TRUE;
				$arrRoleCapabilities[self::USER_CAP_DELETE_ACCOUNT]         = FALSE;
				$arrRoleCapabilities[self::USER_CAP_CHANGE_PASSWORD]        = FALSE;

				continue;
			}

			$hasCapability = false;
			switch ($capability)
			{
				case self::USER_CAP_EDIT_OWN_PROFILE :
				case self::USER_CAP_VIEW_OTHER_PROFILES :
				case self::USER_CAP_CREATE_USER_GROUPS :
				case self::USER_CAP_CONTROL_GROUP_PRIVACY :
				case self::USER_CAP_CHANGE_PASSWORD :
				case self::USER_CAP_CHANGE_PROFILE_COVER :
				case self::USER_CAP_CHANGE_PROFILE_AVATAR :
				$hasCapability = true;
				break;

				default:
					break;
			}

			$arrCapabilities[$capability] = $hasCapability;
		}

		return $arrCapabilities;
	}

	public static function getUltraCommCapabilityList()
	{
		return array(

			self::USER_CAP_MANAGE_ULTRA_COMMUNITY,

			self::USER_CAP_EDIT_OWN_PROFILE,
			self::USER_CAP_EDIT_OTHER_PROFILES ,

			self::USER_CAP_DELETE_ACCOUNT,
			self::USER_CAP_CHANGE_PASSWORD,

			self::USER_CAP_VIEW_OTHER_PROFILES ,
			self::USER_CAP_VIEW_PRIVATE_PROFILES,
			self::USER_CAP_VIEW_ADMIN_TOOLBAR ,

			self::USER_CAP_CHANGE_PROFILE_COVER,
			self::USER_CAP_CHANGE_PROFILE_AVATAR,

			self::USER_CAP_SETUP_PROFILE_PRIVACY,

			self::USER_CAP_DELETE_ACTIVITY_POST,

			self::USER_CAP_ACCESS_WP_DASHBOARD,
			self::USER_CAP_CREATE_USER_GROUPS,
			self::USER_CAP_CONTROL_GROUP_PRIVACY,

			self::USER_CAP_CREATE_NEW_POSTS,
			self::USER_CAP_EDIT_OWN_POSTS,
			self::USER_CAP_DELETE_OWN_POSTS,
			self::USER_CAP_ACCESS_MEDIA_LIBRARY

		);
	}

	private static function getDefaultCoreCapabilitiesByRole($userRoleKey)
	{
		$arrCapabilities = array();

		switch ($userRoleKey)
		{
			case self::ROLE_ADMIN_SLUG :
				$arrCapabilities = array(
					'read'                   => true,
					'edit_posts'             => true,
					'delete_posts'           => true,
					'unfiltered_html'        => true,
					'upload_files'           => true,
					'export'                 => true,
					'import'                 => true,
					'delete_others_pages'    => true,
					'delete_others_posts'    => true,
					'delete_pages'           => true,
					'delete_private_pages'   => true,
					'delete_private_posts'   => true,
					'delete_published_pages' => true,
					'delete_published_posts' => true,
					'edit_others_pages'      => true,
					'edit_others_posts'      => true,
					'edit_pages'             => true,
					'edit_private_pages'     => true,
					'edit_private_posts'     => true,
					'edit_published_pages'   => true,
					'edit_published_posts'   => true,
					'manage_categories'      => true,
					'manage_links'           => true,
					'moderate_comments'      => true,
					'publish_pages'          => true,
					'publish_posts'          => true,
					'read_private_pages'     => true,
					'read_private_posts'     => true,
				);

				break;

			case self::ROLE_MEMBER_SLUG:
			default :
				$arrCapabilities = array('read' => true);
			break;
		}


		return $arrCapabilities;


	}

	public static function removeAllUltraCommRoles()
	{



//		foreach (PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_ROLE, true) as $userRolePostType)
//		{
//			if (null === ($adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($userRolePostType)))
//				continue;
//
//			$roleKey = $adminModuleInstance->getOption(UserRoleAdminModule::OPTION_ROLE_SLUG);
//			if (empty($roleKey))
//				continue;
//
//			$userRole = wp_roles()->get_role($roleKey);
//			if(empty($userRole->capabilities))
//			{
//				wp_roles()->remove_role($roleKey);
//				continue;
//			}
//
//			foreach((array)$userRole->capabilities as $capability => $enabled){
//				wp_roles()->remove_cap($roleKey, $capability);
//			}
//
//			wp_roles()->remove_role($roleKey);
//		}
//
//		foreach (array_keys(self::getDefaultUserRoles() ) as $roleKey) // just in case
//		{
//			$userRole = wp_roles()->get_role($roleKey);
//			if(empty($userRole->capabilities))
//			{
//				wp_roles()->remove_role($roleKey);
//				continue;
//			}
//
//			foreach((array)$userRole->capabilities as $capability => $enabled){
//				wp_roles()->remove_cap($roleKey, $capability);
//			}
//
//			wp_roles()->remove_role($roleKey);
//
//		}

	}

	public static function generateUserRoleKeyFromDescription($postTypeId)
	{
		return 'ultracomm_role_' . (absint($postTypeId));
	}

	public static function isUltraCommUserRole($userRoleSlug)
	{
		return 0 === \strpos($userRoleSlug, 'ultracomm_role_') ||  \in_array($userRoleSlug, array('ultracomm_admin', 'ultracomm_member'));

//		if(self::isBuiltInUserRole($userRoleSlug))
//			return true;
//
//		$arrAllRoles = self::getAllRegisteredUserRoles();
//
//		return isset($arrAllRoles[$userRoleSlug]);
	}

}