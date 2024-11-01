<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Entities;

use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;

class UserMetaEntity
{
	CONST USER_STATUS_APPROVED                    = 1;
	CONST USER_STATUS_AWAITING_REVIEW             = 2;
	CONST USER_STATUS_AWAITING_EMAIL_CONFIRMATION = 3;

	CONST USER_STATUS_PENDING_PAYMENT             = 4;

	public $FirstName          = null;
	public $LastName           = null;
	public $NickName           = null;
	public $Description        = null;

	public $UserStatus         = null;
	public $ProfileFormValues  = array();
	public $RegisterFormValues = array();


	public $AvatarFileName       = null;
	public $ProfileCoverFileName = null;
	public $LastLoggedIn         = null;

	public function __construct()
	{
		$this->ProfileFormValues  = array();
		$this->RegisterFormValues = array();
	}

	public function hasValidUserStatus()
	{
		if(empty($this->UserStatus)){
			return false;
		}

		$this->UserStatus = (int)$this->UserStatus;

		$isValid = false;
		switch($this->UserStatus)
		{
			case self::USER_STATUS_APPROVED                    :
			case self::USER_STATUS_AWAITING_REVIEW             :
			case self::USER_STATUS_AWAITING_EMAIL_CONFIRMATION :
			case self::USER_STATUS_PENDING_PAYMENT             :
			$isValid = true; break;

		}

		return $isValid; // add a filter for status here
	}

//
	public static function getUserStatusDescription($userStatusId)
	{
		switch($userStatusId)
		{
			case self::USER_STATUS_APPROVED                    : return __('Approved', 'ultra-community');
			case self::USER_STATUS_AWAITING_REVIEW             : return __('Awaiting Review', 'ultra-community');
			case self::USER_STATUS_AWAITING_EMAIL_CONFIRMATION : return __('Awaiting Confirmation', 'ultra-community');
			case self::USER_STATUS_PENDING_PAYMENT             : return __('Payment Pending', 'ultra-community');
		}

		return null;  // add a filter for status here
	}

	public function __sleep()
	{
		$arrObjectProperties = get_object_vars(MchUtils::filterObjectEmptyProperties($this));
		unset($arrObjectProperties['FirstName'], $arrObjectProperties['LastName'], $arrObjectProperties['NickName'], $arrObjectProperties['Description']);

		!isset($this->UserStatus) ?: $this->UserStatus = (int)$this->UserStatus;

		return array_keys($arrObjectProperties);

	}

}