<?php


namespace UltraCommunity\Modules\Appearance\GroupProfile;

use UltraCommunity\Controllers\GroupController;
use UltraCommunity\Modules\BasePublicModule;
use UltraCommunity\UltraCommHooks;

class GroupProfileAppearancePublicModule extends BasePublicModule
{
	public function __construct()
	{
		parent::__construct();
	}

	public static function getHeaderStyleId()
	{
		return 1;
	}

	public static function isCustomTabProfileSection($sectionSlug)
	{
		$arrCustomSections = self::getAllCustomTabProfileSections();
		return isset($arrCustomSections[$sectionSlug]);
	}

	public static function getAllCustomTabProfileSections()
	{
		return GroupProfileAppearanceAdminModule::getCustomTabsGroupProfileSections();
	}

	public static function getActiveGroupProfileSections()
	{
		return (array)apply_filters(UltraCommHooks::FILTER_GROUP_PROFILE_SECTIONS, (array)self::getInstance()->getOption(GroupProfileAppearanceAdminModule::OPTION_GROUP_PROFILE_SECTIONS), GroupController::getProfiledGroup() ? GroupController::getProfiledGroup()->Id : 0);
	}

	public static function getAllDefinedGroupProfileSections()
	{
		static $arrProfileSections = null;
		return (null !== $arrProfileSections) ? $arrProfileSections : $arrProfileSections = GroupProfileAppearanceAdminModule::getDefinedMenuSections();
	}

	public static function getGroupProfileSectionNameBySlug($profileSectionSlug)
	{
		$arrDefinedSections = self::getAllDefinedGroupProfileSections();
		return isset($arrDefinedSections[$profileSectionSlug]) ? $arrDefinedSections[$profileSectionSlug] : null;
	}

	public static function getSideBarPosition()
	{
		return self::getInstance()->getOption(GroupProfileAppearanceAdminModule::OPTION_SIDE_BAR_POSITION);
	}

	public static function getAllDefinedUserStatsCounters()
	{
		static $arrUserStats = null;
		return null !== $arrUserStats ? $arrUserStats : GroupProfileAppearanceAdminModule::getDefinedGroupStatsCounters();
	}

	public static function getGroupStatsCounterNameBySlug($statsCounterSlug)
	{
		$arrUserStats = self::getAllDefinedUserStatsCounters();
		return isset($arrUserStats[$statsCounterSlug]) ? $arrUserStats[$statsCounterSlug] : null;
	}

	public static function getGroupProfileHeaderStatsCounters()
	{
		return (array)self::getInstance()->getOption(GroupProfileAppearanceAdminModule::OPTION_HEADER_STATS_COUNTERS);
	}

	public static function showCircledPictureInHeader()
	{
		return self::getInstance()->getOption(GroupProfileAppearanceAdminModule::OPTION_HEADER_PICTURE_STYLE) != GroupProfileAppearanceAdminModule::PICTURE_STYLE_SQUARE;
	}

	public static function getGroupProfileSectionFontAwesomeIcon($profileSectionSlug)
	{
		$faIcon = null;

		switch ($profileSectionSlug)
		{
			case GroupProfileAppearanceAdminModule::PROFILE_SECTION_ABOUT :
				$faIcon = 'fa-info';break;

			case GroupProfileAppearanceAdminModule::PROFILE_SECTION_ACTIVITY :
				$faIcon = 'fa-home';break;

			case GroupProfileAppearanceAdminModule::PROFILE_SECTION_MEMBERS :
				$faIcon = 'fa-users';break;

		}

		if(null === $faIcon && self::isCustomTabProfileSection($profileSectionSlug))
		{
			$arrCustomSections = self::getAllCustomTabProfileSections();
			empty($arrCustomSections[$profileSectionSlug]->IconClass) ?: $faIcon = $arrCustomSections[$profileSectionSlug]->IconClass;
		}

		return \apply_filters(UltraCommHooks::FILTER_GROUP_PROFILE_PAGE_SECTION_ICON, $faIcon, $profileSectionSlug);
	}



}