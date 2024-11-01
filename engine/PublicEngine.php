<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity;


use UltraCommunity\Controllers\TemplatesController;
use UltraCommunity\FrontPages\ActivityPage;
use UltraCommunity\Controllers\ActivityController;
use UltraCommunity\Controllers\FrontPageController;
use UltraCommunity\Controllers\GroupController;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Controllers\ShortCodesController;
use UltraCommunity\Controllers\UploadsController;
use UltraCommunity\Controllers\UserController;
use UltraCommunity\Controllers\UserRoleController;
use UltraCommunity\FrontPages\BasePage;
use UltraCommunity\FrontPages\GroupProfilePage;
use UltraCommunity\FrontPages\GroupsDirectoryPage;
use UltraCommunity\FrontPages\MembersDirectoryPage;
use UltraCommunity\FrontPages\UserProfilePage;
use UltraCommunity\FrontPages\UserSettingsPage;
use UltraCommunity\MchLib\Plugin\MchBasePublicPlugin;
use UltraCommunity\MchLib\Utils\MchMinifier;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\ShortCodeEntity;
use UltraCommunity\Modules\Appearance\UserProfile\UserProfileAppearanceAdminModule;
use UltraCommunity\Modules\Appearance\UserProfile\UserProfileAppearancePublicModule;
use UltraCommunity\Modules\GeneralSettings\FrontPage\FrontPageSettingsPublicModule;
use UltraCommunity\Modules\GeneralSettings\User\UserSettingsPublicModule;

class PublicEngine extends MchBasePublicPlugin
{

	protected function __construct()
	{
		parent::__construct();

		MchWpUtils::addActionHook('wp', function(){
			
			MchWpUtils::addFilterHook('template_include', function($templateFilePath){

				if(!FrontPageController::hasActivePage() || !FrontPageController::canUseGlobalTemplate() || null === ($globalTemplateFilePath = TemplatesController::getGlobalPageTemplateFilePath()) )
					return $templateFilePath;

				return 	$globalTemplateFilePath;

			}, PHP_INT_MAX);

			if(FrontPageController::hasActivePage())
			{
				MchWpUtils::addActionHook('wp_enqueue_scripts', array(PublicEngine::getInstance(), 'enqueuePublicScriptsAndStyles'), PHP_INT_MAX);
			}
			
			if(FrontPageController::hasActivePage() && ($activePageIsUserProfilePage = (FrontPageController::getActivePageTypeId() === FrontPageController::PAGE_USER_PROFILE)))
			{
				$activePageIsUserProfilePage && MchWpUtils::addFilterHook('pre_get_document_title', function($documentTitle) use($activePageIsUserProfilePage){
					static $documentTitleFilter = null; if( !$activePageIsUserProfilePage || null !== $documentTitleFilter) return $documentTitle;

					$documentTitleFilter = MchWpUtils::addFilterHook('document_title_parts', function($arrTitleParts) use($activePageIsUserProfilePage){
						if(!$activePageIsUserProfilePage){
							return $arrTitleParts;
						}

						$siteName = isset($arrTitleParts['site']) ? $arrTitleParts['site'] : null; unset($arrTitleParts['site']);
						$arrTitleParts['title']   = UltraCommHelper::getUserDisplayName(UserController::getProfiledUser());
						$arrTitleParts['tagline'] = UserProfileAppearancePublicModule::getUserProfileSectionNameBySlug(FrontPageController::getActivePage()->getActiveSectionSlug());

						(null === $siteName) ?: $arrTitleParts['site'] = $siteName;

						return $arrTitleParts;

					}, PHP_INT_MAX);

					return null;

				}, PHP_INT_MAX);
			}

			if(is_author() && UserSettingsPublicModule::shouldRedirectAuthorToProfile())
			{
				!MchWpUtils::isWPUser($wpAuthor = get_queried_object()) ?: MchWpUtils::redirectToUrl(UltraCommHelper::getUserProfileUrl($wpAuthor));
			}

			if(UserSettingsPublicModule::shouldRedirectCommentAuthorToProfile())
			{
				MchWpUtils::addFilterHook('get_comment_author_link', function ($authorUrl, $authorName, $commentId){
					$wpComment = get_comment( $commentId );
					if(empty($wpComment->user_id) || !($userProfileUrl = UltraCommHelper::getUserProfileUrl($wpComment->user_id)))
						return $authorUrl;

					return "<a href=\"$userProfileUrl\" class='url'>$authorName</a>";
				}, 9999, 3);

				foreach (array('get_comment_author_url', 'comment_url') as $commentHook){
					MchWpUtils::addFilterHook($commentHook, function ($authorUrl, $commentId){
						$wpComment = get_comment( $commentId );
						if(empty($wpComment->user_id) || !($userProfileUrl = UltraCommHelper::getUserProfileUrl($wpComment->user_id)))
							return $authorUrl;

						return $userProfileUrl;
					}, 9999, 2);
				}
			}

			MchWpUtils::addFilterHook('wp_nav_menu_objects', function($menuItems, $menuArgs){

				$arrDefaultPagesIds = array(
					FrontPageSettingsPublicModule::getLoginPageId()          => FrontPageController::PAGE_LOGIN,
					FrontPageSettingsPublicModule::getUserProfilePageId()    => FrontPageController::PAGE_USER_PROFILE,
					FrontPageSettingsPublicModule::getRegistrationPageId()   => FrontPageController::PAGE_REGISTRATION,
					FrontPageSettingsPublicModule::getForgotPasswordPageId() => FrontPageController::PAGE_FORGOT_PASSWORD,
				);

				$menuItems = (array)$menuItems;
				foreach($menuItems as $index => &$wpPostMenuItem)
				{
					if(empty($wpPostMenuItem->object_id) || !isset($arrDefaultPagesIds[$wpPostMenuItem->object_id]))
						continue;

					$wpPostMenuItem->classes = empty($wpPostMenuItem->classes) ? array() : (array)$wpPostMenuItem->classes;

					switch($arrDefaultPagesIds[$wpPostMenuItem->object_id])
					{
						case FrontPageController::PAGE_LOGIN :

							if(MchWpUtils::isUserLoggedIn())
							{
								$wpPostMenuItem->url       = FrontPageController::getLogOutPageUrl();
								$wpPostMenuItem->title     = __('Logout', 'ultra-community');
								$wpPostMenuItem->classes[] = 'uc-menu-item-login';
							}

							break;

						case FrontPageController::PAGE_REGISTRATION :

							$wpPostMenuItem->classes[] = 'uc-menu-item-register';
							if(UserController::isUserLoggedIn()){
								unset($menuItems[$index]);
							}

							break;

						case FrontPageController::PAGE_FORGOT_PASSWORD :

							if(UserController::isUserLoggedIn()){
								unset($menuItems[$index]);
							}

							break;

						case FrontPageController::PAGE_USER_PROFILE :

							$wpPostMenuItem->classes[] = 'uc-menu-item-myprofile';
							if(!UserController::isUserLoggedIn())
							{
								unset($menuItems[$index]);
							}
							else
							{
								$wpPostMenuItem->url       = UltraCommHelper::getUserProfileUrl(UserController::getLoggedInUser());
								$wpPostMenuItem->title     = __('My Profile', 'ultra-community');
								$wpPostMenuItem->classes[] = 'uc-menu-item-profile';
							}
							break;

					}

				}

				return $menuItems;

			}, 10, 2);


		}, PHP_INT_MAX);


		MchWpUtils::addFilterHook('show_admin_bar', function($shouldShow){

			return UserRoleController::currentUserCanViewAdminToolbar();

		}, PHP_INT_MAX);


		//remove_action('wp_enqueue_scripts', array( $this, 'enqueuePublicScriptsAndStyles' ));

	}

	public function enqueuePublicScriptsAndStyles()
	{
		static $assetsEnqueued = false;
		if( $assetsEnqueued )
				return;

		$assetsEnqueued = true;
		
		wp_enqueue_style('ultracomm-font-roboto', '//fonts.googleapis.com/css?family=Roboto:400,500,600,700', array(), null);

//wp_enqueue_style('ultracomm-fa5', 'https://use.fontawesome.com/releases/v5.1.0/css/all.css', array(), null);

		$stylesBaseUrl  = self::$PLUGIN_URL . '/assets/styles/';
		$scriptsBaseUrl = self::$PLUGIN_URL . '/assets/scripts/';

		$arrRegisteredAssets = array(

			'styles' => array(
				'uc-font-awesome'    => '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css',
				'uc-stylesheet'      => $stylesBaseUrl  . 'ultra-community-dist.min.css',
			),

			'scripts' => array(
				'uc-public-script'     => $scriptsBaseUrl  . 'ultra-community-dist.min.js',
			)
		);

		if(FrontPageController::getActivePage() instanceof UserSettingsPage)
		{
			$arrRegisteredAssets['scripts']['uc-user-settings-script'] = $scriptsBaseUrl  . 'ultra-community-settings-dist.min.js';

			unset($arrRegisteredAssets['scripts']['uc-public-script']);
		}

		$arrRegisteredAssets['styles']  = (array)apply_filters(UltraCommHooks::FILTER_FRONT_END_REGISTERED_STYLES,  $arrRegisteredAssets['styles']);
		$arrRegisteredAssets['scripts'] = (array)apply_filters(UltraCommHooks::FILTER_FRONT_END_REGISTERED_SCRIPTS, $arrRegisteredAssets['scripts']);

		wp_enqueue_script( 'jquery' );

		foreach ($arrRegisteredAssets['styles'] as $assetKey => $assetUrl){
			wp_enqueue_style($assetKey, $assetUrl, array(), ('uc-font-awesome' !== $assetKey) ?  \UltraCommunity::PLUGIN_VERSION : null);
		}

		$pageInlineCss =  FrontPageController::hasActivePage() ? FrontPageController::getActivePage()->getPageCustomCss() : '';

		//$arrDetectedShortCodes = ShortCodesController::getAllDetectedShortCodes();

		if($pageInlineCss = apply_filters(UltraCommHooks::FILTER_FRONT_END_ADDITIONAL_INLINE_CSS, $pageInlineCss, FrontPageController::getActivePage()))
		{
			wp_add_inline_style( 'uc-stylesheet', wp_specialchars_decode(stripslashes(MchMinifier::getMinifiedCss($pageInlineCss)), \ENT_QUOTES) );
		}


		foreach (array('get_footer', 'wp_footer', 'wp_print_footer_scripts') as $footerHook)
		{
			MchWpUtils::addActionHook($footerHook, function () use(&$arrRegisteredAssets){

				
				if(empty($arrRegisteredAssets['scripts'])){
					return;
				}

				(!FrontPageController::hasActivePage()) ?: FrontPageController::getActivePage()->renderPageFooterContent();

				foreach ($arrRegisteredAssets['scripts'] as $assetKey => $assetUrl){
					wp_enqueue_script($assetKey, $assetUrl, array('jquery'), \UltraCommunity::PLUGIN_VERSION);
				}

				$arrLocalizedObjectProperties = array(
					'ajaxUrl' =>  AjaxHandler::getAjaxUrl(),
					'loggedInUserId' => false,
					AjaxHandler::REQUEST_NONCE_KEY => AjaxHandler::getAjaxNonce(),
				);
				
				if(FrontPageController::hasActivePage() && FrontPageController::getActivePage()->getModuleCustomPostType())
				{
					switch (FrontPageController::getActivePage()->getModuleCustomPostType()->PostType)
					{
						case PostTypeController::POST_TYPE_GROUPS_DIRECTORY :
						case PostTypeController::POST_TYPE_MEMBERS_DIRECTORY :
							$arrLocalizedObjectProperties['directoryId'] = FrontPageController::getActivePage()->getModuleCustomPostType()->PostId;
							break;
					}
					
				}
				else
				{
				}
				
				
				
				$arrLocalizedObjectProperties['profiledUserSlug'] = UserController::getProfiledUser()   ?  UserController::getProfiledUser()->NiceName : '';
				$arrLocalizedObjectProperties['profiledGroupId']  = GroupController::getProfiledGroup() ? GroupController::getProfiledGroup()->Id : '';

				wp_localize_script( empty($arrRegisteredAssets['scripts']['uc-public-script']) ?  'uc-user-settings-script' : 'uc-public-script' , 'UltraComm', $arrLocalizedObjectProperties);

				unset($arrRegisteredAssets['scripts']);

			}, 1);

		}


	}


	public function registerAfterSetupThemeHooks()
	{
	}

}