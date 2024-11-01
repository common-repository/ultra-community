<?php
namespace UltraCommunity\Entities;


use UltraCommunity\Controllers\ModulesController;
use UltraCommunity\Controllers\TemplatesController;
use UltraCommunity\Controllers\UserController;
use UltraCommunity\Controllers\UserRelationsController;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Repository\BaseRepository;

class UserPrivacyEntity
{
	CONST META_KEY_HIDE_ONLINE_STATUS  = '_uc-hide-online_status';
	CONST META_KEY_HIDE_IN_SEARCHES    = '_uc-hide-in-searches';
	CONST META_KEY_HIDE_IN_DIRECTORIES = '_uc-hide-in-directories';
	CONST META_KEY_PROFILE_VISIBILITY  = '_uc_profile_visibility';


	CONST PROFILE_VISIBILITY_EVERYONE                   = 1;
	CONST PROFILE_VISIBILITY_SITE_MEMBERS               = 2;
	CONST PROFILE_VISIBILITY_ONLY_FRIENDS               = 3;
	CONST PROFILE_VISIBILITY_ONLY_FOLLOWERS             = 4;
	CONST PROFILE_VISIBILITY_ONLY_FRIENDS_AND_FOLLOWERS = 5;

	private $hideOnlineStatus  = false;
	private $hideInDirectories = false;
	private $hideInSearches    = false;
	private $profileVisibility = null;

	private $userId = 0;

	private $profilePrivacyNoticeObject = -1;


	public function __construct($userKey)
	{
		if(! ($userId = BaseRepository::getUserIdFromKey($userKey)) )
			return;

		$this->userId = $userId;

		$this->hideOnlineStatus  = metadata_exists( 'user', $userId, self::META_KEY_HIDE_ONLINE_STATUS );
		$this->hideInSearches    = metadata_exists( 'user', $userId, self::META_KEY_HIDE_IN_SEARCHES );
		$this->hideInDirectories = metadata_exists( 'user', $userId, self::META_KEY_HIDE_IN_DIRECTORIES );

		$this->profileVisibility = (int)get_user_meta( $userId, self::META_KEY_PROFILE_VISIBILITY, true );

		switch($this->profileVisibility)
		{
			case self::PROFILE_VISIBILITY_ONLY_FRIENDS               : ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FRIENDS)   ?: $this->profileVisibility = 0; break;
			case self::PROFILE_VISIBILITY_ONLY_FOLLOWERS             : ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FOLLOWERS) ?: $this->profileVisibility = 0; break;
			case self::PROFILE_VISIBILITY_ONLY_FRIENDS_AND_FOLLOWERS : ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FRIENDS) && ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FOLLOWERS) ?: $this->profileVisibility = 0; break;
		}

		$this->profileVisibility ?: $this->profileVisibility = self::PROFILE_VISIBILITY_EVERYONE;


	}

	public static function getProfileVisibilityOptions()
	{
		$arrOptions = array(
			self::PROFILE_VISIBILITY_EVERYONE                   => esc_html__('Everyone', 'ultra-community'),
			self::PROFILE_VISIBILITY_SITE_MEMBERS               => esc_html__('Site Members', 'ultra-community'),
			self::PROFILE_VISIBILITY_ONLY_FRIENDS               => esc_html__('Only Friends', 'ultra-community'),
			self::PROFILE_VISIBILITY_ONLY_FOLLOWERS             => esc_html__('Only Followers', 'ultra-community'),
			self::PROFILE_VISIBILITY_ONLY_FRIENDS_AND_FOLLOWERS => esc_html__('Friends and Followers', 'ultra-community'),
		);

		if(!ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FRIENDS))
		{
			unset($arrOptions[self::PROFILE_VISIBILITY_ONLY_FRIENDS_AND_FOLLOWERS], $arrOptions[self::PROFILE_VISIBILITY_ONLY_FRIENDS]);
		}

		if(!ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FOLLOWERS))
		{
			unset($arrOptions[self::PROFILE_VISIBILITY_ONLY_FRIENDS_AND_FOLLOWERS], $arrOptions[self::PROFILE_VISIBILITY_ONLY_FOLLOWERS]);
		}

		return $arrOptions;
	}


	public function getProfileVisibilityValue()
	{
		return $this->profileVisibility;
	}

	public function hideProfileInDirectories()
	{
		return $this->hideInDirectories;
	}

	public function hideProfileInSearches()
	{
		return $this->hideInSearches;
	}

	public function hideOnlineStatus()
	{
		return $this->hideOnlineStatus;
	}

	public function showOnlineStatus()
	{
		return !$this->hideOnlineStatus();
	}

	public function showProfileInDirectories()
	{
		return !$this->hideProfileInDirectories();
	}

	public function showProfileInSearches()
	{
		return !$this->hideProfileInSearches();
	}

	public function userHasPublicProfile()
	{
		return $this->profileVisibility === self::PROFILE_VISIBILITY_EVERYONE;
	}


	public function userHasProfileVisibilityNotice()
	{
		$this->setProfilePrivacyNoticeObject();
		return null !== $this->profilePrivacyNoticeObject;
	}



	public function getProfilePrivacyNotice($asStandardObject = false)
	{
		if(!$this->userHasProfileVisibilityNotice())
			return null;

		return $asStandardObject ? $this->profilePrivacyNoticeObject : TemplatesController::getTemplateOutputContent(TemplatesController::PAGE_CONTENT_NOTICE, array('pageNotice' => $this->profilePrivacyNoticeObject));

	}

	private function setProfilePrivacyNoticeObject()
	{
		if(-1 !== $this->profilePrivacyNoticeObject)
			return;

		if( $this->userHasPublicProfile() || $this->userId === UserController::getLoggedInUserId() || MchWpUtils::isAdminLoggedIn())
		{
			$this->profilePrivacyNoticeObject = null;
			return;
		}


		$pageNoticeItem = new \stdClass();
		$pageNoticeItem->Type     = 'info';
		$pageNoticeItem->Message  = null;


		switch($this->profileVisibility)
		{
			case self::PROFILE_VISIBILITY_SITE_MEMBERS               : if(UserController::isUserLoggedIn()) $this->profilePrivacyNoticeObject = null; break;
			case self::PROFILE_VISIBILITY_ONLY_FRIENDS               : if(UserRelationsController::activeFriendshipExistsBetween($this->userId, UserController::getLoggedInUser())) $this->profilePrivacyNoticeObject = null; break;
			case self::PROFILE_VISIBILITY_ONLY_FOLLOWERS             : if(UserRelationsController::isUserFollowing(UserController::getLoggedInUser(), $this->userId)) $this->profilePrivacyNoticeObject = null; break;
			case self::PROFILE_VISIBILITY_ONLY_FRIENDS_AND_FOLLOWERS :
				if(
						UserRelationsController::isUserFollowing(UserController::getLoggedInUser(), $this->userId)  ||
						UserRelationsController::activeFriendshipExistsBetween($this->userId, UserController::getLoggedInUser())
				) $this->profilePrivacyNoticeObject = null;
				break;

		}

		if(null === $this->profilePrivacyNoticeObject)
			return;

		switch($this->profileVisibility)
		{
			case self::PROFILE_VISIBILITY_SITE_MEMBERS               : $pageNoticeItem->Message = esc_html__('This profile is accessible to logged in users only!', 'ultra-community'); break;
			case self::PROFILE_VISIBILITY_ONLY_FRIENDS               : $pageNoticeItem->Message = esc_html__('This profile is accessible to friends only!', 'ultra-community'); break;
			case self::PROFILE_VISIBILITY_ONLY_FOLLOWERS             : $pageNoticeItem->Message = esc_html__('This profile is accessible to followers only!', 'ultra-community'); break;
			case self::PROFILE_VISIBILITY_ONLY_FRIENDS_AND_FOLLOWERS : $pageNoticeItem->Message = esc_html__('This profile is accessible to friends and followers only!', 'ultra-community'); break;
		}

		$this->profilePrivacyNoticeObject = $pageNoticeItem;
	}

}