<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultra-community.com)
 */

namespace UltraCommunity\Controllers;


use UltraCommunity\FrontPages\BasePage;
use UltraCommunity\FrontPages\ForgotPasswordPage;
use UltraCommunity\FrontPages\GroupProfilePage;
use UltraCommunity\FrontPages\GroupsDirectoryPage;
use UltraCommunity\FrontPages\LoginPage;
use UltraCommunity\FrontPages\MembersDirectoryPage;
use UltraCommunity\FrontPages\RegisterPage;
use UltraCommunity\FrontPages\UserProfilePage;
use UltraCommunity\FrontPages\UserSettingsPage;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\CustomPostType;
use UltraCommunity\MchLib\WordPress\Repository\WpPostRepository;
use UltraCommunity\Modules\BaseAdminModule;
use UltraCommunity\Modules\Forms\RegisterForm\RegisterFormAdminModule;
use UltraCommunity\Modules\Forms\RegisterForm\RegisterFormPublicModule;
use UltraCommunity\Modules\GeneralSettings\FrontPage\FrontPageSettingsPublicModule;
use UltraCommunity\PostsType\ForgotPasswordFormPostType;
use UltraCommunity\PostsType\GroupsDirectoryPostType;
use UltraCommunity\PostsType\GroupPostType;
use UltraCommunity\PostsType\LoginFormPostType;
use UltraCommunity\PostsType\MembersDirectoryPostType;
use UltraCommunity\PostsType\RegisterFormPostType;
use UltraCommunity\PostsType\UserProfileFormPostType;
use UltraCommunity\UltraCommException;
use UltraCommunity\UltraCommHelper;
use UltraCommunity\UltraCommHooks;

final class FrontPageController
{
	CONST PAGE_LOGIN             = 1;
	CONST PAGE_USER_PROFILE      = 2;
	CONST PAGE_REGISTRATION      = 3;
	CONST PAGE_FORGOT_PASSWORD   = 4;
	CONST PAGE_MEMBERS_DIRECTORY = 5;
	CONST PAGE_GROUPS_DIRECTORY  = 6;
	CONST PAGE_GROUP_PROFILE     = 7;

	private static $activePage = null;

	public static function getAllPageKeys()
	{
		return array(self::PAGE_USER_PROFILE, self::PAGE_MEMBERS_DIRECTORY, self::PAGE_LOGIN,  self::PAGE_REGISTRATION, self::PAGE_FORGOT_PASSWORD, self::PAGE_GROUPS_DIRECTORY, self::PAGE_GROUP_PROFILE);
	}

	public static function canUseGlobalTemplate()
	{
		return FrontPageSettingsPublicModule::canUseGlobalPageTemplate();
	}

	public static function getLogInPageUrl()
	{
		return self::getPageUrl(self::PAGE_LOGIN);
	}

	public static function getLogOutPageUrl()
	{
		return self::getLogInPageUrl() . '/?uc-action=logout';
	}

	public static function redirectToLogInPage()
	{
		MchWpUtils::logOutCurrentUser(self::getLogInPageUrl());
	}

	public static function redirectToHomePage()
	{
		MchWpUtils::redirectToUrl(home_url('/'));
	}

	public static function redirectTo404Page()
	{
		MchWpUtils::redirectTo404();
	}

//	public static function redirectWithError($redirectPageUrl, $errorId)
//	{
//		MchWpUtils::redirectToUrl( empty($errorId) ? $redirectPageUrl : add_query_arg(BasePage::PAGE_ERROR_MESSAGE_QUERY_ARG, $errorId, $redirectPageUrl));
//	}
//
//	public static function redirectWithSuccess($redirectPageUrl, $messageId)
//	{
//		MchWpUtils::redirectToUrl( empty($messageId) ? $redirectPageUrl : add_query_arg(BasePage::PAGE_SUCCESS_MESSAGE_QUERY_ARG, $messageId, $redirectPageUrl));
//	}


	public static function getDefaultRegisterPageUrl()
	{
		return self::getPageUrl(self::PAGE_REGISTRATION);
	}

	public static function getRegisterPageUrlByCustomPostType(CustomPostType $registerCustomPostType = null)
	{
		if( null === ($publicModuleInstance = PostTypeController::getAssociatedPublicModuleInstance($registerCustomPostType)) ){
			return null;
		}

		if(!$publicModuleInstance instanceof RegisterFormPublicModule)
			return null;

		$pagePermaLink = get_permalink((int)$publicModuleInstance->getOption(RegisterFormAdminModule::OPTION_ASSIGNED_PAGE_ID));

		return isset($pagePermaLink[0]) ? rtrim(esc_url($pagePermaLink) , '/\\' ) . '/' : null;

	}

	public static function getPageUrl($pageKey)
	{
		switch($pageKey)
		{
			case self::PAGE_USER_PROFILE :
				return FrontPageSettingsPublicModule::getUserProfilePageUrl();
			case self::PAGE_LOGIN :
				return FrontPageSettingsPublicModule::getLoginPageUrl();
			case self::PAGE_REGISTRATION :
				return FrontPageSettingsPublicModule::getRegistrationPageUrl();
			case self::PAGE_FORGOT_PASSWORD :
				return FrontPageSettingsPublicModule::getForgotPasswordPageUrl();
			case self::PAGE_MEMBERS_DIRECTORY :
				return FrontPageSettingsPublicModule::getMembersDirectoryPageUrl();
			case self::PAGE_GROUPS_DIRECTORY :
				return FrontPageSettingsPublicModule::getGroupsDirectoryPageUrl();
		}

		return null;
	}

	public static function getLoggedInUserProfileUrl()
	{
		return UltraCommHelper::getUserProfileUrl(UserController::getLoggedInUser());
	}


	public static function getFrontPageInstance($pageKey)
	{
		switch($pageKey)
		{
			case self::PAGE_USER_PROFILE :
				return new UserProfilePage();
			case self::PAGE_MEMBERS_DIRECTORY:
				return new MembersDirectoryPage();
			case self::PAGE_GROUP_PROFILE :
				return new GroupProfilePage();
			case self::PAGE_GROUPS_DIRECTORY :
				return new GroupsDirectoryPage();
			case self::PAGE_LOGIN :
				return new LoginPage();
			case self::PAGE_REGISTRATION :
				return new RegisterPage();
			case self::PAGE_FORGOT_PASSWORD :
				return new ForgotPasswordPage();

		}

		return null;

	}

	/**
	 * @param $postTypeId
	 *
	 * @return null|ForgotPasswordPage|LoginPage|RegisterPage|MembersDirectoryPage
	 */
	public static function getPageInstanceByCustomPostTypeId($postTypeId)
	{
		$customPostType = PostTypeController::getPostTypeInstanceByPostId((int)$postTypeId);
		$frontPageInstance = null;
		switch(true)
		{
			case ($customPostType instanceof LoginFormPostType) :
				$frontPageInstance = self::getFrontPageInstance(self::PAGE_LOGIN);
				break;

			case ($customPostType instanceof RegisterFormPostType) :
				$frontPageInstance = self::getFrontPageInstance(self::PAGE_REGISTRATION);
				break;

			case ($customPostType instanceof ForgotPasswordFormPostType) :
				$frontPageInstance = self::getFrontPageInstance(self::PAGE_FORGOT_PASSWORD);
				break;

			case ($customPostType instanceof MembersDirectoryPostType) :
				$frontPageInstance = self::getFrontPageInstance(self::PAGE_MEMBERS_DIRECTORY);
				break;

			case ($customPostType instanceof UserProfileFormPostType) :
				$frontPageInstance = self::getFrontPageInstance(self::PAGE_USER_PROFILE);
				break;

			case ($customPostType instanceof GroupsDirectoryPostType) :
				$frontPageInstance = self::getFrontPageInstance(self::PAGE_GROUPS_DIRECTORY);
				break;

			case ($customPostType instanceof GroupPostType) :
				$frontPageInstance = self::getFrontPageInstance(self::PAGE_GROUP_PROFILE);
				break;

		}

		(null === $frontPageInstance) ?: $frontPageInstance->setModuleCustomPostType($customPostType);

		return $frontPageInstance;
	}

	public static function getActivePageUrl()
	{
		switch(true)
		{
			case self::$activePage instanceof UserProfilePage :
				return self::getPageUrl(self::PAGE_USER_PROFILE);

			case self::$activePage instanceof LoginPage :
				return self::getPageUrl(self::PAGE_LOGIN);

			case self::$activePage instanceof RegisterPage :
				return self::getPageUrl(self::PAGE_REGISTRATION);

			case self::$activePage instanceof ForgotPasswordPage :
				return self::getPageUrl(self::PAGE_FORGOT_PASSWORD);

			case self::$activePage instanceof MembersDirectoryPage :
				return self::getPageUrl(self::PAGE_MEMBERS_DIRECTORY);

			case self::$activePage instanceof GroupsDirectoryPage :
				return self::getPageUrl(self::PAGE_GROUPS_DIRECTORY);

			case self::$activePage instanceof GroupProfilePage :
				return self::getPageUrl(self::PAGE_GROUP_PROFILE);

		}

		return null;
	}

	public static function getActivePageTypeId()
	{
		if(null === self::$activePage)
			return 0;

		switch(true)
		{

			case self::$activePage instanceof LoginPage :
				return self::PAGE_LOGIN;

			case self::$activePage instanceof RegisterPage :
				return self::PAGE_REGISTRATION;

			case self::$activePage instanceof ForgotPasswordPage :
				return self::PAGE_FORGOT_PASSWORD;

			case self::$activePage instanceof MembersDirectoryPage :
				return self::PAGE_MEMBERS_DIRECTORY;

			case self::$activePage instanceof GroupsDirectoryPage :
				return self::PAGE_GROUPS_DIRECTORY;

			case self::$activePage instanceof GroupProfilePage :
				return self::PAGE_GROUP_PROFILE;

			case self::$activePage instanceof UserProfilePage :
			case self::$activePage instanceof UserSettingsPage :
				return self::PAGE_USER_PROFILE;

		}

		return 0;
	}

	public static function getUserSettingsPageUrlBySection($section, $userNiceName, $resourceIdentifier = null, $pageNumber = 0, $escaped = true)
	{
		if(empty($userNiceName) || empty($section))
			return null;

		if( null === ($userProfileUrl = FrontPageSettingsPublicModule::getUserProfilePageUrl() ) )
			return null;

		$pageNumber = (int)$pageNumber;

		UserSettingsPage::sectionRequiresIdentifierInUrl($section) ?: $resourceIdentifier = null;

		$userProfileUrl .= 'settings/' . MchWpUtils::formatUrlPath($section);

		empty($resourceIdentifier) ?: $userProfileUrl .= '/' .  $resourceIdentifier ;

		$userProfileUrl .= '/' . MchWpUtils::formatUrlPath($userNiceName) . '/';

		empty($pageNumber) ?: $userProfileUrl .= "page/$pageNumber/";

		return $escaped ? esc_url($userProfileUrl) : $userProfileUrl;

	}


	public static function publishPage($pageType, BaseAdminModule $adminModuleInstance = null)
	{

		if(!MchWpUtils::isAdminLoggedIn())
			return null;

		$arrPageAttributes = array(
			//'post_title'		=> null,
			'post_content'		=> ($adminModuleInstance instanceof BaseAdminModule) &&  $adminModuleInstance->getCustomPostType() ? ShortCodesController::getEmbeddableShortCode($adminModuleInstance->getCustomPostType()) : null,
			//'post_name'			=> null,
			'post_type' 	  	=> 'page',
			'post_status'		=> 'publish',
			'post_author'   	=> get_current_user_id(),
			'comment_status'    => 'closed',
			'ping_status'       => 'closed'
		);

		//$arrPageAttributes['post_content'] = strval('post_content')



//		if(in_array($pageType, array(self::PAGE_MEMBERS_DIRECTORY, self::PAGE_GROUPS_DIRECTORY)))
//		{
//			//$arrPageAttributes['post_content'] = '';
//		}



		$pageTitle = null;
		$arrPageSlugOptions = array();
		switch ($pageType)
		{
			case self::PAGE_LOGIN :
				$pageTitle = __('Login', 'ultra-community');
				$arrPageSlugOptions = array(
						'signin',
						'login',
						'sign-in',
						'log-in'
				);
				break;

			case self::PAGE_REGISTRATION :
				$pageTitle = __('Register', 'ultra-community');
				$arrPageSlugOptions = array(
					'signup',
					'sign-up',
					'register',
				);

				break;


			case self::PAGE_USER_PROFILE :

				$pageTitle = __('User Profile Page', 'ultra-community');
				$arrPageAttributes['post_content'] = '[' . ShortCodesController::ULTRA_COMMUNITY_USER_PROFILE_SHORTCODE . ']';
				$arrPageSlugOptions = array(
					'member',
					'user',
				);

				break;

			case self::PAGE_MEMBERS_DIRECTORY :

				$pageTitle = __('Members', 'ultra-community');
				$arrPageSlugOptions = array(
					'members',
					'users',
				);

				break;

			case self::PAGE_GROUPS_DIRECTORY :

				$pageTitle = __('Groups', 'ultra-community');
				$arrPageSlugOptions = array(
					'groups',
					'users-groups',
					'members-groups'
				);

				break;

			case self::PAGE_FORGOT_PASSWORD :

				$pageTitle = __('Forgot Password', 'ultra-community');
				$arrPageSlugOptions = array(
					'forgot-password',
					'reset-password',
					'password-reset',
				);

				break;


		}

		if(empty($pageTitle))
			return null;

		$arrPageAttributes['post_title'] = $pageTitle;

		foreach ($arrPageSlugOptions as $pageSlugOption)
		{
			if(WpPostRepository::findByPostSlug($pageSlugOption, 'page')){
				continue;
			}

			$arrPageAttributes['post_name'] = $pageSlugOption;
			break;
		}

		if(empty($arrPageAttributes['post_name']) && !empty($arrPageSlugOptions)){
			$arrPageAttributes['post_name'] = $arrPageSlugOptions[0];
		}

		return WpPostRepository::save($arrPageAttributes);

	}


	public static function hasActivePage()
	{
		return null !== self::$activePage;
	}

	public static function setActivePage(BasePage $activePage = null)
	{
		self::$activePage = $activePage;
	}

	/**
	 * @return BasePage
	 */
	public static function getActivePage()
	{
		return self::$activePage;
	}

	private function __construct(){}
}