<?php
namespace UltraCommunity\Controllers;

use UltraCommunity\Entities\GroupUserEntity;
use UltraCommunity\Entities\UserEntity;
use UltraCommunity\FrontPages\UserSettingsPage;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\Repository\WpPostRepository;
use UltraCommunity\Repository\BaseRepository;
use UltraCommunity\Repository\GroupRepository;
use UltraCommunity\Repository\UserRepository;
use UltraCommunity\UltraCommHelper;
use UltraCommunity\UltraCommHooks;
use UltraCommunity\UltraCommUtils;

class WidgetsController
{
	CONST WIDGET_USER_LATEST_POSTS     = 1;
	CONST WIDGET_USER_SOCIAL_NETWORKS  = 2;
	CONST WIDGET_USER_ABOUT_MYSELF     = 3;
	CONST WIDGET_USER_LATEST_FRIENDS   = 4;
	CONST WIDGET_USER_LATEST_FOLLOWERS = 5;
	CONST WIDGET_USER_LATEST_GROUPS    = 6;
	CONST WIDGET_USER_REVIEWS          = 7;

	CONST WIDGET_USER_MAIN_NAVIGATION  = 10;

	CONST WIDGET_GROUP_ABOUT          = 51;
	CONST WIDGET_GROUP_RECENT_MEMBERS = 52;
	CONST WIDGET_GROUP_ONLINE_MEMBERS = 53;

	public static function registerWidgets()
	{
//		\add_action( 'widgets_init', function(){
//			//register_widget( '\UltraCommunity\Widgets\My_Widget_Class' );
//		});
	}

	public static function getUserAvailableWidgets()
	{
		$arrAvailableWidgets  = array(
			self::WIDGET_USER_ABOUT_MYSELF    => esc_html__('About MySelf', 'ultra-community'),
			self::WIDGET_USER_LATEST_POSTS    => esc_html__('Latest Posts', 'ultra-community'),
			self::WIDGET_USER_SOCIAL_NETWORKS => esc_html__('Social Networks', 'ultra-community'),
			self::WIDGET_USER_LATEST_GROUPS   => esc_html__('Groups', 'ultra-community'),

		);

		if(ModulesController::isModuleRegistered(ModulesController::MODULE_USER_REVIEWS)){
			$arrAvailableWidgets[self::WIDGET_USER_REVIEWS] = esc_html__('Reviews', 'ultra-community');
		}

		if(ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FOLLOWERS)){
			$arrAvailableWidgets[self::WIDGET_USER_LATEST_FOLLOWERS] = esc_html__('Followers', 'ultra-community');
		}

		if(ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FRIENDS)){
			$arrAvailableWidgets[self::WIDGET_USER_LATEST_FRIENDS] = esc_html__('Friends', 'ultra-community');
		}

		return $arrAvailableWidgets;
	}

	public static function getGroupAvailableWidgets()
	{
		return array(
			self::WIDGET_GROUP_ABOUT             => esc_html__('About', 'ultra-community'),
			self::WIDGET_GROUP_RECENT_MEMBERS    => esc_html__('Recent Members', 'ultra-community'),
			self::WIDGET_GROUP_ONLINE_MEMBERS    => esc_html__('Online Members', 'ultra-community'),
		);

	}

	public static function renderWidgetContent($widgetId, \stdClass $widgetItemInfo = null)
	{
		(null !== $widgetItemInfo) ?: $widgetItemInfo = new \stdClass();

		switch($widgetId)
		{
			case self::WIDGET_USER_MAIN_NAVIGATION : self::renderUserMainNavigation($widgetItemInfo); break;

			case self::WIDGET_USER_LATEST_POSTS    : self::renderUserLatestPosts($widgetItemInfo); break;
			case self::WIDGET_USER_SOCIAL_NETWORKS : self::renderUserSocialNetworks($widgetItemInfo); break;
			case self::WIDGET_USER_ABOUT_MYSELF    : self::renderUserAboutMySelf($widgetItemInfo); break;

			case self::WIDGET_USER_LATEST_FRIENDS   : self::renderUserLatestFriends($widgetItemInfo); break;
			case self::WIDGET_USER_LATEST_FOLLOWERS : self::renderUserLatestFollowers($widgetItemInfo); break;
			case self::WIDGET_USER_LATEST_GROUPS    : self::renderUserLatestGroups($widgetItemInfo); break;

			case self::WIDGET_USER_REVIEWS         : self::renderUserReviews($widgetItemInfo); break;

			case self::WIDGET_GROUP_ABOUT          : self::renderGroupAbout($widgetItemInfo); break;
			case self::WIDGET_GROUP_RECENT_MEMBERS : self::renderGroupRecentMembers($widgetItemInfo); break;
			case self::WIDGET_GROUP_ONLINE_MEMBERS : self::renderGroupOnlineMembers($widgetItemInfo); break;



		}
	}

	private static function renderUserMainNavigation(\stdClass $widgetItemInfo = null)
	{
		//$widgetItemInfo->Title = '';
		!empty($widgetItemInfo->UserEntity) && ($widgetItemInfo->UserEntity instanceof UserEntity) ?: $widgetItemInfo->UserEntity = UserController::getProfiledUser();
		if(empty($widgetItemInfo->UserEntity))
			return;

		$widgetItemInfo->ArrMenuSections = array();

		$menuSection = new \stdClass();
		$menuSection->Name = esc_html__('Quick Links', 'ultra-community');$menuSection->Name =null;
		$menuSection->ArrNavItems = array();

		$menuNavItem = new \stdClass();
		$menuNavItem->Name = $menuNavItem->IconClass = $menuNavItem->Url = $menuNavItem->Counter = null;
		$widgetItemInfo->ArrMenuSections['uc-section-quick-links'] = clone $menuSection;

		if(GroupController::userCanCreateGroups(UserController::getProfiledUser()))
		{
			$menuSection = new \stdClass();
			$menuSection->Name = esc_html__('Groups', 'ultra-community');
			$menuSection->ArrNavItems = array();
			
			$menuNavItem = new \stdClass();
			$menuNavItem->Name                     = esc_html__('Create New Group', 'ultra-community');
			$menuNavItem->IconClass                = 'fa fa-plus';
			$menuNavItem->Counter                  = 0;
			$menuNavItem->Url                      = FrontPageController::getUserSettingsPageUrlBySection(UserSettingsPage::SETTINGS_SECTION_GROUPS_CREATE_GROUP, UserController::getProfiledUser()->NiceName);
			$menuSection->ArrNavItems['new-group'] = clone $menuNavItem;

			$menuNavItem = new \stdClass();
			$menuNavItem->Name                     = esc_html__('Manage Groups', 'ultra-community');
			$menuNavItem->IconClass                = 'fa fa-cog';
			$menuNavItem->Counter                  = uc_count_user_created_groups(UserController::getProfiledUser());
			$menuNavItem->Url                      = FrontPageController::getUserSettingsPageUrlBySection(UserSettingsPage::SETTINGS_SECTION_GROUPS_MY_GROUPS, UserController::getProfiledUser()->NiceName);
			$menuSection->ArrNavItems['my-groups'] = clone $menuNavItem;
			
			$menuNavItem = new \stdClass();
			$menuNavItem->Name                         = esc_html__('Joined Groups', 'ultra-community');
			$menuNavItem->IconClass                    = 'fa fa-list-ul';
			$menuNavItem->Counter                      = uc_count_user_all_groups(UserController::getProfiledUser()) - uc_count_user_created_groups(UserController::getProfiledUser());
			$menuNavItem->Url                          = FrontPageController::getUserSettingsPageUrlBySection(UserSettingsPage::SETTINGS_SECTION_GROUPS_JOINED_GROUPS, UserController::getProfiledUser()->NiceName);
			$menuSection->ArrNavItems['joined-groups'] = clone $menuNavItem;
			
			$widgetItemInfo->ArrMenuSections['uc-section-groups'] = clone $menuSection;

		}

		$menuSection = new \stdClass();
		$menuSection->Name = esc_html__('Settings', 'ultra-community');
		$menuSection->ArrNavItems = array();

		$menuNavItem = new \stdClass();
		$menuNavItem->Name                            = esc_html__('Profile Settings', 'ultra-community');
		$menuNavItem->IconClass                       = 'fa fa-user-circle';
		$menuNavItem->Url                             = FrontPageController::getUserSettingsPageUrlBySection(UserSettingsPage::SETTINGS_SECTION_PROFILE_FORM_SECTION, UserController::getProfiledUser()->NiceName, false);
		$menuSection->ArrNavItems['profile-settings'] = clone $menuNavItem;

		$menuNavItem->Name                            = esc_html__('Account Settings', 'ultra-community');
		$menuNavItem->IconClass                       = 'fa fa-cogs';
		$menuNavItem->Url                             = FrontPageController::getUserSettingsPageUrlBySection(UserSettingsPage::SETTINGS_SECTION_ACCOUNT_CHANGE_PASSWORD, UserController::getProfiledUser()->NiceName, false);
		$menuSection->ArrNavItems['account-settings'] = clone $menuNavItem;

		$widgetItemInfo->ArrMenuSections['uc-section-settings'] = clone $menuSection;


		$widgetItemInfo->ArrMenuSections  = (array)MchWpUtils::applyFilters(UltraCommHooks::FILTER_WIDGET_MAIN_MENU_SECTIONS, $widgetItemInfo->ArrMenuSections, $widgetItemInfo);
		$widgetItemInfo->ShowLogOutButton = MchWpUtils::applyFilters(UltraCommHooks::FILTER_WIDGET_MAIN_SHOW_LOG_OUT_BUTTON, true, $widgetItemInfo);

		foreach($widgetItemInfo->ArrMenuSections as $sectionLey => $menuSection)
		{
			$menuSection->ArrNavItems = empty($menuSection->ArrNavItems) ? array() : (array) $menuSection->ArrNavItems;
			foreach($menuSection->ArrNavItems as $menuNavItem)
			{
				$menuNavItem->Url       = empty($menuNavItem->Url)       ? null : esc_url($menuNavItem->Url);
				$menuNavItem->Name      = empty($menuNavItem->Name)      ? null : esc_html($menuNavItem->Name);
				$menuNavItem->IconClass = empty($menuNavItem->IconClass) ? null : implode(' ', array_map('sanitize_html_class', explode(' ', $menuNavItem->IconClass)));
			}
		}


		TemplatesController::loadTemplate(TemplatesController::WIDGET_USER_MAIN_NAVIGATION_TEMPLATE, array('widgetItemInfo' => $widgetItemInfo));

	}




	private static function renderGroupAbout(\stdClass $widgetItemInfo = null)
	{
		$widgetItemInfo->Title = empty($widgetItemInfo->Title) ? esc_html__('About', 'ultra-community') : esc_html($widgetItemInfo->Title);

		TemplatesController::loadTemplate(TemplatesController::WIDGET_GROUP_ABOUT_TEMPLATE, array('widgetItemInfo' => $widgetItemInfo));

	}


	private static function renderGroupRecentMembers(\stdClass $widgetItemInfo = null)
	{
		$widgetItemInfo->Title = empty($widgetItemInfo->Title) ? esc_html__('Recent Members', 'ultra-community') : esc_html($widgetItemInfo->Title);

		!empty($widgetItemInfo->GroupEntity) ?: $widgetItemInfo->GroupEntity  = GroupController::getProfiledGroup();

		$groupId = BaseRepository::getGroupIdFromKey($widgetItemInfo->GroupEntity);
		if(empty($groupId))
			return;

		$widgetItemInfo->ArrRecentUserEntities = GroupRepository::getGroupUsers($groupId, 1, 5, GroupUserEntity::GROUP_USER_STATUS_ACTIVE, 'DESC');

		foreach ($widgetItemInfo->ArrRecentUserEntities as $index => &$userEntity)
		{
			$userEntity = UserController::getUserEntityBy($userEntity->UserId);

			if( ! $userEntity )
			{
				unset($widgetItemInfo->ArrRecentUserEntities[$index]);
				continue;
			}

			$userEntity->Actions = UserRelationsController::getUserRelationsPossiblePageActions(UserController::getLoggedInUser(), $userEntity, true);

			foreach ($userEntity->Actions as $actionEntity) {
				$actionEntity->ActionType = (-1) * $actionEntity->ActionType;
			}

		}

		unset($userEntity);

		TemplatesController::loadTemplate(TemplatesController::WIDGET_GROUP_RECENT_MEMBERS_TEMPLATE, array('widgetItemInfo' => $widgetItemInfo));

	}

	private static function renderGroupOnlineMembers(\stdClass $widgetItemInfo = null)
	{
		!empty($widgetItemInfo->GroupEntity) ?: $widgetItemInfo->GroupEntity  = GroupController::getProfiledGroup();

		$widgetItemInfo->ArrOnlineUserEntities = UserController::getOnlineUserIds(true);
		$widgetItemInfo->ArrOnlineUserEntities = GroupRepository::getGroupUsersEntities($widgetItemInfo->GroupEntity, $widgetItemInfo->ArrOnlineUserEntities);
		$widgetItemInfo->ArrOnlineUserEntities = \array_slice($widgetItemInfo->ArrOnlineUserEntities, 0, 10);

		foreach ($widgetItemInfo->ArrOnlineUserEntities as &$userEntity){
			$userEntity = UserController::getUserEntityBy($userEntity->UserId);
		}
		unset($userEntity);

		$widgetItemInfo->ArrOnlineUserEntities = array_filter($widgetItemInfo->ArrOnlineUserEntities);

		$widgetItemInfo->Title = empty($widgetItemInfo->Title) ? esc_html__('Online Members', 'ultra-community') : esc_html($widgetItemInfo->Title);

		TemplatesController::loadTemplate(TemplatesController::WIDGET_GROUP_ONLINE_MEMBERS_TEMPLATE, array('widgetItemInfo' => $widgetItemInfo));

	}


	private static function renderUserLatestFriends(\stdClass $widgetItemInfo = null)
	{

		$widgetItemInfo->Title = empty($widgetItemInfo->Title) ? esc_html__('Friends', 'ultra-community') : esc_html($widgetItemInfo->Title);
		TemplatesController::loadTemplate(TemplatesController::WIDGET_USER_LATEST_FRIENDS_TEMPLATE, array('widgetItemInfo' => $widgetItemInfo));

	}

	private static function renderUserLatestFollowers(\stdClass $widgetItemInfo = null)
	{
		$widgetItemInfo->Title = empty($widgetItemInfo->Title) ? esc_html__('Followers', 'ultra-community') : esc_html($widgetItemInfo->Title);
		TemplatesController::loadTemplate(TemplatesController::WIDGET_USER_LATEST_FOLLOWERS_TEMPLATE, array('widgetItemInfo' => $widgetItemInfo));

	}

	private static function renderUserReviews(\stdClass $widgetItemInfo = null)
	{
		$widgetItemInfo->Title = empty($widgetItemInfo->Title) ? esc_html__('Reviews', 'ultra-community') : esc_html($widgetItemInfo->Title);
		!empty($widgetItemInfo->UserEntity) && ($widgetItemInfo->UserEntity instanceof UserEntity) ?: $widgetItemInfo->UserEntity = UserController::getProfiledUser();

		if(!UserController::getUserNumberOfReviews($widgetItemInfo->UserEntity))
			return;

		TemplatesController::loadTemplate(TemplatesController::WIDGET_USER_REVIEWS_TEMPLATE, array('widgetItemInfo' => $widgetItemInfo));

	}

	private static function renderUserLatestGroups(\stdClass $widgetItemInfo = null)
	{
		$widgetItemInfo->Title = empty($widgetItemInfo->Title) ? esc_html__('Groups', 'ultra-community') : esc_html($widgetItemInfo->Title);

		!empty($widgetItemInfo->UserEntity) && ($widgetItemInfo->UserEntity instanceof UserEntity) ?: $widgetItemInfo->UserEntity = UserController::getProfiledUser();

		if(empty($widgetItemInfo->UserEntity))
			return;

		$widgetItemInfo->ArrGroupEntities = GroupController::getUserJoinedGroups($widgetItemInfo->UserEntity, 1, 5);
		if(empty($widgetItemInfo->ArrGroupEntities))
			return;

		foreach ($widgetItemInfo->ArrGroupEntities as $groupEntity)
		{
			unset($groupEntity->Description);

			$groupEntity->Actions = GroupController::getGroupUserPossibleActions($groupEntity, UserController::getLoggedInUser());
			foreach ($groupEntity->Actions as $actionEntity) {
				$actionEntity->ActionType = (-1) * $actionEntity->ActionType;
			}

		}

		//print_r($widgetItemInfo->ArrGroupEntities);exit;


		TemplatesController::loadTemplate(TemplatesController::WIDGET_USER_LATEST_GROUPS_TEMPLATE, array('widgetItemInfo' => $widgetItemInfo));

	}


	private static function renderUserAboutMySelf(\stdClass $widgetItemInfo = null)
	{
		$widgetItemInfo->Title = empty($widgetItemInfo->Title) ? esc_html__('About Me', 'ultra-community') : esc_html($widgetItemInfo->Title);

		!empty($widgetItemInfo->UserEntity) && ($widgetItemInfo->UserEntity instanceof UserEntity) ?: $widgetItemInfo->UserEntity = UserController::getProfiledUser();

		if(empty($widgetItemInfo->UserEntity))
			return;


		TemplatesController::loadTemplate(TemplatesController::WIDGET_USER_ABOUT_MYSELF_TEMPLATE, array('widgetItemInfo' => $widgetItemInfo));

	}


	private static function renderUserSocialNetworks(\stdClass $widgetItemInfo = null)
	{
		$arrSocialNetworks = array();

		foreach ( UltraCommHelper::getUserSocialNetworksProfileFields( isset($widgetItemInfo->UserKey) ? $widgetItemInfo->UserKey : UserController::getProfiledUser() ) as $socialNetworkField ) {
			$icon = $socialNetworkField->getFontAwesomeClass( $socialNetworkField->NetworkId );
			if ( empty( $icon ) || empty( $socialNetworkField->Value ) ) {
				continue;
			}

			$socialNetworkItem = new \stdClass();
			$socialNetworkItem->Icon = $icon;
			$socialNetworkItem->Url  = esc_url($socialNetworkField->Value);
			$socialNetworkItem->Name = $socialNetworkField->getNetworkName($socialNetworkField->NetworkId);

			$arrSocialNetworks[] = $socialNetworkItem;
		}

		$widgetTitle = esc_html__("Let's Connect", 'ultra-community');

		TemplatesController::loadTemplate(TemplatesController::WIDGET_USER_SOCIAL_NETWORKS_TEMPLATE, array('widgetTitle' => $widgetTitle, 'arrSocialNetworks' => $arrSocialNetworks));

	}


	private static function renderUserLatestPosts(\stdClass $widgetItemInfo = null)
	{
		if(MchUtils::isNullOrEmpty($userId = UserRepository::getUserIdFromKey(isset($widgetItemInfo->UserKey) ? $widgetItemInfo->UserKey : UserController::getProfiledUser())))
			return;

		$postsPerPage    = 5;
		$arrUserPosts = array();

		foreach (WpPostRepository::getUserPosts($userId, 1, $postsPerPage, array('post_type' => UltraCommHelper::getUserDisplayablePostTypes(UserController::getProfiledUser()))) as $wpUserPost)
		{
			$userPost = new \stdClass();

			$userPost->Id       = $wpUserPost->ID;
			$userPost->Title    = get_the_title($wpUserPost);
			$userPost->Url      = esc_url(get_permalink($wpUserPost->ID ));
			$userPost->Date     = get_the_date('M j, Y', $wpUserPost);
			$userPost->Comments = $wpUserPost->comment_count;

			$userPost->ThumbUrl = UltraCommUtils::getPostThumbnailUrl($userPost->Id, 'thumbnail');
			//!empty($userPost->ThumbUrl) ?: $userPost->ThumbUrl = UltraCommUtils::getPostDefaultThumbnailUrl($userPost->Id);

			if(empty($userPost->ThumbUrl))
			{
				$userPost->Icon = UltraCommHelper::getPostFontAwesomeIcon($userPost->Id);
				unset($userPost->ThumbUrl);
			}

			$arrUserPosts[] = $userPost;

		}

		if(empty($arrUserPosts))
			return;

		$widgetTitle = esc_html__('Latest Posts', 'ultra-community');

		TemplatesController::loadTemplate(TemplatesController::WIDGET_USER_LATEST_POSTS_TEMPLATE, array('arrUserPosts' => $arrUserPosts, 'widgetTitle' => $widgetTitle));

	}




	private function __construct()
	{}

}