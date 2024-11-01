<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Modules\GeneralSettings\User;


use UltraCommunity\Modules\BasePublicModule;
use UltraCommunity\Entities\UserEntity;

class UserSettingsPublicModule extends BasePublicModule
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public static function isUserGravatarUrlEnabled()
	{
		return !!self::getInstance()->getOption(UserSettingsAdminModule::OPTION_ENABLE_GRAVATAR_URL);
	}
	
	public static function shouldRedirectAuthorToProfile()
	{
		return self::getInstance()->getOption(UserSettingsAdminModule::OPTION_REDIRECT_AUTHOR_USER_URL);
	}
	
	public static function shouldRedirectCommentAuthorToProfile()
	{
		return self::getInstance()->getOption(UserSettingsAdminModule::OPTION_REDIRECT_COMMENT_USER_URL);
	}

	public function getUserDisplayName(UserEntity $userEntity = null)
	{
		if(empty($userEntity->Id))
			return null;

		$userDisplayName = '';
		
		switch ($this->getOption(UserSettingsAdminModule::OPTION_DISPLAY_NAME))
		{
			case UserSettingsAdminModule::USER_DISPLAY_NAME_FIRST_LAST_NAME :
				$userDisplayName =  $userEntity->UserMetaEntity->FirstName . ' ' . $userEntity->UserMetaEntity->LastName; break;

			case UserSettingsAdminModule::USER_DISPLAY_NAME_LAST_FIRST_NAME :
				$userDisplayName =  $userEntity->UserMetaEntity->LastName . ' ' . $userEntity->UserMetaEntity->FirstName; break;

			case UserSettingsAdminModule::USER_DISPLAY_NAME_FIRST_NAME :
				$userDisplayName = $userEntity->UserMetaEntity->FirstName; break;

		}
		
		$userDisplayName = \trim($userDisplayName);
		
		if(!empty($userDisplayName))
			return $userDisplayName;
		
		if(!empty($userEntity->DisplayName))
			return $userEntity->DisplayName;
		
		return empty($userEntity->UserMetaEntity->NickName) ? $userEntity->UserName : $userEntity->UserMetaEntity->NickName;
		
	
	}


}