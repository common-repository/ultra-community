<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity;

use UltraCommunity\Controllers\FrontPageController;
use UltraCommunity\Controllers\GroupController;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Controllers\UserController;
use UltraCommunity\Controllers\UserRoleController;
use UltraCommunity\Entities\ActivityEntity;
use UltraCommunity\Entities\UserEntity;
use UltraCommunity\Entities\GroupEntity;
use UltraCommunity\Entities\UserMetaEntity;
use UltraCommunity\FrontPages\ForgotPasswordPage;
use UltraCommunity\FrontPages\LoginPage;
use UltraCommunity\FrontPages\UserProfilePage;
use UltraCommunity\FrontPages\UserSettingsPage;
use UltraCommunity\MchLib\Plugin\MchBasePlugin;
use UltraCommunity\MchLib\Utils\MchFileUtils;
use UltraCommunity\MchLib\Utils\MchHttpRequest;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\CustomPostType;
use UltraCommunity\MchLib\WordPress\Repository\WpPostRepository;
use UltraCommunity\MchLib\WordPress\Repository\WpUserRepository;
use UltraCommunity\Modules\Appearance\GroupProfile\GroupProfileAppearanceAdminModule;
use UltraCommunity\Modules\Appearance\UserProfile\UserProfileAppearanceAdminModule;
use UltraCommunity\Modules\Appearance\UserProfile\UserProfileAppearancePublicModule;
use UltraCommunity\Modules\Forms\BaseForm\BaseFormAdminModule;
use UltraCommunity\Modules\Forms\FormFields\SocialConnectField;
use UltraCommunity\Modules\Forms\FormFields\SocialNetworkUrlField;
use UltraCommunity\Modules\Forms\UserProfileForm\UserProfileFormAdminModule;
use UltraCommunity\Modules\GeneralSettings\FrontPage\FrontPageSettingsPublicModule;
use UltraCommunity\Modules\GeneralSettings\User\UserSettingsAdminModule;
use UltraCommunity\Modules\GeneralSettings\User\UserSettingsPublicModule;
use UltraCommunity\Modules\UserRole\UserRoleAdminModule;
use UltraCommunity\Modules\UserRole\UserRolePublicModule;
use UltraCommunity\PostsType\UserRolePostType;
use UltraCommunity\Repository\ActivityRepository;
use UltraCommunity\Repository\BaseRepository;
use UltraCommunity\Repository\GroupRepository;
use UltraCommunity\Repository\UserRepository;

final class UltraCommHelper
{

	public static function getGroupCoverUrl($groupKey, $fallBackToDefault = true)
	{

		if( null === UltraCommUtils::getGroupCoverFilePath($groupEntity = GroupController::getGroupEntityBy($groupKey)) ) {
			return $fallBackToDefault ? self::getGroupDefaultCoverUrl() : null;
		}

		return UltraCommUtils::getGroupCoverBaseUrl($groupEntity->Id) . '/' . $groupEntity->CoverFileName;

	}

	public static function getGroupPictureUrl($groupKey, $fallBackToDefault = true)
	{
		if( null === UltraCommUtils::getGroupPictureFilePath($groupEntity = GroupController::getGroupEntityBy($groupKey)) )
			return $fallBackToDefault ? self::getGroupDefaultPictureUrl() : null;

		return UltraCommUtils::getGroupPictureBaseUrl($groupEntity->Id) . '/' . $groupEntity->PictureFileName;
	}

	public static function getGroupDefaultCoverUrl()
	{
		return MchBasePlugin::getPluginBaseUrl() . '/assets/images/user-default-cover.png';
	}

	public static function getGroupDefaultPictureUrl()
	{
		return MchBasePlugin::getPluginBaseUrl() . '/assets/images/user-groups-default-avatar.png';
	}

	public static function getGroupDisplayName($groupKey, $escaped = true)
	{
		if(null === ($groupEntity = GroupController::getGroupEntityBy($groupKey)))
			return null;

		return $escaped ? \esc_html($groupEntity->Name) : $groupEntity->Name;
	}


	public static function getUserDisplayName($userKey, $escaped = true)
	{
		$userEntity = ($userKey instanceof UserEntity) ? $userKey : UserController::getUserEntityBy($userKey);
		$userDisplayName = UserSettingsPublicModule::getInstance()->getUserDisplayName($userEntity);

		return $escaped ? \esc_html($userDisplayName) : $userDisplayName;
	}

	public static function getUserShortDescription($userKey, $numberOfWords = 25, $escaped = true)
	{
		$userEntity = ($userKey instanceof UserEntity) ? $userKey : UserController::getUserEntityBy($userKey);

		if( empty($userEntity->UserMetaEntity) || empty($userEntity->UserMetaEntity->Description) )
		{
			return null;
		}

		$shortDescription = \wp_specialchars_decode(\stripslashes($userEntity->UserMetaEntity->Description), \ENT_QUOTES);
		$shortDescription = \wp_trim_words($shortDescription, $numberOfWords);

//		$shortDescription  = force_balance_tags( html_entity_decode( wp_trim_words( htmlentities( wpautop(get_the_content()) ), 100, $more ) ) );
		//force_balance_tags(html_entity_decode(wp_trim_words(htmlentities($text))));

//		wp_kses_decode_entities(html_entity_decode(wp_trim_words(htmlentities($text)))))

		//wp_kses_decode_entities(html_entity_decode(htmlentities($text)));


		return $escaped ? \esc_html($shortDescription) : $shortDescription;

	}


	public static function getUserAvatarUrl($userKey, $size = 0)
	{
		if( null === ($userKey = BaseRepository::getUserIdFromKey($userKey) ))
			return self::getUserDefaultAvatarUrl();

		$avatarUrl =  \get_avatar_url($userKey, array('size' => empty($size) ? 200 : (int)$size ));

		return (false !== $avatarUrl) ? esc_url($avatarUrl) : self::getUserDefaultAvatarUrl();

	}

	public static function getUserDefaultAvatarUrl($size = 0)
	{
		if(!MchUtils::isNullOrEmpty($defaultAvatarUrl = UserSettingsPublicModule::getInstance()->getOption(UserSettingsAdminModule::OPTION_DEFAULT_AVATAR_URL)))
			return $defaultAvatarUrl;

		return MchBasePlugin::getPluginBaseUrl() . '/assets/images/user-default-avatar.png';
	}

	public static function getUserDefaultCoverUrl()
	{
		if(!MchUtils::isNullOrEmpty($defaultCoverUrl = UserSettingsPublicModule::getInstance()->getOption(UserSettingsAdminModule::OPTION_DEFAULT_COVER_URL)))
			return $defaultCoverUrl;

		return MchBasePlugin::getPluginBaseUrl() . '/assets/images/user-default-cover.png';
	}

	public static function getUserProfileCoverUrl(UserEntity $userEntity, $fallBackToDefault = true)
	{
		if( (null === ($profileCoverFilePath = UltraCommUtils::getUserProfileCoverFilePath($userEntity))) )
			return $fallBackToDefault ? self::getUserDefaultCoverUrl() : null;

		return UltraCommUtils::getProfileCoverBaseUrl($userEntity) . '/' . MchFileUtils::getFileBaseName($profileCoverFilePath);

	}


	public static function getUserAccountConfirmationUrl($userId)
	{
		if(MchUtils::isNullOrEmpty($userToken = UserRepository::getUserRegistrationEmailToken($userId)))
			return null;

		$url = add_query_arg(array('user-id' => $userId, LoginPage::USER_ACTIVATE_ACCOUNT_TOKEN_QUERY_ARG => $userToken), FrontPageController::getPageUrl(FrontPageController::PAGE_LOGIN));

		return esc_url($url);
	}

	public static function getUserResetPasswordUrl($userId)
	{
		if(!MchWpUtils::isWPUser($wpUser = WpUserRepository::getUserById($userId)))
			return null;

		$url = add_query_arg(array('user-id' => $userId, ForgotPasswordPage::USER_RESET_PASSWORD_TOKEN_QUERY_ARG => get_password_reset_key($wpUser)), FrontPageController::getPageUrl(FrontPageController::PAGE_FORGOT_PASSWORD));

		return esc_url($url);
	}

	/**
	 * @param string|int|\WP_User $userKey
	 */
	public static function changeUserStatus($userKey, $newUserStatusId)
	{
		UserController::changeUserStatus($userKey, $newUserStatusId);
	}

	/**
	 * @param $userKey
	 *
	 * @return SocialNetworkUrlField[]
	 */
	public static function getUserSocialNetworksProfileFields($userKey)
	{
		$userEntity = UserController::getUserEntityBy($userKey);

		$arrSocialNetworkFields = array();
		foreach (UserController::getUserProfileFormFields($userEntity) as $profileFormField)
		{
			if(!$profileFormField instanceof SocialNetworkUrlField)
				continue;

			$socialNetworkUrl = UserController::getUserProfileFormFieldValue($userEntity, $profileFormField);
			if(empty($socialNetworkUrl) || empty($profileFormField->NetworkId)) {
				continue;
			}

			$profileFormField->Value = $socialNetworkUrl;
			$arrSocialNetworkFields[] = $profileFormField;

//			$cssOutput = '';
//			foreach ($profileFormField->getAllNetworks() as $network)
//			{
//				$iconKey = $profileFormField->getFontAwesomeClass($network->Id);
//				$bgColor = $network->IconBgColor;
//
//				$cssOutput .= "a.uc-social-network:not(.uc-social-network-colored).uc-sn-$iconKey:hover, .uc-social-network-colored.uc-sn-$iconKey {
//                                background-color: $bgColor
//                                }\n";
//
//				$cssOutput .= "a.uc-social-network-colored.uc-sn-$iconKey:hover{
//                                background-color: lighten($bgColor, 6%);
//							}\n\n";
//			}
//
//			echo $cssOutput;exit;
		}

		return $arrSocialNetworkFields;
	}

	/**
	 * @param $userInfo UserEntity|\WP_User| int userId | string userEmail| string userName $userEntity
	 *
	 * @return null
	 */
	public static function  getUserProfileUrl($userInfo, $profileSectionSlug = null, $escaped = true)
	{
		$userNiceName = null;
		switch (true)
		{
			case !empty($userInfo->NiceName)     : $userNiceName = $userInfo->NiceName;break; // UserEntity
			case !empty($userInfo->user_nicename): $userNiceName = $userInfo->user_nicename;break; // WP_User

			default :
				$userEntity = UserRepository::getUserEntityBy($userInfo);
				empty($userEntity->NiceName) ?: $userNiceName = $userEntity->NiceName;
				break;
		}

		if(null === $userNiceName)
			return null;

		$arrCustomTabsSections = UserProfileAppearancePublicModule::getAllCustomTabProfileSections();
		if(isset($arrCustomTabsSections[$profileSectionSlug]) && !empty($arrCustomTabsSections[$profileSectionSlug]->PostUrl))
		{
			return $escaped ? esc_url($arrCustomTabsSections[$profileSectionSlug]->PostUrl) : $arrCustomTabsSections[$profileSectionSlug]->PostUrl;
		}

		if(isset($profileSectionSlug))
		{
			$profileSectionSlug = (null !== UserProfileAppearancePublicModule::getUserProfileSectionNameBySlug($profileSectionSlug)) ? $profileSectionSlug . '/' : '';
		}

		$pageUrl = FrontPageSettingsPublicModule::getUserProfilePageUrl() . $profileSectionSlug . $userNiceName . '/';


		return $escaped ? esc_url($pageUrl) : $pageUrl;

	}

	public static function  getGroupUrl($groupKey, $groupSectionSlug = null, $pageNumber = 1, $escaped = true)
	{

		$groupSlug = null; $pageNumber = (int)$pageNumber;
		switch (true)
		{
			case !empty($groupKey->Slug)     : $groupSlug = $groupKey->Slug;break; // GroupEntity
			case !empty($groupKey->post_name): $groupSlug = $groupKey->post_name;break; // WP_Post

			default :
				$groupEntity = GroupRepository::getGroupEntityBy($groupKey);
				empty($groupEntity->Slug) ?: $groupSlug = $groupEntity->Slug;
				break;
		}

		if(null === $groupSlug)
			return null;


		//echo "GroupSlug -> $groupSlug\n";
		
		if(!empty($groupSectionSlug) && $pageNumber > 1)
		{
			$groupSectionSlug .= "/page/$pageNumber";
		}

		$pageUrl = FrontPageSettingsPublicModule::getGroupsDirectoryPageUrl() . "$groupSlug/$groupSectionSlug/";
		$pageUrl = rtrim(esc_url($pageUrl) , '/\\' ) . '/';

		return $escaped ? esc_url($pageUrl) : $pageUrl;

	}

	public static function getGroupSettingsUrl($groupKey, $escaped = true)
	{
		if(null === ($groupEntity = GroupController::getGroupEntityBy($groupKey)))
			return null;

		if(null === ($adminUserEntity = UserController::getUserEntityBy($groupEntity->AdminUserId)))
			return null;

		return FrontPageController::getUserSettingsPageUrlBySection(UserSettingsPage::SETTINGS_SECTION_GROUPS_EDIT_GROUP, $adminUserEntity->NiceName, 0, $escaped);

	}


	public static function getUserProfileFormPublicInstance(UserEntity $userEntity = null)
	{

		if(empty($userEntity))
			return null;

		$arrUserRolePostType = UserRoleController::getUserRolePostTypes($userEntity);

		usort($arrUserRolePostType, function($userRolePostType1, $userRolePostType2){ // descending by role priority
			return $userRolePostType2->Priority - $userRolePostType1->Priority;
		});

		foreach($arrUserRolePostType as $userRolePostType)
		{
			foreach (PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_PROFILE_FORM) as $profileFormPostType)
			{
				if(null === ($profileFormPublicInstance = PostTypeController::getAssociatedPublicModuleInstance($profileFormPostType)))
					continue;

				if(!in_array($userRolePostType->PostId, (array)$profileFormPublicInstance->getOption(UserProfileFormAdminModule::OPTION_ASSIGNED_USER_ROLES)))
					continue;

				return $profileFormPublicInstance;
			}

		}

		if(null === ($defaultUserRolePublicInstance = self::getDefaultUserRolePublicInstance()))
			return null;

		foreach (PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_PROFILE_FORM) as $profileFormPostType)
		{
			if(null === ($profileFormPublicInstance = PostTypeController::getAssociatedPublicModuleInstance($profileFormPostType)))
				continue;

			if(!in_array($defaultUserRolePublicInstance->getCustomPostTypeId(), (array)$profileFormPublicInstance->getOption(UserProfileFormAdminModule::OPTION_ASSIGNED_USER_ROLES)))
				continue;

			return $profileFormPublicInstance;

		}

		return null;


//		if(null === ($userRolePublicInstance = self::getUserRolePublicInstanceByUserInfo($userEntity))){
//			if(null === ($userRolePublicInstance =  self::getDefaultUserRolePublicInstance()))
//				return null;
//		}
//
//		$defaultUserRolePublicInstance    =  self::getDefaultUserRolePublicInstance();
//		$defaultProfileFormPublicInstance = null;
//		foreach (PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_PROFILE_FORM) as $profileFormPostType)
//		{
//			if(null === ($profileFormPublicInstance = PostTypeController::getAssociatedPublicModuleInstance($profileFormPostType)))
//				continue;
//
//			if($profileFormPublicInstance->getOption(UserProfileFormAdminModule::OPTION_USER_ROLE_CUSTOM_POST_ID) == $userRolePublicInstance->getCustomPostTypeId())
//				return $profileFormPublicInstance;
//
//			if($profileFormPublicInstance->getOption(UserProfileFormAdminModule::OPTION_USER_ROLE_CUSTOM_POST_ID) == $defaultUserRolePublicInstance->getCustomPostTypeId())
//				$defaultProfileFormPublicInstance = $profileFormPublicInstance;
//		}
//
//		return $defaultProfileFormPublicInstance;


	}

	/**
	 * @return UserRolePublicModule|null
	 */
	public static function getDefaultUserRolePublicInstance()
	{
		return PostTypeController::getAssociatedPublicModuleInstance(UserRoleController::getDefaultUserRolePostType());
	}

	/**
	 * @return UserRolePublicModule|null
	 */
//	public static function getUserRolePublicInstanceByUserInfo($userInfo, $defaultsToBuiltIn = true)
//	{
//		$wpUser = (($userInfo instanceof \WP_User) && !empty($userInfo->roles) ) ? $userInfo : null;
//
//		if(null === $wpUser)
//		{
//			if(($userInfo instanceof UserEntity) && !empty($userInfo->Id))
//			{
//				$wpUser = WpUserRepository::getUserById($userInfo->Id);
//			}
//			else
//			{
//				$userEntity = UserRepository::getUserEntityBy($userInfo);
//				$wpUser = empty($userEntity->Id) ? null :  WpUserRepository::getUserById($userEntity->Id);
//			}
//		}
//
//		if(null === $wpUser)
//			return null;
//
////		if(empty($wpUser->roles)){
////			return null;
////		}
//
//		$wpUser->roles   = (array)$wpUser->roles;
//		$arrFlippedRoles = array_flip($wpUser->roles);
//
//		$arrUltraCommRoles = UserRoleController::getAllRegisteredUserRoles();
//
//		foreach(array_intersect_key($arrFlippedRoles, $arrUltraCommRoles) as $userRoleSlug => $v)
//		{
//			if(!isset($arrUltraCommRoles[$userRoleSlug]))
//				continue;
//
//			foreach (PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_ROLE) as $userRolePostType)
//			{
//				if(null === ($publicModuleInstance = PostTypeController::getAssociatedPublicModuleInstance($userRolePostType)))
//					continue;
//
//				if($publicModuleInstance->getOption(UserRoleAdminModule::OPTION_ROLE_SLUG) !== $userRoleSlug)
//					continue;
//
//				isset($arrFlippedRoles[$userRoleSlug]) ?: $wpUser->add_role($userRoleSlug);
//
//				//\in_array($userRoleSlug, $wpUser->roles) ?: $wpUser->add_role($userRoleSlug);
//
//				return $publicModuleInstance;
//			}
//
//		}
//
//		$defaultUserRolePublicInstance = self::getDefaultUserRolePublicInstance();
//		$defaultRoleSlug = (null !== $defaultUserRolePublicInstance) ? $defaultUserRolePublicInstance->getOption(UserRoleAdminModule::OPTION_ROLE_SLUG) : null;
//
//		if($defaultRoleSlug && !isset($arrFlippedRoles[$defaultRoleSlug]))
//		{
//			$wpUser->add_role($defaultRoleSlug);
//		}
//
//		return $defaultsToBuiltIn ? $defaultUserRolePublicInstance : null;
//
//	}


	/**
	 * @param BaseFormAdminModule $formAdminModuleInstance
	 *
	 * @return UserRolePostType[]
	 */
	public static function getUnAssignedUserRolesForNewForm(CustomPostType $formCustomPostType)
	{
		$arrPostTypeIds     = array();

		foreach (PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_ROLE) as $publishedPostType){
			$arrPostTypeIds[(int)$publishedPostType->PostId] = $publishedPostType;
		}

		foreach (PostTypeController::getPublishedPosts($formCustomPostType->PostType) as $publishedPostType)
		{
			if (null === ($admModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($publishedPostType)))
				continue;

			$usedPostTypeId = (int)$admModuleInstance->getOption(BaseFormAdminModule::OPTION_USER_ROLE_CUSTOM_POST_ID);
			if (! isset($arrPostTypeIds[$usedPostTypeId]) )
				continue;

			unset($arrPostTypeIds[$usedPostTypeId]);
		}

		return $arrPostTypeIds;

	}

//	public static function removeUserRegistrationHooks()
//	{
////		foreach(array('user_register', 'profile_update') as $userRegistrationHook){
////			remove_action($userRegistrationHook, array('\UltraCommunity\Tasks\UserRolesMapperTask', 'synchronizeUserRoles'), 10, 1);
////		}
//
//	}


	public static function initCommonHooks()
	{
		self::initUserAvatarHooks();
		self::initCommentsHooks();
		self::initCustomPostsHooks();
	}

	public static function getUserDisplayablePostTypes($userKey = null)
	{
		return \array_unique((array)MchWpUtils::applyFilters(UltraCommHooks::FILTER_USER_DISPLAYABLE_POST_TYPES,  array('post'), BaseRepository::getUserIdFromKey($userKey)));
	}


	private static function initCustomPostsHooks()
	{
//		MchWpUtils::addFilterHook(UltraCommHooks::FILTER_USER_DISPLAYABLE_POST_TYPES, function($arrDisplayablePostTypes = array(), $userKey = null){
//			return empty($arrDisplayablePostTypes) ? array('post') : array_unique((array)$arrDisplayablePostTypes);
//		}, 1, 2);



		MchWpUtils::addFilterHook('post_type_link', function ($postUrl, \WP_Post $wpPost){

			if($wpPost->post_type !== PostTypeController::POST_TYPE_ACTIVITY)
				return $postUrl;

			static $arrPermaLinks = array();
			if(isset($arrPermaLinks[$wpPost->ID]))
				return $arrPermaLinks[$wpPost->ID];

			$activityEntity = new ActivityEntity();
			$activityEntity->StatusId   = ActivityEntity::ACTIVITY_STATUS_ACTIVE;
			$activityEntity->PostTypeId = (int)$wpPost->ID;

			$arrActivities =  ActivityRepository::findByEntityProperties($activityEntity, 1, 1, array());
			if(!isset($arrActivities[0]))
				return $postUrl;

			$activityEntity = $arrActivities[0]; unset($arrActivities);

			if($activityEntity->TargetTypeId === ActivityEntity::ACTIVITY_TARGET_TYPE_USER)
			{
				$wpUser = new \WP_User($activityEntity->TargetId);
				if(!$wpUser->exists())
					return $postUrl;

				$postUrl =  self::getUserProfileUrl($wpUser, UserProfileAppearanceAdminModule::PROFILE_SECTION_ACTIVITY) . $activityEntity->ActivityId;

				$arrPermaLinks[$wpPost->ID] = $postUrl;

				return $postUrl;
			}


			if(! ($groupCustomPost = \WP_Post::get_instance($activityEntity->TargetId)) )
				return $postUrl;

			$postUrl =  self::getGroupUrl($groupCustomPost, GroupProfileAppearanceAdminModule::PROFILE_SECTION_ACTIVITY) . $activityEntity->ActivityId;

			$arrPermaLinks[$wpPost->ID] = $postUrl;


			return $postUrl;


		}, PHP_INT_MAX, 2);

	}

	private static function initCommentsHooks()
	{
		MchWpUtils::addActionHook('pre_get_comments', function($wpCommentQuery){

			isset($wpCommentQuery->query_vars['type'])     ?: $wpCommentQuery->query_vars['type'] = array();
			isset($wpCommentQuery->query_vars['type__in']) ?: $wpCommentQuery->query_vars['type__in'] = array();

			isset($wpCommentQuery->query_vars['type__not_in'])    ?: $wpCommentQuery->query_vars['type__not_in'] = array();
			is_array($wpCommentQuery->query_vars['type__not_in']) ?: $wpCommentQuery->query_vars['type__not_in'] = array($wpCommentQuery->query_vars['type__not_in']);

			$arrAllowedCommentTypes    = array_flip(array_merge( (array) $wpCommentQuery->query_vars['type'], (array) $wpCommentQuery->query_vars['type__in'] ));
			$arrNotAllowedCommentTypes = array_flip(array_merge(array(PostTypeController::POST_TYPE_ACTIVITY, PostTypeController::POST_TYPE_USER_REVIEW), $wpCommentQuery->query_vars['type__not_in']));

			$wpCommentQuery->query_vars['type__not_in'] = array_keys(array_diff_key($arrNotAllowedCommentTypes, $arrAllowedCommentTypes));


		}, PHP_INT_MAX);


		MchWpUtils::addFilterHook('pre_comment_approved', function($isApproved, $arrCommentData ){

			if(empty($arrCommentData['comment_type']))
				return $isApproved;

			switch($arrCommentData['comment_type'])
			{
				case PostTypeController::POST_TYPE_ACTIVITY    :
				case PostTypeController::POST_TYPE_USER_REVIEW :
					return 1;

			}

			return $isApproved;


		}, PHP_INT_MAX, 2);

		foreach(array('comment_notification_recipients', 'comment_moderation_recipients') as $commentHook)
		{
			MchWpUtils::addFilterHook($commentHook, function($arrEmails, $commentId ){

				$comment = get_comment( $commentId );

				if ( empty( $comment->comment_type ) )
					return $arrEmails;

				switch($comment->comment_type)
				{
					case PostTypeController::POST_TYPE_ACTIVITY    :
					case PostTypeController::POST_TYPE_USER_REVIEW :
						return array();

				}

				return $arrEmails;

			}, PHP_INT_MAX, 2);

		}

	}


	private static function initUserAvatarHooks()
	{
		$canInitAvatarFilter = true;

		if(MchWpUtils::isUserInDashboard()){
			MchWpUtils::addFilterHook('avatar_defaults', function($avatarDefaults) use(&$canInitAvatarFilter){
				$canInitAvatarFilter = false;
				return $avatarDefaults;
			});
		}

		MchWpUtils::addFilterHook('pre_get_avatar_data',  function($arrAvatarArgs, $userKey) use(&$canInitAvatarFilter){

			static $arrCachedUrls = array(); static $firstTimeCalled = true;

			isset($arrCachedUrls['default']) ?: $arrCachedUrls['default'] = self::getUserDefaultAvatarUrl();
			$arrAvatarArgs['default'] = $arrCachedUrls['default'];

			$userId = UserRepository::getUserIdFromKey($userKey);
			$avatarSize =  empty($arrAvatarArgs['size']) ? 96 : (int)$arrAvatarArgs['size'];

			if(!empty($userId) && isset($arrCachedUrls[$userId][$avatarSize]))
			{
				$arrAvatarArgs['url'] = $arrCachedUrls[$userId][$avatarSize];
				return $arrAvatarArgs;
			}

			if(null !== ($userEntity = UserRepository::getUserEntityBy($userId)))
			{
				if(null !== ($avatarFilePath = UltraCommUtils::getUserAvatarFilePath($userEntity, $avatarSize, true)))
				{
					$arrAvatarArgs['url']   = esc_url(UltraCommUtils::getAvatarBaseUrl($userEntity) . '/' . wp_basename($avatarFilePath));
					$arrCachedUrls[$userId][$avatarSize] = $arrAvatarArgs['url'];
				}
			}


			$firstTimeCalled && MchWpUtils::addFilterHook('get_avatar_data', function($arrAvatarData, $userKey) use(&$canInitAvatarFilter, &$arrCachedUrls){

				if(!$canInitAvatarFilter)
					return $arrAvatarData;

				$userId = UserRepository::getUserIdFromKey($userKey);
				$avatarSize =  empty($arrAvatarData['size']) ? 96 : (int)$arrAvatarData['size'];

				if(!empty($userId) && isset($arrCachedUrls[$userId][$avatarSize]))
				{
					$arrAvatarData['url'] = $arrCachedUrls[$userId][$avatarSize];
					return $arrAvatarData;
				}

				if(null === ($userEntity = UserRepository::getUserEntityBy($userId)))
					return $arrAvatarData;

				$avatarFilePath = UltraCommUtils::getUserAvatarFilePath($userEntity, $avatarSize, true);

				if(null !== $avatarFilePath)
				{
					$arrAvatarData['url'] = esc_url(UltraCommUtils::getAvatarBaseUrl($userEntity) . '/' . wp_basename($avatarFilePath));
				}
				elseif(!UserSettingsPublicModule::isUserGravatarUrlEnabled())
				{
					$arrAvatarData['url'] = UltraCommHelper::getUserDefaultAvatarUrl();
				}

				empty($arrAvatarData['url']) ?: $arrCachedUrls[$userId][$avatarSize] = $arrAvatarData['url'];

				return $arrAvatarData;

			}, PHP_INT_MAX, 2);

			$firstTimeCalled = false;
			return $arrAvatarArgs;

		}, PHP_INT_MAX, 2);

		MchWpUtils::addFilterHook('get_avatar', function($avatar, $userKey, $size, $default, $alt) use(&$canInitAvatarFilter){

			if(!$canInitAvatarFilter || null === ($userEntity = UserRepository::getUserEntityBy($userKey)))
				return $avatar;

			if(null === ($avatarUrl = self::getUserAvatarUrl($userEntity, $size)))
				return $avatar;

			$avatar = "<img alt='" . esc_attr($alt) . "' src='" . esc_url($avatarUrl) . "' class='avatar avatar-" . esc_attr($size) . " photo' height='" . esc_attr($size) . "' width='" . esc_attr($size) . "' />";

			return $avatar;

		}, PHP_INT_MAX, 5);

	}

	public static function getPostFontAwesomeIcon($postId)
	{
		switch (MchWpUtils::getPostFormat($postId))
		{
			case 'standard': return "file-text-o";
			case 'video':    return "video-camera";
			case 'image':    return "camera";
			case 'status':   return "file-text";
			case 'quote':    return "quote-right";
			case 'link':     return "unlink";
			case 'gallery':  return "film";
			case 'audio':    return "volume-up";
		}

		return "pencil";

	}

	private function __construct(){
	}
	private function __clone(){
	}
	private function __wakeup(){
	}
}