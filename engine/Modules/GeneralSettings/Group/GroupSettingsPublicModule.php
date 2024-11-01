<?php
namespace UltraCommunity\Modules\GeneralSettings\Group;

use UltraCommunity\Modules\BasePublicModule;

class GroupSettingsPublicModule extends BasePublicModule
{
	public function __construct()
	{
		parent::__construct();
	}

	public static function isGroupModuleActive()
	{
		return !!self::getInstance()->getOption(GroupSettingsAdminModule::OPTION_GROUPS_ENABLED);
	}

}