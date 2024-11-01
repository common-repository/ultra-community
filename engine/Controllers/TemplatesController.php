<?php
namespace UltraCommunity\Controllers;

use UltraCommunity\FrontPages\UserSettingsPage;
use UltraCommunity\MchLib\Plugin\MchBasePlugin;
use UltraCommunity\MchLib\Utils\MchDirectoryUtils;
use UltraCommunity\MchLib\Utils\MchFileUtils;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\UltraCommHooks;

class TemplatesController
{
	CONST PAGE_CONTENT_NOTICE           = 'page-content-notice';

	CONST LOGIN_FORM_TEMPLATE           = 'login';
	CONST REGISTRATION_FORM_TEMPLATE    = 'register';
	CONST FORGOT_PASSWORD_FORM_TEMPLATE = 'forgot-password';
	CONST MEMBERS_DIRECTORY_TEMPLATE    = 'members-directory';


	CONST USER_PROFILE_NAV_BAR_TEMPLATE  = 'user-profile/user-profile-page-navbar';

	CONST USER_PROFILE_ACTIVITY_TEMPLATE = 'user-profile/user-profile-page-activity';

	CONST USER_PROFILE_FRIENDS_TEMPLATE           = 'user-profile-page-friends.php';
	CONST USER_PROFILE_FRIEND_REQUESTS_TEMPLATE   = 'user-settings-relations-friend-requests.php';
	CONST USER_PROFILE_FOLLOWERS_TEMPLATE         = 'user-profile-page-followers.php';
	CONST USER_PROFILE_FOLLOWING_TEMPLATE         = 'user-profile-page-following.php';

	CONST USER_PROFILE_REVIEWS_TEMPLATE           = 'user-profile-page-reviews.php';

	CONST USER_PROFILE_FORUMS_TOPICS    = 'user-profile-page-forums-topics.php';
	CONST USER_PROFILE_FORUMS_REPLIES   = 'user-profile-page-forums-replies.php';
	CONST USER_PROFILE_FORUMS_FAVORITES = 'user-profile-page-forums-favorites.php';


	
	

	CONST USER_PROFILE_NAV_BAR_USER_SETTINGS_TEMPLATE    = 'user-profile-page-navbar-settings';
	CONST USER_PROFILE_NAV_BAR_USER_ACTIONS_TEMPLATE     = 'user-profile-page-navbar-actions';

	CONST WIDGET_USER_MAIN_NAVIGATION_TEMPLATE  = 'widgets/widget-user-main-navigation.php';
	CONST WIDGET_USER_LATEST_POSTS_TEMPLATE     = 'widgets/widget-user-latest-posts.php';
	CONST WIDGET_USER_ABOUT_MYSELF_TEMPLATE     = 'widgets/widget-user-about-myself.php';
	CONST WIDGET_USER_LATEST_FRIENDS_TEMPLATE   = 'widgets/widget-user-latest-friends.php';
	CONST WIDGET_USER_LATEST_FOLLOWERS_TEMPLATE = 'widgets/widget-user-latest-followers.php';
	CONST WIDGET_USER_LATEST_GROUPS_TEMPLATE    = 'widgets/widget-user-latest-groups.php';
	CONST WIDGET_USER_SOCIAL_NETWORKS_TEMPLATE  = 'widgets/widget-user-social-networks.php';
	CONST WIDGET_USER_REVIEWS_TEMPLATE          = 'widgets/widget-user-reviews.php';

	CONST WIDGET_GROUP_ABOUT_TEMPLATE          = 'widgets/widget-group-about.php';
	CONST WIDGET_GROUP_RECENT_MEMBERS_TEMPLATE = 'widgets/widget-group-recent-members.php';
	CONST WIDGET_GROUP_ONLINE_MEMBERS_TEMPLATE = 'widgets/widget-group-online-members.php';



	CONST GROUP_PROFILE_ABOUT_TEMPLATE                 = 'group-profile/group-profile-page-about';
	CONST GROUP_PROFILE_MEMBERS_TEMPLATE               = 'group-profile/group-profile-page-members';
	CONST GROUP_PROFILE_ACTIVITY_TEMPLATE              = 'group-profile/group-profile-page-activity';
	CONST GROUP_PROFILE_NAV_BAR_USER_ACTIONS_TEMPLATE  = 'group-profile-page-navbar-actions';
	
	
	
	CONST USER_SETTINGS_SIDEBAR_TEMPLATE   = 'user-settings-sidebar';
	
	/**
	 * @param $headerStyleVersionId
	 * @param array $arrHeaderArguments
	 *
	 * The structure of arrHeaderArguments is expected as follow
	/*
		$arrPageHeaderInfo = array(
			'head-line' => 'User Display Name',
			'cover-url' => 'https://ultracommunity.com/wp-content/plugins/ultra-community/assets/cover-3.jpg',
			'picture-url' => 'https://ultracommunity.com/wp-content/uploads/ultra-comm/uploads/15/avatar/photo-1477814670986-8d8dccc5640d-1.jpeg',
			'meta-list' => array(

					array('icon' => 'fa-map-marker', 'content' => 'Canada, Chicago', 'url' => ''),
					array('icon' => 'fa-chain-broken', 'content' => 'www.ultracommunity.com', 'url' => 'https://www.ultracommunity.com')

			),

			'stats-list' => array(
					array('content' => 'Posts', 'number' => 12), array('content' => 'Comments', 'number' => 84)
			),

			'sn-list' => array(
							'facebook' => array(
							'url' => 'http://facebook.com',
	                        'colored' => true
					)
			),
		);
	 */
	public static function loadPageHeaderTemplate($headerStyleVersionId, $arrHeaderArguments = array())
	{

		$coverUrl = $pictureUrl = $headLineOutput = $metaListOutput = $statsListOutput = $socialNetworksListOutput = null;

		$arrHeaderArguments['meta-list']  = !empty($arrHeaderArguments['meta-list'])  ? (array)$arrHeaderArguments['meta-list']  : array();
		$arrHeaderArguments['stats-list'] = !empty($arrHeaderArguments['stats-list']) ? (array)$arrHeaderArguments['stats-list'] : array();
		$arrHeaderArguments['sn-list']    = !empty($arrHeaderArguments['sn-list'])    ? (array)$arrHeaderArguments['sn-list']    : array();


		empty($arrHeaderArguments['cover-url'])   ?: $coverUrl       = esc_url($arrHeaderArguments['cover-url']);
		empty($arrHeaderArguments['picture-url']) ?: $pictureUrl     = esc_url($arrHeaderArguments['picture-url']);
		empty($arrHeaderArguments['head-line'])   ?: $headLineOutput = esc_html($arrHeaderArguments['head-line']);

		foreach($arrHeaderArguments['meta-list'] as $arrMetaItem)
		{
			$metaListOutput .= '<li>';

			empty($arrMetaItem['icon']) ?: $metaListOutput .= '<i class="fa ' . sanitize_html_class($arrMetaItem['icon']) . '"></i>';

			$arrMetaItem['content'] = empty($arrMetaItem['content']) ? null : esc_html($arrMetaItem['content']);
			$arrMetaItem['url']     = empty($arrMetaItem['url'])     ? null : esc_url($arrMetaItem['url']);

			$metaListOutput .= empty($arrMetaItem['url']) ? "<span>{$arrMetaItem['content']}</span>" : "<a href=\"{$arrMetaItem['url']}\"><span>{$arrMetaItem['content']}</span></a>";

			$metaListOutput .= '</li>';
		}

		foreach($arrHeaderArguments['stats-list'] as $arrStats)
		{
			$arrStats['number']  = empty($arrStats['number'])  ? 0    : esc_html($arrStats['number']);
			$arrStats['content'] = empty($arrStats['content']) ? null : esc_html($arrStats['content']);
			$statsListOutput .= "<li><span>{$arrStats['number']}</span><span>{$arrStats['content']}</span></li>";
		}

		$coloredSocialNetworkBackground = in_array($headerStyleVersionId, array(1, 2, 3));
		foreach ( $arrHeaderArguments['sn-list'] as $networkKey => $socialNetwork )
		{
			$networkKey = sanitize_html_class($networkKey);

			empty($socialNetwork['url']) ?: $socialNetwork['url'] = esc_url($socialNetwork['url']);

			$linkHtmlClass = "uc-sn-$networkKey";

			empty($coloredSocialNetworkBackground)         ?: $linkHtmlClass .= ' uc-social-network-colored';
			empty($arrHeaderArguments['circled-sn-icons']) ?: $linkHtmlClass .= ' uc-circle-border';

			$socialNetworksListOutput .= '<a class="uc-grid-cell uc-grid-cell--autoSize uc-grid--justify-center uc-social-network '. $linkHtmlClass .'" href="'. $socialNetwork['url'] .'">';
			$socialNetworksListOutput .= '<i class="uc-grid-cell--center fa fa-' . $networkKey . '"></i>';
			$socialNetworksListOutput .= '</a>';
		}

		$pictureHolderAdditionalClass  = empty($arrHeaderArguments['circled-avatar'])     ? ' ' : ' uc-circle-border';
		$pictureHolderAdditionalClass .= empty($arrHeaderArguments['show-online-status']) ? ' ' : ' uc-online-indicator';
		
		$changeCoverOutput = $changeAvatarOutput = null;
		if(FrontPageController::getActivePage() instanceof UserSettingsPage)
		{
			$changeCoverText  = esc_html__('Change image', 'ultra-community');
			$changeAvatarText = esc_html__('Change picture', 'ultra-community');
			
			$changeCoverUrl   = FrontPageController::getUserSettingsPageUrlBySection(UserSettingsPage::SETTINGS_SECTION_PROFILE_COVER_PICTURE, UserController::getProfiledUser()->NiceName);
			$changeAvatarUrl = FrontPageController::getUserSettingsPageUrlBySection(UserSettingsPage::SETTINGS_SECTION_PROFILE_AVATAR_PICTURE, UserController::getProfiledUser()->NiceName);
			
			$changeCoverOutput = self::getTemplateOutputContent('page-header-change-cover.php', array(
				'changeCoverUrl'  => $changeCoverUrl,
				'changeCoverText' => $changeCoverText,
			));
			
			$changeAvatarOutput = self::getTemplateOutputContent('page-header-change-avatar.php', array(
				'changeAvatarUrl'  => $changeAvatarUrl,
				'changeAvatarText' => $changeAvatarText,
			));
		}
		
		$beforeMetaListOutput = MchUtils::captureOutputBuffer(function () use ($headerStyleVersionId){
			do_action(UltraCommHooks::ACTION_USER_PROFILE_HEADER_BEFORE_META_LIST, $headerStyleVersionId);
		});

		$afterMetaListOutput = MchUtils::captureOutputBuffer(function () use ($headerStyleVersionId){
			do_action(UltraCommHooks::ACTION_USER_PROFILE_HEADER_AFTER_META_LIST, $headerStyleVersionId);
		});
		
		//$metaListOutput  = '<ul class="uc-grid-cell uc-header-meta-list-holder">' . $metaListOutput . '</ul>';
		//$statsListOutput = "<ul>$statsListOutput</ul>";
		//$headLineOutput  = "<h2>$headLineOutput</h2>";
		
		$arrHeaderArguments = compact('coverUrl', 'pictureUrl', 'changeCoverOutput', 'changeAvatarOutput', 'pictureHolderAdditionalClass', 'headLineOutput', 'beforeMetaListOutput', 'metaListOutput', 'afterMetaListOutput', 'statsListOutput', 'socialNetworksListOutput');
		
		
		$templateName = 'page-headers/page-header-style-' . ((int)$headerStyleVersionId) . '.php';
		
		self::loadTemplate($templateName, $arrHeaderArguments);
	}


	public static function loadTemplate($templateKeyName, array $arrTemplateArguments = array())
	{
		$templateKeyName      = \apply_filters(UltraCommHooks::FILTER_FRONT_END_LOAD_TEMPLATE_KEY, $templateKeyName, $arrTemplateArguments);
		
		$arrTemplateArguments = (array)\apply_filters(UltraCommHooks::FILTER_FRONT_END_TEMPLATE_ARGUMENTS, $arrTemplateArguments,  $templateKeyName);

		if(null === ($templateFilePath = self::getTemplateFilePath($templateKeyName, $arrTemplateArguments)) || !is_file($templateFilePath))
			return;

		if(!empty($arrTemplateArguments) /*&& is_array($arrTemplateArguments)*/)
		{
			extract( $arrTemplateArguments );
		}

		include $templateFilePath;

	}

	public static function getTemplateOutputContent($templateKeyName, $arrTemplateArguments = array())
	{
		if(empty($templateKeyName))
			return null;

		ob_start();

		self::loadTemplate($templateKeyName, $arrTemplateArguments);

		return ob_get_clean();
	}

	public static function getGlobalPageTemplateFilePath()
	{
		return self::getTemplateFilePath('global-page-template.php');
	}

	public static function getTemplateFilePath($templateKeyName, $arrTemplateArguments = array())
	{
		if(empty($templateKeyName))
			return null;

		static $arrSubFolders = null;
		if(null === $arrSubFolders)
		{
			$arrSubFolders = array();
			if( \is_dir($templatesDirPath = \get_stylesheet_directory() . '/ultra-community/templates') ){
				$arrSubFolders = \array_merge($arrSubFolders, [$templatesDirPath]);
				$arrSubFolders = \array_merge($arrSubFolders, MchDirectoryUtils::getDirectorySubDirectories($templatesDirPath, true));
			}

			if( \is_dir($templatesDirPath = \get_template_directory() . '/ultra-community/templates') ){
				$arrSubFolders = \array_merge($arrSubFolders, [$templatesDirPath]);
				$arrSubFolders = \array_merge($arrSubFolders, MchDirectoryUtils::getDirectorySubDirectories($templatesDirPath, true));
			}

			$defaultTemplatesDirPath = MchBasePlugin::getPluginDirectoryPath() . '/templates';
			
			$templatesDirPath = \rtrim( \apply_filters(UltraCommHooks::FILTER_FRONT_END_TEMPLATES_DIR_PATH, $defaultTemplatesDirPath), '/\\' );
			
			if($defaultTemplatesDirPath !== $templatesDirPath && is_dir($templatesDirPath)) {
				$arrSubFolders = \array_merge($arrSubFolders, [$templatesDirPath]);
				$arrSubFolders = \array_merge($arrSubFolders, MchDirectoryUtils::getDirectorySubDirectories($templatesDirPath, true));
			}
			
			if( \is_dir($defaultTemplatesDirPath) ){
				$arrSubFolders = \array_merge($arrSubFolders, [$defaultTemplatesDirPath]);
				$arrSubFolders = \array_merge($arrSubFolders, MchDirectoryUtils::getDirectorySubDirectories($defaultTemplatesDirPath, true));
			}
			
//			if( \is_dir( $templatesDirPath = \rtrim( \apply_filters(UltraCommHooks::FILTER_FRONT_END_TEMPLATES_DIR_PATH,  MchBasePlugin::getPluginDirectoryPath() . '/templates'), '/\\' ) ) ){
//				$arrSubFolders = \array_merge($arrSubFolders, MchDirectoryUtils::getDirectorySubDirectories($templatesDirPath, true));
//			}
		}

		
		//print_r($arrSubFolders);exit;
		
		$templateKeyName = \trim($templateKeyName, '/\\');

		( 0 === \substr_compare($templateKeyName, '.php', -4 , 4, false) ) ?: $templateKeyName .= '.php';

		$templateFilePath = null;
		foreach($arrSubFolders as $subFolderPath)
		{
			if (\is_file($templateFilePath = "{$subFolderPath}/{$templateKeyName}"))
				break;

			$templateFilePath = null;
		}

		return \apply_filters(UltraCommHooks::FILTER_FRONT_END_TEMPLATE_FILE_PATH, $templateFilePath, $templateKeyName, $arrTemplateArguments);

	}

	
	public static function templatesHaveSameKey($templateKey1, $templateKey2)
	{
		return MchFileUtils::getFileName($templateKey1) === MchFileUtils::getFileName($templateKey2);
	}
	
	public static function getTemplateFileNameFromKey($templateKey)
	{
		return MchFileUtils::getFileName($templateKey) . '.php';
	}
	
	public static function getTemplatesDirectoryPath()
	{
		static $templatesDirPath = null;
		if(null !== $templatesDirPath)
			return $templatesDirPath;

		if(is_dir($templatesDirPath = \get_stylesheet_directory() . '/ultra-community/templates')){
			return $templatesDirPath;
		}

		if(is_dir($templatesDirPath = \get_template_directory() . '/ultra-community/templates')){
			return $templatesDirPath;
		}

		$templatesDirPath = rtrim( \apply_filters(UltraCommHooks::FILTER_FRONT_END_TEMPLATES_DIR_PATH,  MchBasePlugin::getPluginDirectoryPath() . '/templates'), '/\\' ) ;

		return $templatesDirPath;

	}





	private function __construct() {
	}
}