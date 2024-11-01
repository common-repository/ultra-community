<?php

namespace UltraCommunity\Modules\GroupsDirectory;

use UltraCommunity\Controllers\UserController;
use UltraCommunity\Modules\BasePublicModule;
use UltraCommunity\PostsType\GroupsDirectoryPostType;
use UltraCommunity\UltraCommHelper;

class GroupsDirectoryPublicModule extends BasePublicModule
{
	protected function __construct()
	{
		parent::__construct();
	}

//	public static function getGroupTypesToList(GroupsDirectoryPostType $directoryPostType = null)
//	{
//
//		$arrListGroupType = (array)self::getInstance()->getOption();
//		print_r($arrListGroupType);exit;
//		return $arrListGroupType;
//	}
//
//	public static function userCanSeeDirectory($userKey)
//	{
//		self::getGroupTypesToList();
//
//		$arrAllowedUserRoles = (array)self::getInstance()->getOption(GroupsDirectoryAdminModule::OPTION_ALLOWED_USER_ROLES);
//
//
//		print_r($arrAllowedUserRoles);exit;
//
//		//UltraCommHelper::getUserRolePublicInstanceByUserInfo()
//		//UserController::getUser
//	}
}