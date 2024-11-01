<?php


namespace UltraCommunity\Entities;


class UserRelationEntity
{

	CONST RELATION_TYPE_FRIENDSHIP  = 1;
	CONST RELATION_TYPE_FOLLOWING   = 2;
	CONST RELATION_TYPE_FOLLOWED    = 3;

	CONST RELATION_TYPE_BLOCKING    = 9;

	CONST RELATION_STATUS_ACTIVE  = 1;
	CONST RELATION_STATUS_PENDING = 2;
//	CONST RELATION_STATUS_BLOCKED = 3;

	public $RelationId      = null;
	public $PrimaryUserId   = null;
	public $SecondaryUserId = null;
	public $RelationTypeId  = null;
	public $CreatedDate     = null;
	public $StatusId        = null;

	public function __construct($relationId, $primaryUserId, $secondaryUserId, $relationTypeId, $statusId = null)
	{
		$this->RelationId      = (int)$relationId;
		$this->PrimaryUserId   = (int)$primaryUserId;
		$this->SecondaryUserId = (int)$secondaryUserId;
		$this->RelationTypeId  = (int)$relationTypeId;
		$this->StatusId        = (int)$statusId;
	}
}