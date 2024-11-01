<?php

namespace UltraCommunity\Modules\Appearance\UserProfile;

use UltraCommunity\Controllers\UserController;
use UltraCommunity\Entities\PageActionEntity;
use UltraCommunity\Modules\BasePublicModule;
use UltraCommunity\UltraCommHooks;

class UserProfileAppearancePublicModule extends BasePublicModule
{
	public function __construct()
	{
		parent::__construct();
	}

	public static function getHeaderStyleId()
	{
		$headerStyleId =  (int)self::getInstance()->getOption(UserProfileAppearanceAdminModule::OPTION_HEADER_STYLE_ID);
		$headerStyleId = (int)apply_filters(UltraCommHooks::FILTER_USER_PROFILE_PAGE_HEADER_STYLE, $headerStyleId, UserController::getProfiledUser() ? UserController::getProfiledUser()->Id : 0);

		return empty($headerStyleId) ? 3 : $headerStyleId;
	}

	public static function isCustomTabProfileSection($sectionSlug)
	{
		$arrCustomSections = self::getAllCustomTabProfileSections();
		return isset($arrCustomSections[$sectionSlug]);
	}

	public static function getAllCustomTabProfileSections()
	{
		return UserProfileAppearanceAdminModule::getCustomTabsUserProfileSections();
	}

	public static function getActiveUserProfileSections()
	{

		static $arrProfileSections = null;

		return $arrProfileSections !== null ? $arrProfileSections : $arrProfileSections = (array)apply_filters(UltraCommHooks::FILTER_USER_PROFILE_PAGE_ACTIVE_SECTIONS,
				(array)self::getInstance()->getOption(UserProfileAppearanceAdminModule::OPTION_USER_PROFILE_SECTIONS), (UserController::getProfiledUser() ? UserController::getProfiledUser()->Id : 0));
	}

	public static function getAllDefinedUserProfileSections()
	{
		static $arrProfileSections = null;
		return (null !== $arrProfileSections) ? $arrProfileSections : $arrProfileSections = UserProfileAppearanceAdminModule::getDefinedUserProfileSections();
	}

	public static function getUserProfileSectionNameBySlug($profileSectionSlug)
	{
		$arrDefinedSections = self::getAllDefinedUserProfileSections();

		$profileSectionName = isset($arrDefinedSections[$profileSectionSlug]) ? $arrDefinedSections[$profileSectionSlug] : null;

		return \apply_filters(UltraCommHooks::FILTER_USER_PROFILE_PAGE_SECTION_NAME, $profileSectionName, $profileSectionSlug);

	}

	public static function getAllDefinedUserStatsCounters()
	{
		static $arrUserStats = null;
		return null !== $arrUserStats ? $arrUserStats : UserProfileAppearanceAdminModule::getDefinedUserStatsCounters();
	}

	public static function getUserStatsCounterNameBySlug($statsCounterSlug)
	{
		$arrUserStats = self::getAllDefinedUserStatsCounters();
		return isset($arrUserStats[$statsCounterSlug]) ? $arrUserStats[$statsCounterSlug] : null;
	}

	public static function getUserProfileHeaderStatsCounters()
	{
		return (array)self::getInstance()->getOption(UserProfileAppearanceAdminModule::OPTION_HEADER_STATS_COUNTERS);
	}

	public static function getUserProfileSideBarPosition()
	{
		return apply_filters(UltraCommHooks::FILTER_USER_PROFILE_PAGE_SIDEBAR_POSITION, self::getInstance()->getOption(UserProfileAppearanceAdminModule::OPTION_SIDE_BAR_POSITION), UserController::getProfiledUser() ? UserController::getProfiledUser()->Id : 0);
	}

	public static function showUserOnlineStatusInHeader()
	{
		return (bool)self::getInstance()->getOption(UserProfileAppearanceAdminModule::OPTION_HEADER_SHOW_ONLINE_STATUS);
	}

	public static function showUserSocialNetworksInHeader()
	{
		return (bool)self::getInstance()->getOption(UserProfileAppearanceAdminModule::OPTION_HEADER_SHOW_SOCIAL_NETWORKS);
	}

	public static function showCircledSocialNetworksIconsInHeader()
	{
		return self::getInstance()->getOption(UserProfileAppearanceAdminModule::OPTION_HEADER_SOCIAL_NETWORKS_STYLE) != UserProfileAppearanceAdminModule::PICTURE_STYLE_SQUARE;
	}

	public static function showCircledUserPictureInHeader()
	{
		return self::getInstance()->getOption(UserProfileAppearanceAdminModule::OPTION_HEADER_USER_PICTURE_STYLE) != UserProfileAppearanceAdminModule::PICTURE_STYLE_SQUARE;
	}

	public static function getUserProfileSectionFontAwesomeIcon($profileSectionSlug)
	{
		$faIcon = null;

		switch ($profileSectionSlug)
		{
			case UserProfileAppearanceAdminModule::PROFILE_SECTION_ACTIVITY :
				$faIcon = 'fa-bars';break;

			case UserProfileAppearanceAdminModule::PROFILE_SECTION_ABOUT :
				$faIcon = 'fa-info';break;

			case UserProfileAppearanceAdminModule::PROFILE_SECTION_POSTS :
				$faIcon = 'fa-pencil';break;

			case UserProfileAppearanceAdminModule::PROFILE_SECTION_COMMENTS :
				$faIcon = 'fa-comments-o';break;

			case UserProfileAppearanceAdminModule::PROFILE_SECTION_FRIENDS :
				$faIcon = 'fa-handshake-o';break;

			case UserProfileAppearanceAdminModule::PROFILE_SECTION_FOLLOWERS :
			case UserProfileAppearanceAdminModule::PROFILE_SECTION_FOLLOWING :
			$faIcon = PageActionEntity::getSvgIcon('user-fa-friends-solid');break;

			case UserProfileAppearanceAdminModule::PROFILE_SECTION_FORUMS        :
			case UserProfileAppearanceAdminModule::PROFILE_SECTION_FORUMS_TOPICS :
				$faIcon = 'fa-comments';break;

			case UserProfileAppearanceAdminModule::PROFILE_SECTION_FORUMS_REPLIES :
				$faIcon = 'fa-commenting';break;

			case UserProfileAppearanceAdminModule::PROFILE_SECTION_FORUMS_FAVORITES :
				$faIcon = 'fa-heart';break;

			case UserProfileAppearanceAdminModule::PROFILE_SECTION_REVIEWS :
				$faIcon = 'fa-star';break;

			case UserProfileAppearanceAdminModule::PROFILE_SECTION_GROUPS :
				$faIcon = PageActionEntity::getSvgIcon('group-users-solid');break;
		}

		if(null === $faIcon && self::isCustomTabProfileSection($profileSectionSlug))
		{
			$arrCustomSections = self::getAllCustomTabProfileSections();
			empty($arrCustomSections[$profileSectionSlug]->IconClass) ?: $faIcon = $arrCustomSections[$profileSectionSlug]->IconClass;
		}


		return apply_filters(UltraCommHooks::FILTER_USER_PROFILE_PAGE_SECTION_ICON, $faIcon, $profileSectionSlug);
	}


}