<?php
namespace UltraCommunity\Entities;

class GroupUserEntity
{
	CONST GROUP_USER_STATUS_ACTIVE  = 1;
	CONST GROUP_USER_STATUS_INVITED = 2;
	CONST GROUP_USER_STATUS_PENDING = 3;
	CONST GROUP_USER_STATUS_BLOCKED = 9;
	
	CONST GROUP_USER_TYPE_ADMIN      = 1;
	CONST GROUP_USER_TYPE_MODERATOR  = 2;
	CONST GROUP_USER_TYPE_MEMBER     = 3;

	public $GroupUserId   = null;
	public $GroupId       = null;
	public $UserId        = null;
	public $JoinedDate    = null;
	public $UserStatusId  = null;
	public $UserTypeId    = null;

	public function __construct($groupUserId = null, $groupId = null, $userId = null)
	{
		$this->GroupUserId = (int)$groupUserId;
		$this->GroupId     = (int)$groupId;
		$this->UserId      = (int)$userId;
	}

}