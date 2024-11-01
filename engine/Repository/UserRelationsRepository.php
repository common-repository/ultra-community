<?php
namespace UltraCommunity\Repository;
use UltraCommunity\Entities\UserRelationEntity;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\WordPress\Repository\WpUserRepository;

final class UserRelationsRepository extends BaseRepository
{

	public static function saveUserRelation(UserRelationEntity $userRelationEntity)
	{

		!empty($userRelationEntity->CreatedDate) ?: $userRelationEntity->CreatedDate = current_time( 'mysql' );
		!empty($userRelationEntity->StatusId)    ?: $userRelationEntity->StatusId    = UserRelationEntity::RELATION_STATUS_ACTIVE;

		if(empty($userRelationEntity->RelationId))
		{
			return (false === self::getDbObject()->insert(self::getUserRelationsTableName(), array_filter((array)$userRelationEntity))) ? 0 : self::getDbObject()->insert_id;
		}

		$sqlQuery  = ' UPDATE ' . self::getUserRelationsTableName()  . ' SET';
		$sqlQuery .= ' PrimaryUserId    = ' . self::getDbObject()->prepare( '%d', $userRelationEntity->PrimaryUserId );
		$sqlQuery .= ' ,SecondaryUserId = ' . self::getDbObject()->prepare( '%d', $userRelationEntity->SecondaryUserId );
		$sqlQuery .= ' ,RelationTypeId  = ' . self::getDbObject()->prepare( '%d', $userRelationEntity->RelationTypeId );
		$sqlQuery .= ' ,StatusId        = ' . self::getDbObject()->prepare( '%d', $userRelationEntity->StatusId );
		$sqlQuery .= ' ,CreatedDate     = ' . self::getDbObject()->prepare( '%s', $userRelationEntity->CreatedDate );

		$sqlQuery .= ' WHERE RelationId = %d';

		return self::executePreparedQuery( self::getDbObject()->prepare( $sqlQuery, array($userRelationEntity->RelationId)) );

	}

	/**
	 * @param      $userKey
	 * @param null $relationTypeId
	 * @param int  $statusId
	 *
	 * @return UserRelationEntity[]
	 */
	public static function getUserRelations($userKey, $relationTypeId = null, $statusId = null) //UserRelationEntity::RELATION_STATUS_ACTIVE
	{
		$userId = self::getUserIdFromKey($userKey);
		if(empty($userId))
			return array();

		$sqlQuery  = 'SELECT * FROM ' . self::getUserRelationsTableName() . ' WHERE';
		$sqlQuery .= ' (PrimaryUserId = %d OR SecondaryUserId = %d) ';

		$arrParams   = array($userId, $userId);

		if(!empty($relationTypeId))
		{
			$sqlQuery    .= ' AND RelationTypeId = %d';
			$arrParams[] = $relationTypeId;
		}

		if(!empty($statusId))
		{
			$sqlQuery    .= ' AND  StatusId = %d';
			$arrParams[] = $statusId;
		}


		$sqlQuery .= ' ORDER BY CreatedDate DESC';

		//$arrCheckedUsers = array();

		$arrRelations = array();
		/**
		 * @var $userRelation UserRelationEntity
		 */
		foreach(self::executePreparedQuery( self::getDbObject()->prepare($sqlQuery,$arrParams) ) as $userRelation)
		{
			$userRelationEntity = new UserRelationEntity($userRelation->RelationId, $userRelation->PrimaryUserId, $userRelation->SecondaryUserId, $userRelation->RelationTypeId, $userRelation->StatusId);
			$userRelationEntity->CreatedDate = $userRelation->CreatedDate;

			$arrRelations[] = $userRelationEntity;
		}

		return $arrRelations;//self::executePreparedQuery( self::getDbObject()->prepare($sqlQuery,$arrParams) );

	}

	public static function countUserRelationsByType($userKey, $relationTypeId)
	{

		if( empty( $userId = self::getUserIdFromKey($userKey) ) )
			return 0;

		static $arrUserRelations = array();

		if(isset($arrUserRelations[$userId][$relationTypeId]))
			return $arrUserRelations[$userId][$relationTypeId];

		$arrUserRelations[$userId] = array();
		$relationsTableName = self::getUserRelationsTableName();

		$sqlQuery = "SELECT
					 COUNT( CASE WHEN RelationTypeId = 1 AND (PrimaryUserId = $userId OR SecondaryUserId  = $userId) THEN RelationId END ) AS Friends,
					 COUNT( CASE WHEN RelationTypeId = 2 AND PrimaryUserId  = $userId  THEN RelationId END ) AS Following,
					 COUNT( CASE WHEN RelationTypeId = 2 AND PrimaryUserId != $userId  THEN RelationId END ) AS Followers
					 FROM $relationsTableName WHERE (PrimaryUserId = $userId OR SecondaryUserId = $userId) AND StatusId = %d";

		foreach(self::executePreparedQuery( self::getDbObject()->prepare($sqlQuery, UserRelationEntity::RELATION_STATUS_ACTIVE) ) as $stdUserCounters)
		{
			$arrUserRelations[$userId][UserRelationEntity::RELATION_TYPE_FOLLOWED]   = isset($stdUserCounters->Followers) ? (int)$stdUserCounters->Followers : 0;
			$arrUserRelations[$userId][UserRelationEntity::RELATION_TYPE_FOLLOWING]  = isset($stdUserCounters->Following) ? (int)$stdUserCounters->Following : 0;
			$arrUserRelations[$userId][UserRelationEntity::RELATION_TYPE_FRIENDSHIP] = isset($stdUserCounters->Friends)   ? (int)$stdUserCounters->Friends   : 0;
		}

		return isset($arrUserRelations[$userId][$relationTypeId]) ? $arrUserRelations[$userId][$relationTypeId] : $arrUserRelations[$userId][$relationTypeId] = 0;

	}

	public static function getRelationByPK($relationId)
	{
		if(empty($relationId))
			return null;

		$sqlQuery = "SELECT * FROM " . self::getUserRelationsTableName() . " WHERE RelationId = %d";
		foreach(self::executePreparedQuery( self::getDbObject()->prepare($sqlQuery, $relationId) ) as $userRelation)
		{
			$userRelationEntity = new UserRelationEntity($userRelation->RelationId, $userRelation->PrimaryUserId, $userRelation->SecondaryUserId, $userRelation->RelationTypeId, $userRelation->StatusId);
			$userRelationEntity->CreatedDate = $userRelation->CreatedDate;
			return $userRelationEntity;
		}

		return null;
	}

	public static function deleteRelationByPK($relationId)
	{
		if(!MchValidator::isInteger($relationId))
			return;

		$sqlQuery = "DELETE FROM " . self::getUserRelationsTableName() . " WHERE RelationId = %d";

		self::executePreparedQuery( self::getDbObject()->prepare( $sqlQuery, array($relationId)) );


	}

	public static function deleteRelationByUserId($userId)
	{
		if(!MchValidator::isInteger($userId))
			return;

		$sqlQuery = "DELETE FROM " . self::getUserRelationsTableName() . " WHERE (PrimaryUserId = %d OR SecondaryUserId = %d)";

		self::executePreparedQuery( self::getDbObject()->prepare( $sqlQuery, array($userId, $userId)) );

	}

	public static function createUserRelationsTable()
	{
		if ( parent::tableExists( parent::getUserRelationsTableName() ) ) {
			return false;
		}


		global $wpdb;

		function_exists('dbDelta') || require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$createTableStatement = "CREATE TABLE " . parent::getUserRelationsTableName() . " (
                                                                  RelationId int unsigned NOT NULL auto_increment,
                                                                  PrimaryUserId  int unsigned NOT NULL,
                                                                  SecondaryUserId  int unsigned NOT NULL,
                                                                  CreatedDate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                                                  RelationTypeId tinyint unsigned NOT NULL,
                                                                  StatusId tinyint unsigned NULL,
                                                                  PRIMARY KEY  (RelationId),
                                                                  INDEX idx_uc_puser_rel (PrimaryUserId, RelationTypeId, StatusId,  CreatedDate DESC),
                                                                  INDEX idx_uc_suser_rel (SecondaryUserId, RelationTypeId, StatusId, CreatedDate DESC)
                                                                )";

		$createTableStatement .= ! empty( $wpdb->charset ) ? " DEFAULT CHARACTER SET {$wpdb->charset}" : '';
		$createTableStatement .= ! empty( $wpdb->collate ) ? " COLLATE {$wpdb->collate}" : '';

		$result = dbDelta( $createTableStatement );

		return ! empty( $result ) ? true : false;
	}


}