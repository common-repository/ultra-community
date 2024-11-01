<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */
namespace UltraCommunity;

use UltraCommunity\Controllers\ActivityController;
use UltraCommunity\Controllers\FrontPageController;
use UltraCommunity\Controllers\GroupController;
use UltraCommunity\Controllers\ModulesController;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Controllers\ShortCodesController;
use UltraCommunity\Controllers\UploadsController;
use UltraCommunity\Controllers\UserController;
use UltraCommunity\Controllers\UserRoleController;
use UltraCommunity\Controllers\WidgetsController;
use UltraCommunity\FrontPages\BasePage;
use UltraCommunity\FrontPages\ForgotPasswordPage;
use UltraCommunity\FrontPages\GroupProfilePage;
use UltraCommunity\FrontPages\GroupsDirectoryPage;
use UltraCommunity\FrontPages\LoginPage;
use UltraCommunity\FrontPages\MembersDirectoryPage;
use UltraCommunity\FrontPages\RegisterPage;
use UltraCommunity\FrontPages\UserProfilePage;
use UltraCommunity\FrontPages\UserSettingsPage;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\Repository\WpPostRepository;
use UltraCommunity\Modules\Appearance\GroupProfile\GroupProfileAppearanceAdminModule;
use UltraCommunity\Modules\Appearance\UserProfile\UserProfileAppearanceAdminModule;
use UltraCommunity\Modules\Forms\RegisterForm\RegisterFormAdminModule;
use UltraCommunity\Modules\GeneralSettings\FrontPage\FrontPageSettingsPublicModule;
use UltraCommunity\Modules\GeneralSettings\Plugin\PluginSettingsPublicModule;
use UltraCommunity\Modules\MembersDirectory\MembersDirectoryAdminModule;
use UltraCommunity\PostsType\GroupsDirectoryPostType;

class RequestHandler
{

	public static function handleRequest()
	{
		self::registerCommonHooks();
	}


	private static function registerCommonHooks()
	{

		MchWpUtils::addActionHook('plugins_loaded', function(){

			ModulesController::initializeAvailableModules();

			switch(true)
			{
				case MchWpUtils::isAjaxRequest()     : AjaxHandler::handleRequest(); break;
				case MchWpUtils::isUserInDashboard() : AdminEngine::getInstance();   break;
				default                              : PublicEngine::getInstance();  break;
			}

			UltraCommHelper::initCommonHooks();
			UserController::initUserHooks();
			GroupController::initGroupHooks();
			ActivityController::initActivityHooks();
			WidgetsController::registerWidgets();
			ShortCodesController::initShortCodesHooks();


		}, 0);

		MchWpUtils::addActionHook('init', function (){

			foreach (PostTypeController::getAllPostTypeKeys() as $postTypeKey){
				\register_post_type($postTypeKey, PostTypeController::getPostTypeInstance($postTypeKey)->getAttributes());
			}

			foreach (ShortCodesController::getAvailableShortCodes() as $availableShortCode){
				\add_shortcode($availableShortCode, array('\UltraCommunity\Controllers\ShortCodesController', 'handleShortCodeOutput'));
			}

			if(UserController::isUserLoggedIn() && MchWpUtils::isUserInDashboard()){
				if(!UserRoleController::currentUserCanAccessWPDashboard() && !MchWpUtils::isAdminLoggedIn()){
					MchWpUtils::redirectToUrl(FrontPageController::getLoggedInUserProfileUrl());
				}
			}
			
		}, 0);


		MchWpUtils::addActionHook('init', function (){

			RequestHandler::registerUserProfileRoutes();
			RequestHandler::registerGroupsRoutes();
			PluginSettingsPublicModule::handleRewriteRulesFlush();
			
		}, PHP_INT_MAX);


		MchWpUtils::addActionHook('wp', function ($wpObject){
			
			ShortCodesController::registerDetectedShortCodes();
			
			FrontPageController::hasActivePage() ?: RequestHandler::detectUserPage($wpObject);
			FrontPageController::hasActivePage() ?: RequestHandler::detectGroupPage($wpObject);
			FrontPageController::hasActivePage() ?: RequestHandler::detectOtherPages($wpObject);
			
			if( !FrontPageController::hasActivePage() )
			{
				return;
			}

			if(FrontPageController::getActivePage()->isAuthenticationRequired() && (! UserController::isUserLoggedIn() )){
				FrontPageController::redirectToLogInPage();
			}


			FrontPageController::getActivePage()->processRequest();


		}, 0);

	}

	public static function detectGroupPage($wpObject)
	{
		
		if( empty($wpObject->query_vars['ultracomm-group-slug'])){
			return;
		}

		GroupController::setProfiledGroup($wpObject->query_vars['ultracomm-group-slug']);
		if(null === GroupController::getProfiledGroup()){
			FrontPageController::redirectTo404Page();
		}

		$activePage = new GroupProfilePage(GroupController::getProfiledGroup()->Id);

		if( !empty($wpObject->query_vars['ultracomm-group-section']) )
		{
			$activePage->setActiveSectionSlug($wpObject->query_vars['ultracomm-group-section']);
		}

		if( !empty($wpObject->query_vars['uc-page-number']) )
		{
			$activePage->setActiveSectionPageNumber($wpObject->query_vars['uc-page-number']);
		}

		if( !empty($wpObject->query_vars['ultracomm-group-section-key']) )
		{
			$activePage->setActiveSectionIdentifier($wpObject->query_vars['ultracomm-group-section-key']);
		}

		if( !empty($wpObject->query_vars['uc-page-number']) && 1 == $wpObject->query_vars['uc-page-number']){
			MchWpUtils::redirectToUrl(UltraCommHelper::getGroupUrl(GroupController::getProfiledGroup(), $activePage->getActiveSectionSlug()));
		}

		if( null === ($customPostType = PostTypeController::getPostTypeInstanceByPostId(GroupController::getProfiledGroup()->Id)) )
			return;

		$activePage->setModuleCustomPostType($customPostType);

		FrontPageController::setActivePage($activePage);

	}

	public static function detectUserPage($wpObject)
	{

		$activePage = null;

		if(!empty($wpObject->query_vars['user-edits-settings']))
		{
			$arrSections = UserSettingsPage::getAllSettingsSections();
			//reset($arrSections);
			
			$userSlug        = empty($wpObject->query_vars['ultracomm-user-slug'])        ? null : $wpObject->query_vars['ultracomm-user-slug'];
			$settingsSection = empty($wpObject->query_vars['ultracomm-settings-section']) ? null : $wpObject->query_vars['ultracomm-settings-section'];
			
			if(isset($arrSections[$userSlug]) && empty($settingsSection)) # /settings/something can be main settings page or a relative url to a particular section without userslug
			{
				$redirectUrl = UserController::isUserLoggedIn() ? FrontPageController::getUserSettingsPageUrlBySection( $userSlug, UserController::getLoggedInUser()->NiceName) : null;
				empty($redirectUrl) ?  FrontPageController::redirectTo404Page() : MchWpUtils::redirectToUrl($redirectUrl);
			}

			if(empty($userSlug) && UserController::isUserLoggedIn()){
				$userSlug = UserController::getLoggedInUser()->NiceName ;
			}

			UserController::setProfiledUserSlug( $userSlug );
			if(!UserController::getProfiledUser())
				return;

			if(empty($settingsSection) || !isset($arrSections[$settingsSection]))
			{
				$settingsSection = array_key_first($arrSections);
				$settingsSectionUrl = FrontPageController::getUserSettingsPageUrlBySection($settingsSection, UserController::getProfiledUser()->NiceName);
				empty($settingsSectionUrl) ? FrontPageController::redirectTo404Page() : MchWpUtils::redirectToUrl($settingsSectionUrl);
			}

			if(!empty($settingsSection) && isset($arrSections[$settingsSection]))
			{
				$activePage = new UserSettingsPage();
				$activePage->setActiveSection($settingsSection);
				$activePage->PageId = FrontPageSettingsPublicModule::getUserProfilePageId();

				if(!empty($wpObject->query_vars['ultracomm-settings-section-resource-identifier']))
				{
					$activePage->setActiveSectionResourceIdentifier($wpObject->query_vars['ultracomm-settings-section-resource-identifier']);
				}

				if(!empty($wpObject->query_vars['uc-page-number']))
				{
					$activePage->setActiveSectionPageNumber($wpObject->query_vars['uc-page-number']);
				}

				FrontPageController::setActivePage($activePage);
			}

		}

		if(FrontPageController::hasActivePage()){
			return;
		}

		if(empty($wpObject->query_vars['ultracomm-user-slug']) ){
			return;
		}

		UserController::setProfiledUserSlug($wpObject->query_vars['ultracomm-user-slug']);
		if(null === UserController::getProfiledUser()){
			FrontPageController::redirectTo404Page();
		}

		if( null === $activePage )
		{

			$activePage = new UserProfilePage(FrontPageSettingsPublicModule::getUserProfilePageId());

			empty($wpObject->query_vars['ultracomm-userprofile-section'])     ?: $activePage->setActiveSectionSlug($wpObject->query_vars['ultracomm-userprofile-section']);
			empty($wpObject->query_vars['ultracomm-userprofile-section-key']) ?: $activePage->setActiveSectionIdentifier($wpObject->query_vars['ultracomm-userprofile-section-key']);
			empty($wpObject->query_vars['uc-page-number'])                    ?: $activePage->setActiveSectionPageNumber($wpObject->query_vars['uc-page-number']);

		}

		FrontPageController::setActivePage($activePage);

	}

	public static function detectOtherPages($wpObject)
	{

		if(!empty($_POST))
		{

			foreach ( FrontPageController::getAllPageKeys() as $frontPageKey )
			{
				if ( null === ( $pageInstance = FrontPageController::getFrontPageInstance( $frontPageKey ) ) || empty( $_POST[ $pageInstance->getPostRequestActionKey() ] ))
					continue;

				FrontPageController::setActivePage( $pageInstance );
				FrontPageController::getActivePage()->setModuleCustomPostType(PostTypeController::getPostTypeInstanceByPostId($_POST[ $pageInstance->getPostRequestActionKey() ]));
				break;
			}

			return;

		}
		
		if(0 === ($queriedObjectId = UltraCommUtils::getQueriedObjectId()) || !is_page())
			return;
		
		if(null === FrontPageController::getActivePage())
		{
			
			switch ($queriedObjectId) {

				case FrontPageSettingsPublicModule::getGroupsDirectoryPageId() :
					$pageNumber = !empty($wpObject->query_vars['uc-is-groups-directory']) && !empty($wpObject->query_vars['uc-page-number']) && MchValidator::isPositiveInteger($wpObject->query_vars['uc-page-number']) ? $wpObject->query_vars['uc-page-number'] : 1;
					FrontPageController::setActivePage(new GroupsDirectoryPage($queriedObjectId, $pageNumber));
					
					foreach (ShortCodesController::getDetectedShortCodesByTagName(ShortCodesController::ULTRA_COMMUNITY_GROUPS_DIRECTORY_SHORTCODE) as $shortCode)
					{
						if(empty($shortCode->Attributes['id']) || !MchValidator::isPositiveInteger($shortCode->Attributes['id']))
							continue;

						if( ($directoryCPT = PostTypeController::getPostTypeInstanceByPostId($shortCode->Attributes['id']))  && ($directoryCPT instanceof GroupsDirectoryPostType) )
						{
							FrontPageController::getActivePage()->setModuleCustomPostType($directoryCPT);
						}
					}
					
					break;

				case FrontPageSettingsPublicModule::getLoginPageId() :
					FrontPageController::setActivePage(new LoginPage($queriedObjectId));
					break;

				case FrontPageSettingsPublicModule::getForgotPasswordPageId() :
					FrontPageController::setActivePage(new ForgotPasswordPage($queriedObjectId));
					break;

				case FrontPageSettingsPublicModule::getUserProfilePageId() :

					if(null === UserController::getProfiledUser())
					{
						if(UserController::isUserLoggedIn())
						{
							MchWpUtils::redirectToUrl(UltraCommHelper::getUserProfileUrl(UserController::getLoggedInUser()));
						}
						else
						{
							FrontPageController::redirectToLogInPage();
						}
					}
					else
					{
						MchWpUtils::redirectToUrl(UltraCommHelper::getUserProfileUrl(UserController::getProfiledUser()));
					}

					break;
			}
		}


		if(!FrontPageController::hasActivePage())
		{
			foreach(PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_MEMBERS_DIRECTORY) as $memberDirectoryCpt)
			{
				if(null === ($publicInstance = PostTypeController::getAssociatedPublicModuleInstance($memberDirectoryCpt)))
					continue;

				$assignedPageId = (int)$publicInstance->getOption(MembersDirectoryAdminModule::OPTION_ASSIGNED_PAGE_ID);

				if(empty($assignedPageId) && $publicInstance->getOption(MembersDirectoryAdminModule::OPTION_IS_DEFAULT_MEMBERS_DIRECTORY)){
					if($adminInstance = PostTypeController::getAssociatedAdminModuleInstance($memberDirectoryCpt)) {
						$adminInstance->saveOption(MembersDirectoryAdminModule::OPTION_ASSIGNED_PAGE_ID, FrontPageSettingsPublicModule::getMembersDirectoryPageId());
						$assignedPageId = FrontPageSettingsPublicModule::getMembersDirectoryPageId();
					}
				}

				if($queriedObjectId !== $assignedPageId)
					continue;

				FrontPageController::setActivePage(new MembersDirectoryPage($queriedObjectId, empty($wpObject->query_vars['paged']) ? 1 : $wpObject->query_vars['paged']));
				FrontPageController::getActivePage()->setModuleCustomPostType($memberDirectoryCpt);

				break;
			}
		}

		if(!FrontPageController::hasActivePage())
		{
			foreach(PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_REGISTER_FORM) as $registerFormPostType)
			{
				if(null === ($publicInstance = PostTypeController::getAssociatedPublicModuleInstance($registerFormPostType)) || $queriedObjectId !== (int)$publicInstance->getOption(RegisterFormAdminModule::OPTION_ASSIGNED_PAGE_ID))
					continue;

				FrontPageController::setActivePage(new RegisterPage($queriedObjectId));
				FrontPageController::getActivePage()->setModuleCustomPostType($registerFormPostType);

				break;
			}

		}


		if( ! FrontPageController::getActivePage() instanceof BasePage )
			return;

		FrontPageController::getActivePage()->PageId = $queriedObjectId;

	}

	public static function registerUserProfileRoutes()
	{
		//flush_rewrite_rules(true);

		$userProfilePageId     = FrontPageSettingsPublicModule::getUserProfilePageId();
		$userProfilePageSlug   = FrontPageSettingsPublicModule::getUserProfilePageSlug();

		$userActivitySectionSlug = UserProfileAppearanceAdminModule::PROFILE_SECTION_ACTIVITY;

		add_rewrite_tag("%ultracomm-settings-section%", '([^/]+)');
		add_rewrite_tag("%ultracomm-settings-section-resource-identifier%", '([^/]+)');
		add_rewrite_tag('%user-edits-settings%', 'editsettings');

		add_rewrite_rule($userProfilePageSlug . "/settings/([^/]+)/([^/]+)/([^/]+)(/page/([0-9]+)?)?/?$", "index.php?page_id=$userProfilePageId&user-edits-settings=1&ultracomm-settings-section=\$matches[1]&ultracomm-settings-section-resource-identifier=\$matches[2]&ultracomm-user-slug=\$matches[3]&uc-page-number=\$matches[5]", 'top');
		add_rewrite_rule($userProfilePageSlug . "/settings/([^/]+)/([^/]+)/?$", "index.php?page_id=$userProfilePageId&user-edits-settings=1&ultracomm-settings-section=\$matches[1]&ultracomm-user-slug=\$matches[2]", 'top');
		add_rewrite_rule($userProfilePageSlug . '/settings/([^/]+)/?$', "index.php?page_id=$userProfilePageId&user-edits-settings=1&ultracomm-user-slug=\$matches[1]", 'top');

		add_rewrite_tag('%ultracomm-user-slug%', '([^/]+)');
		add_rewrite_rule($userProfilePageSlug . '/([^/]+)/?$', "index.php?page_id=$userProfilePageId&ultracomm-user-slug=\$matches[1]", 'top');

		add_rewrite_tag("%uc-page-number%", '([^/]+)');
		add_rewrite_tag("%ultracomm-userprofile-section%", '([^/]+)');
		add_rewrite_rule($userProfilePageSlug . "/([^/]+)/([^/]+)(/page/([0-9]+)?)?/?$", "index.php?page_id=$userProfilePageId&ultracomm-userprofile-section=\$matches[1]&ultracomm-user-slug=\$matches[2]&uc-page-number=\$matches[4]", 'top');

		add_rewrite_tag('%ultracomm-userprofile-section-key%', '([^/]+)');
		add_rewrite_rule($userProfilePageSlug . "/$userActivitySectionSlug/([^/]+)/([0-9]+)/?$", "index.php?page_id=$userProfilePageId&ultracomm-userprofile-section=$userActivitySectionSlug&ultracomm-user-slug=\$matches[1]&ultracomm-userprofile-section-key=\$matches[2]", 'top');

	}

	public static function registerGroupsRoutes()
	{
		
		//flush_rewrite_rules(true);
		
		$groupsDirectoryPageId   = FrontPageSettingsPublicModule::getGroupsDirectoryPageId();
		$groupsDirectoryPageSlag = FrontPageSettingsPublicModule::getGroupsDirectoryPageSlug();

		$groupActivitySectionSlug = GroupProfileAppearanceAdminModule::PROFILE_SECTION_ACTIVITY;
		
		add_rewrite_tag('%ultracomm-group-slug%', '([^/]+)');
		add_rewrite_tag('%uc-is-groups-directory%', '([0-9]{1})');
		
		add_rewrite_rule($groupsDirectoryPageSlag . '/([^/]+)/?$', "index.php?page_id=$groupsDirectoryPageId&ultracomm-group-slug=\$matches[1]", 'top');
		
		add_rewrite_rule($groupsDirectoryPageSlag . "/page/([0-9]+)?/?$", "index.php?page_id=$groupsDirectoryPageId&uc-page-number=\$matches[1]&uc-is-groups-directory=1", 'top');

		add_rewrite_tag("%uc-page-number%", '([^/]+)');
		
		add_rewrite_tag("%ultracomm-group-section%", '([^/]+)');
		add_rewrite_rule($groupsDirectoryPageSlag . "/([^/]+)/([^/]+)(/page/([0-9]+)?)?/?$", "index.php?page_id=$groupsDirectoryPageId&ultracomm-group-slug=\$matches[1]&ultracomm-group-section=\$matches[2]&uc-page-number=\$matches[4]", 'top');

		add_rewrite_tag('%ultracomm-group-section-key%', '([^/]+)');
		add_rewrite_rule($groupsDirectoryPageSlag . "/([^/]+)/$groupActivitySectionSlug/([0-9]+)/?$", "index.php?page_id=$groupsDirectoryPageId&ultracomm-group-slug=\$matches[1]&ultracomm-group-section=$groupActivitySectionSlug&ultracomm-group-section-key=\$matches[2]", 'top');
		
		

	}


	private function __construct(){
	}
	private function __clone(){
	}

}

