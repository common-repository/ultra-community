<?php
namespace  UltraCommunity\Modules\Appearance\Directories;

use UltraCommunity\Modules\BasePublicModule;
use UltraCommunity\Modules\MembersDirectory\MembersDirectoryAdminModule;
use UltraCommunity\Modules\MembersDirectory\MembersDirectoryPublicModule;

class DirectoriesAppearancePublicModule extends BasePublicModule
{
	CONST DIRECTORY_TYPE_MEMBERS = 1;
	CONST DIRECTORY_TYPE_GROUPS  = 2;

	protected function __construct()
	{
		parent::__construct();
	}

	public static function showStatsHighlighted($directoryType)
	{
		$optionName = ((int)$directoryType === self::DIRECTORY_TYPE_MEMBERS) ?
			DirectoriesAppearanceAdminModule::OPTION_MEMBERS_HIGHLIGHT_STATS :
			DirectoriesAppearanceAdminModule::OPTION_GROUPS_HIGHLIGHT_STATS;

		return self::getInstance()->getOption($optionName);

	}

	public static function showSquarePicture($directoryType)
	{
		$optionName = ((int)$directoryType === self::DIRECTORY_TYPE_MEMBERS) ?
			DirectoriesAppearanceAdminModule::OPTION_MEMBERS_PICTURE_STYLE :
			DirectoriesAppearanceAdminModule::OPTION_GROUPS_PICTURE_STYLE;

		return self::getInstance()->getOption($optionName) != DirectoriesAppearanceAdminModule::PICTURE_STYLE_CIRCLE;

	}



	public static function showMembersSocialNetworks()
	{
		return self::getInstance()->getOption(DirectoriesAppearanceAdminModule::OPTION_MEMBERS_DISPLAY_SOCIAL_NETWORKS);
	}

	public static function showMemberOnlineStatus()
	{
		return self::getInstance()->getOption(DirectoriesAppearanceAdminModule::OPTION_MEMBERS_DISPLAY_ONLINE_STATUS);
	}

	public static function showMemberRatings()
	{
		return self::getInstance()->getOption(DirectoriesAppearanceAdminModule::OPTION_MEMBERS_DISPLAY_RATINGS);
	}

	public static function getPaginationType($directoryType)
	{
		$paginationType = DirectoriesAppearanceAdminModule::PAGINATION_TYPE_SCROLL;
		switch ($directoryType)
		{
			case self::DIRECTORY_TYPE_MEMBERS : $paginationType = self::getInstance()->getOption(DirectoriesAppearanceAdminModule::OPTION_MEMBERS_PAGINATION_TYPE);break;
			case self::DIRECTORY_TYPE_GROUPS  : $paginationType = self::getInstance()->getOption(DirectoriesAppearanceAdminModule::OPTION_GROUPS_PAGINATION_TYPE);break;
		}

		return $paginationType;
	}

	public static function showCoverImage($directoryType)
	{
		$optionName = ((int)$directoryType === self::DIRECTORY_TYPE_MEMBERS) ?
			DirectoriesAppearanceAdminModule::OPTION_MEMBERS_DISPLAY_COVER :
			DirectoriesAppearanceAdminModule::OPTION_GROUPS_DISPLAY_COVER;

		return self::getInstance()->getOption($optionName);

	}


	public static function showStatsAsIcons($directoryType)
	{
		$displayStatsType = DirectoriesAppearanceAdminModule::DISPLAY_STATS_TYPE_NUMBERS;

		switch ($directoryType)
		{
			case self::DIRECTORY_TYPE_MEMBERS : $displayStatsType = self::getInstance()->getOption(DirectoriesAppearanceAdminModule::OPTION_MEMBERS_DISPLAY_STATS_TYPE);break;
			case self::DIRECTORY_TYPE_GROUPS  : $displayStatsType = self::getInstance()->getOption(DirectoriesAppearanceAdminModule::OPTION_GROUPS_DISPLAY_STATS_TYPE);break;
		}

		return $displayStatsType == DirectoriesAppearanceAdminModule::DISPLAY_STATS_TYPE_ICONS;
	}

	public static function getRecordsPerPage($directoryType)
	{
		$recordsPerPage = 0;
		switch ($directoryType)
		{
			case self::DIRECTORY_TYPE_MEMBERS :
				$recordsPerPage = (int)self::getInstance()->getOption(DirectoriesAppearanceAdminModule::OPTION_MEMBERS_PER_PAGE); break;
			case self::DIRECTORY_TYPE_GROUPS  :
				$recordsPerPage = (int)self::getInstance()->getOption(DirectoriesAppearanceAdminModule::OPTION_GROUPS_PER_PAGE); break;
		}
		
		return ($recordsPerPage <= 0 ) ? 9 : $recordsPerPage;

	}



}