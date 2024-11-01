<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Controllers;

use UltraCommunity\MchLib\Modules\MchBaseModule;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Modules\GeneralSettings\User\UserSettingsAdminModule;
use UltraCommunity\Modules\GeneralSettings\User\UserSettingsPublicModule;
use UltraCommunity\Modules\UserRole\UserRoleAdminModule;
use UltraCommunity\Modules\UserRole\UserRolePublicModule;
use UltraCommunity\PostsType\CustomTabPostType;
use UltraCommunity\PostsType\ForgotPasswordFormPostType;
use UltraCommunity\PostsType\ActivityPostType;
use UltraCommunity\PostsType\GroupsDirectoryPostType;
use UltraCommunity\PostsType\MembersDirectoryPostType;
use UltraCommunity\PostsType\SocialConnectPostType;
use UltraCommunity\PostsType\GroupPostType;
use UltraCommunity\PostsType\UserSubscriptionPostType;
use UltraCommunity\PostsType\UserReviewPostType;
use UltraCommunity\PostsType\UserRolePostType;
use UltraCommunity\PostsType\LoginFormPostType;
use UltraCommunity\PostsType\RegisterFormPostType;
use UltraCommunity\PostsType\UserProfileFormPostType;
use UltraCommunity\MchLib\WordPress\CustomPostType;
use UltraCommunity\MchLib\WordPress\Repository\WpPostRepository;
use UltraCommunity\UltraCommException;
use UltraCommunity\UltraCommHelper;


final class PostTypeController
{
	CONST POST_TYPE_LOGIN_FORM           = 'uc-login-form';
	CONST POST_TYPE_REGISTER_FORM        = 'uc-register-form';
	CONST POST_TYPE_USER_ROLE            = 'uc-user-role';
	CONST POST_TYPE_USER_PROFILE_FORM    = 'uc-profile-form';
	CONST POST_TYPE_FORGOT_PASSWORD_FORM = 'uc-forgot-pwd-form';
	CONST POST_TYPE_SOCIAL_CONNECT       = 'uc-social-connect';
	CONST POST_TYPE_MEMBERS_DIRECTORY    = 'uc-members-directory';
	//CONST POST_TYPE_USER_SUBSCRIPTION    = 'uc-user-subscription';
	CONST POST_TYPE_CUSTOM_TAB           = 'uc-custom-tab';

	CONST POST_TYPE_ACTIVITY             = 'uc-activity';
	CONST POST_TYPE_GROUPS_DIRECTORY     = 'uc-groups-directory';
	CONST POST_TYPE_GROUP                = 'uc-user-group';
	CONST POST_TYPE_USER_REVIEW          = 'uc-user-review';

	private function __construct()
	{}

	public static function getAllPostTypeKeys()
	{
		return array(
			self::POST_TYPE_USER_PROFILE_FORM,
			self::POST_TYPE_USER_ROLE,
			self::POST_TYPE_GROUP,
			self::POST_TYPE_MEMBERS_DIRECTORY,
			self::POST_TYPE_GROUPS_DIRECTORY,
			//self::POST_TYPE_USER_SUBSCRIPTION,
			self::POST_TYPE_LOGIN_FORM,
			self::POST_TYPE_REGISTER_FORM,
			self::POST_TYPE_FORGOT_PASSWORD_FORM,
			self::POST_TYPE_SOCIAL_CONNECT,
			self::POST_TYPE_ACTIVITY,
			self::POST_TYPE_USER_REVIEW,
			self::POST_TYPE_CUSTOM_TAB,
		);

	}

	/**
	 * @param      $postType
	 * @param bool $skipCache
	 *
	 * @return  \UltraCommunity\MchLib\WordPress\CustomPostType[]
	 */
	public static function getPublishedPosts($postType, $skipCache = false)
	{
		static $arrCashedPosts = array();
		if((!$skipCache) && isset($arrCashedPosts[$postType])){
			return $arrCashedPosts[$postType];
		}

		$arrPostType = array_flip(self::getAllPostTypeKeys());

		if(!isset($arrPostType[$postType]))
			return array();

		unset($arrPostType[self::POST_TYPE_ACTIVITY], $arrPostType[self::POST_TYPE_USER_REVIEW], $arrPostType[self::POST_TYPE_CUSTOM_TAB], $arrPostType[self::POST_TYPE_USER_PROFILE_FORM], $arrPostType[self::POST_TYPE_GROUP]);

		if(!isset($arrPostType[$postType]) && !in_array($postType, array(self::POST_TYPE_ACTIVITY, self::POST_TYPE_USER_REVIEW))){
			$arrPostType[$postType] = true;
		}

		if(!$skipCache && ( ! ($arrPostType = array_diff_key($arrPostType, $arrCashedPosts) ) ))
		{
			return array();
		}

		$arrAdditionalArguments = array(
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'cache_results'          => false,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false, //'suppress_filters' => false,
		);

		foreach(WpPostRepository::findByPostType( array_keys($arrPostType), $arrAdditionalArguments) as $publishedPostType)
		{
			if( ! ($instance = self::getPostTypeInstance($publishedPostType->post_type, $publishedPostType) ) )
				continue;

			isset($arrCashedPosts[$instance->PostType]) ?: $arrCashedPosts[$instance->PostType] = array();

			$arrCashedPosts[$instance->PostType][$instance->PostId] = $instance;

		}

		foreach(array_diff_key($arrPostType, $arrCashedPosts) as $postTypeKey => $index){
			$arrCashedPosts[$postTypeKey] = array();
		}

		return isset($arrCashedPosts[$postType]) ? $arrCashedPosts[$postType] : $arrCashedPosts[$postType] = array();


	}


	public static function getUserRolePostTypeByRoleSlug($userRoleSlug)
	{
		foreach (self::getPublishedPosts(self::POST_TYPE_USER_ROLE) as $customPostUserRole)
		{
			if (null === ($publicModuleInstance = self::getAssociatedPublicModuleInstance($customPostUserRole)))
				continue;

			if($userRoleSlug === $publicModuleInstance->getOption(UserRoleAdminModule::OPTION_ROLE_SLUG))
				return $customPostUserRole;
		}

		return null;
	}

	/**
	 * @return \UltraCommunity\MchLib\WordPress\CustomPostType | null
	 */
	public static function getDefaultMemberUserRole($skipCache = false)
	{
		$defaultUserRoleId = UserSettingsPublicModule::getInstance()->getOption(UserSettingsAdminModule::OPTION_DEFAULT_USER_ROLE);

		if(null !== $defaultUserRoleId)
		{
			foreach ( self::getPublishedPosts( self::POST_TYPE_USER_ROLE, $skipCache ) as $customPostUserRole ){
				if($customPostUserRole->PostId == $defaultUserRoleId)
					return $customPostUserRole;
			}
		}

		foreach ( self::getPublishedPosts( self::POST_TYPE_USER_ROLE, $skipCache ) as $customPostUserRole )
		{
			if ( null === ( $publicModuleInstance = self::getAssociatedPublicModuleInstance( $customPostUserRole ) ) ) {
				continue;
			}

			if(!UserRoleController::isDefaultMemberRole($publicModuleInstance->getOption( UserRoleAdminModule::OPTION_ROLE_SLUG )))
				continue;

			UserSettingsAdminModule::getInstance()->saveOption(UserSettingsAdminModule::OPTION_DEFAULT_USER_ROLE, $customPostUserRole->PostId);

			return $customPostUserRole;
		}

		return null;
	}

	public static function getPostTypeInstanceByPostId($customPostId)
	{
		return self::getMappedPostTypeInstance(WpPostRepository::findByPostId($customPostId));
	}

	public static function getMappedPostTypeInstance(\WP_Post $mappedWpPost = null)
	{
		return isset($mappedWpPost->post_type) ? self::getPostTypeInstance($mappedWpPost->post_type, $mappedWpPost) : null;
	}

	/**
	 * @param $postType
	 * @param \WP_Post|null $mappedWpPost
	 *
	 * @return null|CustomPostType
	 */
	public static function getPostTypeInstance($postType, \WP_Post $mappedWpPost = null)
	{
		switch($postType)
		{
			case self::POST_TYPE_USER_PROFILE_FORM :
				return new UserProfileFormPostType($postType, $mappedWpPost);

			case self::POST_TYPE_GROUP :
				return new GroupPostType($postType, $mappedWpPost);

			case self::POST_TYPE_MEMBERS_DIRECTORY :
				return new MembersDirectoryPostType($postType, $mappedWpPost);

			case self::POST_TYPE_GROUPS_DIRECTORY :
				return new GroupsDirectoryPostType($postType, $mappedWpPost);

			case self::POST_TYPE_ACTIVITY :
				return new ActivityPostType($postType, $mappedWpPost);

			case self::POST_TYPE_USER_ROLE :
				return new UserRolePostType($postType, $mappedWpPost);

			case self::POST_TYPE_USER_REVIEW :
				return new UserReviewPostType($postType, $mappedWpPost);

			case self::POST_TYPE_LOGIN_FORM    :
				return new LoginFormPostType($postType, $mappedWpPost);

			case self::POST_TYPE_REGISTER_FORM :
				return new RegisterFormPostType($postType, $mappedWpPost);

			case self::POST_TYPE_FORGOT_PASSWORD_FORM :
				return new ForgotPasswordFormPostType($postType, $mappedWpPost);

			case self::POST_TYPE_SOCIAL_CONNECT :
				return new SocialConnectPostType($postType, $mappedWpPost);

			case self::POST_TYPE_CUSTOM_TAB :
				return new CustomTabPostType($postType, $mappedWpPost);

//			case self::POST_TYPE_USER_SUBSCRIPTION :
//				return new UserSubscriptionPostType($postType, $mappedWpPost);

		}

		return null;
	}


	public static function getAssociatedAdminModuleInstance(CustomPostType $customPostType = null)
	{
		return self::getAssociatedModuleInstance($customPostType, 1);
	}

	public static function getAssociatedPublicModuleInstance(CustomPostType $customPostType = null)
	{
		return self::getAssociatedModuleInstance($customPostType, 2);
	}


	private static function getAssociatedModuleInstance(CustomPostType $customPostType = null, $instanceTypeId) //1 = admin, 2 = public
	{
		if(empty($customPostType) || empty($customPostType->PostType))
			return null;

		$associatedModuleInstance = null;
		switch($customPostType->PostType)
		{

			case self::POST_TYPE_USER_ROLE :
				if( ! ModulesController::isModuleRegistered(ModulesController::MODULE_USER_ROLE ) )
					return null;

				$associatedModuleInstance =
					(1 === $instanceTypeId )
						? UserRoleAdminModule::getInstance()//ModulesController::getAdminModuleInstance(ModulesController::MODULE_USER_ROLE )
						: UserRolePublicModule::getInstance();//ModulesController::getPublicModuleInstance(ModulesController::MODULE_USER_ROLE);

				break;


			case self::POST_TYPE_USER_PROFILE_FORM :
				if( ! ModulesController::isModuleRegistered(ModulesController::MODULE_USER_PROFILE_FORM ) )
					return null;

				$associatedModuleInstance =
					(1 === $instanceTypeId )
						? ModulesController::getAdminModuleInstance(ModulesController::MODULE_USER_PROFILE_FORM )
						: ModulesController::getPublicModuleInstance(ModulesController::MODULE_USER_PROFILE_FORM);

				break;

			case self::POST_TYPE_MEMBERS_DIRECTORY :
				if( ! ModulesController::isModuleRegistered(ModulesController::MODULE_MEMBERS_DIRECTORY ) )
					return null;

				$associatedModuleInstance =
					(1 === $instanceTypeId )
						? ModulesController::getAdminModuleInstance(ModulesController::MODULE_MEMBERS_DIRECTORY )
						: ModulesController::getPublicModuleInstance(ModulesController::MODULE_MEMBERS_DIRECTORY);

				break;

			case self::POST_TYPE_GROUPS_DIRECTORY :
				if( ! ModulesController::isModuleRegistered(ModulesController::MODULE_GROUPS_DIRECTORY ) )
					return null;

				$associatedModuleInstance =
					(1 === $instanceTypeId )
						? ModulesController::getAdminModuleInstance(ModulesController::MODULE_GROUPS_DIRECTORY )
						: ModulesController::getPublicModuleInstance(ModulesController::MODULE_GROUPS_DIRECTORY);

				break;

			case self::POST_TYPE_LOGIN_FORM :
				if( ! ModulesController::isModuleRegistered(ModulesController::MODULE_LOGIN_FORM ) )
					return null;

				$associatedModuleInstance =
					(1 === $instanceTypeId )
						? ModulesController::getAdminModuleInstance(ModulesController::MODULE_LOGIN_FORM )
						: ModulesController::getPublicModuleInstance(ModulesController::MODULE_LOGIN_FORM);

				break;

			case self::POST_TYPE_REGISTER_FORM :
				if( ! ModulesController::isModuleRegistered(ModulesController::MODULE_REGISTER_FORM ) )
					return null;

				$associatedModuleInstance =
					(1 === $instanceTypeId )
						? ModulesController::getAdminModuleInstance(ModulesController::MODULE_REGISTER_FORM )
						: ModulesController::getPublicModuleInstance(ModulesController::MODULE_REGISTER_FORM);

				break;

			case self::POST_TYPE_FORGOT_PASSWORD_FORM :
				if( ! ModulesController::isModuleRegistered(ModulesController::MODULE_FORGOT_PASSWORD_FORM ) )
					return null;

				$associatedModuleInstance =
						(1 === $instanceTypeId )
								? ModulesController::getAdminModuleInstance(ModulesController::MODULE_FORGOT_PASSWORD_FORM )
								: ModulesController::getPublicModuleInstance(ModulesController::MODULE_FORGOT_PASSWORD_FORM);

				break;

			case self::POST_TYPE_SOCIAL_CONNECT :
				if( ! ModulesController::isModuleRegistered(ModulesController::MODULE_SOCIAL_CONNECT ) )
					return null;

				$associatedModuleInstance =
					(1 === $instanceTypeId )
						? ModulesController::getAdminModuleInstance(ModulesController::MODULE_SOCIAL_CONNECT )
						: ModulesController::getPublicModuleInstance(ModulesController::MODULE_SOCIAL_CONNECT);

				break;


			case self::POST_TYPE_CUSTOM_TAB :
				if( ! ModulesController::isModuleRegistered(ModulesController::MODULE_CUSTOM_TABS ) )
					return null;

				$associatedModuleInstance =
						(1 === $instanceTypeId )
								? ModulesController::getAdminModuleInstance(ModulesController::MODULE_CUSTOM_TABS )
								: ModulesController::getPublicModuleInstance(ModulesController::MODULE_CUSTOM_TABS);

				break;


		}

		if(null === $associatedModuleInstance || (! $associatedModuleInstance instanceof MchBaseModule))
			return null;

		if(null === $associatedModuleInstance->getCustomPostType())
		{
			$associatedModuleInstance->setCustomPostType($customPostType);
			return $associatedModuleInstance;
		}

		if($associatedModuleInstance->getCustomPostType()->PostId == $customPostType->PostId)
			return $associatedModuleInstance;

		$moduleClass = get_class($associatedModuleInstance);

		$associatedModuleInstance = $moduleClass::getInstance(true);

		$associatedModuleInstance->setCustomPostType($customPostType);

		return $associatedModuleInstance;

	}

	public static function publishPostType(CustomPostType $customPostType)
	{

		$arrPostAttrs = $customPostType->toPublishArray();

		//print_r($arrPostAttrs);exit;


		if(!current_user_can('unfiltered_html'))
		{
			add_filter('content_save_pre', 'wp_filter_kses' , 1);

			add_filter('wp_kses_allowed_html', function($arrTags, $context) use($customPostType){

				if((string)$context !== 'content_save_pre') {
					return $arrTags;
				}

				if($customPostType->PostType === PostTypeController::POST_TYPE_ACTIVITY){
					return ModulesController::isModuleRegistered(ModulesController::MODULE_EXTENDED_ACTIVITY)  ?  ActivityController::getActivityPostAllowedTags() : array();
				}

				return $arrTags;

			}, PHP_INT_MAX, 2);
		}

		$postTypeId = WpPostRepository::save($arrPostAttrs);

		return $postTypeId;

	}

	public static function deletePostType(CustomPostType $customPostType = null)
	{
		if(null === $customPostType)
			return;

		try
		{
			$adminModuleInstance = self::getAssociatedAdminModuleInstance($customPostType);

			if(null === $adminModuleInstance){
				//print_r($customPostType);
				throw new UltraCommException(__('Invalid CustomPostType received', 'ultra-community'));
			}

			if($customPostType->PostType === self::POST_TYPE_USER_ROLE)
			{
				if(UserRoleController::isUltraCommUserRole($customPostType->PostSlug))
				{
					wp_roles()->remove_role($customPostType->PostSlug);
				}
				
				if(UserRoleController::isUltraCommUserRole($adminModuleInstance->getOption(UserRoleAdminModule::OPTION_ROLE_SLUG)))
				{
					wp_roles()->remove_role($adminModuleInstance->getOption(UserRoleAdminModule::OPTION_ROLE_SLUG));
				}
			}

			$adminModuleInstance->deleteAllSettingOptions();

			if(!WpPostRepository::delete($adminModuleInstance->getCustomPostTypeId())){
				throw new UltraCommException(__('An error occurred while deleting the post!', 'ultra-community'));
			}

		}
		catch(\Exception $ue)
		{
			throw new UltraCommException($ue->getMessage());
		}

	}

}