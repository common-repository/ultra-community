<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Entities;


use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\UltraCommHelper;

class UserEntity
{
	public $Id = null;
	public $Email = null;
	public $UserName = null;
	public $Password = null;
	public $NiceName = null;
	public $DisplayName = null;
	public $RegisteredDate = null;
	public $WebSiteUrl = null;

	public $IsUltraCommUser = null;


	/**
	 * @var UserMetaEntity
	 */
	public $UserMetaEntity = null;

//	/**
//	 * @var UserRoleEntity
//	 */
//	public $UserRoleEntity = null;

	/**
	 * @var UserPrivacyEntity
	 */
	private $UserPrivacyEntity = null;

	public function __construct()
	{}

	/**
	 * @return UserPrivacyEntity
	 */
	public function getPrivacyEntity()
	{
		if($this->UserPrivacyEntity instanceof UserPrivacyEntity)
			return $this->UserPrivacyEntity;

		$this->UserPrivacyEntity = new UserPrivacyEntity($this->Id);

		return $this->UserPrivacyEntity;

	}

	public function fromWPUser($wpUserInfo)
	{
		if(!MchWpUtils::isWPUser($wpUserInfo))
			return $this;

		empty( $wpUserInfo->ID )              ?: $this->Id             = (int)$wpUserInfo->ID;
		empty( $wpUserInfo->user_login )      ?: $this->UserName       = $wpUserInfo->user_login;
		empty( $wpUserInfo->user_nicename )   ?: $this->NiceName       = $wpUserInfo->user_nicename;
		empty( $wpUserInfo->user_email )      ?: $this->Email          = $wpUserInfo->user_email;
		empty( $wpUserInfo->user_registered ) ?: $this->RegisteredDate = $wpUserInfo->user_registered;
		empty( $wpUserInfo->display_name )    ?: $this->DisplayName    = $wpUserInfo->display_name;
		empty( $wpUserInfo->user_url )        ?: $this->WebSiteUrl     = $wpUserInfo->user_url;

		($this->UserMetaEntity instanceof UserMetaEntity) ?: $this->UserMetaEntity = new UserMetaEntity();

		$this->UserMetaEntity->FirstName    = empty($wpUserInfo->first_name)  ? null : $wpUserInfo->first_name;
		$this->UserMetaEntity->LastName     = empty($wpUserInfo->last_name)   ? null : $wpUserInfo->last_name;
		$this->UserMetaEntity->Description  = empty($wpUserInfo->description) ? null : $wpUserInfo->description;
		$this->UserMetaEntity->NickName     = empty($wpUserInfo->nickname)    ? null : $wpUserInfo->nickname;

		return $this;
	}

	public function toWPUser()
	{
		$wpUserInfo = new \WP_User((int)$this->Id);

		//empty($this->Id)             ?: $wpUserInfo->ID              = $this->Id;
		empty($this->UserName)       ?: $wpUserInfo->user_login      = $this->UserName;
		empty($this->NiceName)       ?: $wpUserInfo->user_nicename   = $this->NiceName;
		empty($this->Email)          ?: $wpUserInfo->user_email      = $this->Email;
		empty($this->RegisteredDate) ?: $wpUserInfo->user_registered = $this->RegisteredDate;
		empty($this->DisplayName)    ?: $wpUserInfo->display_name    = $this->DisplayName;
		empty($this->WebSiteUrl)     ?: $wpUserInfo->user_url        = $this->WebSiteUrl;
		empty($this->Password)       ?: $wpUserInfo->user_pass       = $this->Password;

		empty($this->UserMetaEntity->FirstName)    ?: $wpUserInfo->first_name  = $this->UserMetaEntity->FirstName;
		empty($this->UserMetaEntity->LastName)     ?: $wpUserInfo->last_name   = $this->UserMetaEntity->LastName;
		empty($this->UserMetaEntity->Description)  ?: $wpUserInfo->description = $this->UserMetaEntity->Description;
		empty($this->UserMetaEntity->NickName)     ?: $wpUserInfo->nickname    = $this->UserMetaEntity->NickName;

		return $wpUserInfo;
	}

	public function __clone()
	{
		(null === $this->UserMetaEntity)    ?: $this->UserMetaEntity    = clone  $this->UserMetaEntity;
		(null === $this->UserPrivacyEntity) ?: $this->UserPrivacyEntity = clone  $this->UserPrivacyEntity;

	}

	public function __sleep()
	{

		$arrObjectProperties = get_object_vars(MchUtils::filterObjectEmptyProperties($this));
		unset(
			$arrObjectProperties['UserPrivacyEntity'],
			$arrObjectProperties['RegisteredDate'],
			$arrObjectProperties['DisplayName'],
			$arrObjectProperties['WebSiteUrl'],
			$arrObjectProperties['Password'],
			$arrObjectProperties['UserName'],
			$arrObjectProperties['NiceName'],
			$arrObjectProperties['Email'],
			$arrObjectProperties['Id']

		);

		return array_keys($arrObjectProperties);

	}



//	public function __wakeup()
//	{
//		$this->UserMetaEntity instanceof UserMetaEntity ?: $this->UserMetaEntity = new UserMetaEntity();
//		$this->UserRoleEntity instanceof UserRoleEntity ?: $this->UserRoleEntity = new UserRoleEntity();
//	}

}