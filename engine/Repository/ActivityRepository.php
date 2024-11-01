<?php

namespace UltraCommunity\Repository;


use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Entities\ActivityEntity;
use UltraCommunity\Entities\ActivityMetaData;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\Repository\WpPostRepository;

final class ActivityRepository extends BaseRepository
{
	public static function getActivityByKey($activityKey)
	{
		$activityId = parent::getActivityIdFromKey($activityKey);
		if(empty($activityId))
			return null;

		$sqlQuery = "SELECT * FROM " . self::getActivityTableName() . " WHERE ActivityId = %d";
		$activityEntity = null;
		foreach ( self::executePreparedQuery( self::getDbObject()->prepare( $sqlQuery, $activityId) )  as $entityInfo )
		{
			$activityEntity = self::getEntityFromResultRow($entityInfo); break;
		}

		return $activityEntity;
	}

	public static function getActivitiesByPrimaryKeys(array $arrPrimaryKeys)
	{
		$arrPrimaryKeys = array_filter($arrPrimaryKeys, function($primaryKey){ return MchValidator::isPositiveInteger($primaryKey);});

		if(empty($arrPrimaryKeys))
			return array();

		$sqlQuery = "SELECT * FROM " . self::getActivityTableName() . " WHERE ActivityId IN(" . implode(',', $arrPrimaryKeys) . ') AND 1 = %d';

		$arrEntities = array();

		foreach ( self::executePreparedQuery( self::getDbObject()->prepare( $sqlQuery, 1) )  as $entityInfo )
		{
			$activityEntity = self::getEntityFromResultRow($entityInfo);
			empty($activityEntity->ActivityId) ?: $arrEntities[$activityEntity->ActivityId] = $activityEntity;

		}

		return $arrEntities;

	}


	public static function getUserProfileActivityFeed($userKey, $pageNumber = 1, $recordsPerPage = 10)
	{
		if(! ($userId = parent::getUserIdFromKey($userKey) ) )
			return array();

		( ( $pageNumber = (int) $pageNumber ) > 0 ) ?: $pageNumber = 1;

		$activityTable = parent::getActivityTableName();
		$repostsTable  = parent::getActivityRepostsTableName();
		$relationsTable = parent::getUserRelationsTableName();
		$userGroupsTable = parent::getGroupUsersTableName();

		$selectStatement  = "SELECT activity.*, RepostedDate FROM $activityTable activity ";
		$selectStatement .= "LEFT JOIN $repostsTable reposts ON (activity.ActivityId = reposts.ActivityId) ";
		$selectStatement .= "WHERE 1 ";
		$selectStatement .= "AND (activity.UserId = %d OR reposts.UserId = %d) ";
		$selectStatement .= "OR ( activity.TargetTypeId = 1 AND EXISTS ( SELECT 1 FROM $relationsTable WHERE PrimaryUserId = %d and SecondaryUserId = activity.UserId AND RelationTypeId IN (1, 2) AND StatusId = 1) ) ";
		$selectStatement .= "OR ( activity.TargetTypeId = 2 AND EXISTS ( SELECT 1 FROM $userGroupsTable groups WHERE GroupId = activity.TargetId AND groups.UserId = %d) ) ";
		$selectStatement .= "ORDER BY IFNULL(reposts.RepostedDate, activity.CreatedDate) DESC LIMIT %d, %d";

		$arrParams = array($userId, $userId, $userId, $userId);
		$arrParams[] = ( $pageNumber - 1 ) * $recordsPerPage;
		$arrParams[] = $recordsPerPage;

		$arrEntities = array();


		foreach (self::executePreparedQuery(self::getDbObject()->prepare($selectStatement, $arrParams)) as $resultEntity)
		{
			$activityEntity = self::getEntityFromResultRow($resultEntity);

			empty($activityEntity->ActivityId) ?: $arrEntities[$activityEntity->ActivityId] = $activityEntity;

		}

		//echo self::getDbObject()->last_query;exit;

		return $arrEntities;

	}


	public static function getUserRePostedActivityIds($userKey)
	{
		if(! ($userId = parent::getUserIdFromKey($userKey) ) )
			return array();

		$repostsTable  = parent::getActivityRepostsTableName();

		$selectStatement = "SELECT DISTINCT ActivityId FROM $repostsTable WHERE UserId = %d";

		$arrIds = array();

		foreach (self::executePreparedQuery(self::getDbObject()->prepare($selectStatement, $userId)) as $rowResult) {

			$activityId = (int)$rowResult->ActivityId;
			$arrIds[$activityId] = $activityId;
		}

		return $arrIds;

	}

	public static function countActivityReposts($activityKey)
	{
		if(! ($activityId = parent::getActivityIdFromKey($activityKey) ) )
			return 0;

		static $arrCachedCounters = null;
		if(null === $arrCachedCounters)
		{
			$arrCachedCounters = array();
			$repostsTable    = parent::getActivityRepostsTableName();
			$selectStatement = "SELECT ActivityId, Count(ActivityId) AS Counter FROM $repostsTable WHERE %d GROUP BY ActivityId";
			foreach (self::executePreparedQuery(self::getDbObject()->prepare($selectStatement, 1)) as $rowResult) {
				$arrCachedCounters[(int)$rowResult->ActivityId] = (int)$rowResult->Counter;
			}
		}

		return isset($arrCachedCounters[$activityId]) ? $arrCachedCounters[$activityId] : 0;
	}

	/**
	 * @param ActivityEntity $activityEntity
	 * @param int            $pageNumber
	 * @param int            $recordsPerPage
	 * @param array          $arrAdditionalParams
	 *
	 * @return ActivityEntity[]
	 */
	public static function findByEntityProperties(ActivityEntity $activityEntity, $pageNumber = 1, $recordsPerPage = 10, $arrAdditionalParams = array())
	{
		$arrProperties = empty($arrAdditionalParams) ? get_object_vars($activityEntity) : array_merge( get_object_vars($activityEntity), $arrAdditionalParams );
		$arrProperties = array_filter($arrProperties, 'is_scalar');

		( ( $pageNumber = (int) $pageNumber ) > 0 ) ?: $pageNumber = 1;

		$selectStatement  = 'SELECT * FROM ' . self::getActivityTableName() . ' WHERE ';
		$selectStatement .= implode('=%s AND ', array_keys($arrProperties)) . '= %s ';

		$selectStatement .= 'ORDER BY CreatedDate DESC LIMIT %d, %d';

		$arrParams = array_values($arrProperties);
		$arrParams[] = ( $pageNumber - 1 ) * $recordsPerPage;
		$arrParams[] = $recordsPerPage;


		$arrEntities = array();
		foreach (self::executePreparedQuery(self::getDbObject()->prepare($selectStatement, $arrParams)) as $resultEntity)
		{
			$activityEntity = self::getEntityFromResultRow($resultEntity);
			empty($activityEntity->ActivityId) ?: $arrEntities[$activityEntity->ActivityId] = $activityEntity;

		}

		return $arrEntities;

	}

	public static function saveActivity(ActivityEntity $activityEntity)
	{
		if(empty($activityEntity->PostTypeId)) {
			$activityEntity->PostTypeId = PostTypeController::publishPostType(PostTypeController::getPostTypeInstance(PostTypeController::POST_TYPE_ACTIVITY));
		}

		empty($activityEntity->MetaData->Attachments) ?: $activityEntity->MetaData->Attachments = array_values($activityEntity->MetaData->Attachments);
		if(empty($activityEntity->MetaData->PostFormat) || $activityEntity->MetaData->PostFormat == ActivityEntity::ACTIVITY_POST_FORMAT_STATUS){
			$activityEntity->MetaData->PostFormat = null;
		}

		if(MchUtils::isNullOrEmpty(get_object_vars(MchUtils::filterObjectEmptyProperties($activityEntity->MetaData)))){
			$activityEntity->MetaData = null;
		}

		$activityEntity->MetaData = maybe_serialize($activityEntity->MetaData);

		!empty($activityEntity->CreatedDate) ?: $activityEntity->CreatedDate = current_time( 'mysql' );
		!empty($activityEntity->StatusId)    ?: $activityEntity->StatusId    = ActivityEntity::ACTIVITY_STATUS_ACTIVE;


		if(empty($activityEntity->ActivityId))
		{
			return (false === self::getDbObject()->insert(self::getActivityTableName(), array_filter((array)$activityEntity))) ? 0 : self::getDbObject()->insert_id;
		}

		$sqlQuery  = ' UPDATE ' . self::getActivityTableName()  . ' SET';
		$sqlQuery .= ' TargetTypeId      = ' . self::getDbObject()->prepare( '%d', $activityEntity->TargetTypeId );
		$sqlQuery .= ' ,ActionTypeId     = ' . self::getDbObject()->prepare( '%d', $activityEntity->ActionTypeId );
		$sqlQuery .= ' ,UserId           = ' . self::getDbObject()->prepare( '%d', $activityEntity->UserId );
		$sqlQuery .= ' ,StatusId         = ' . self::getDbObject()->prepare( '%d', $activityEntity->StatusId );
		$sqlQuery .= ' ,PostFormatTypeId = ' . self::getDbObject()->prepare( '%d', $activityEntity->PostFormatTypeId );

		$sqlQuery .= ' ,CreatedDate  = ' . self::getDbObject()->prepare( '%s', $activityEntity->CreatedDate );

		$sqlQuery .= ' ,TargetId   = '  . (  MchValidator::isInteger($activityEntity->TargetId) ? self::getDbObject()->prepare( '%d', $activityEntity->TargetId ) : 'null' );
		$sqlQuery .= ' ,PostTypeId = '  . (  MchValidator::isInteger($activityEntity->PostTypeId) ? self::getDbObject()->prepare( '%d', $activityEntity->PostTypeId ) : 'null' );
		$sqlQuery .= ' ,MetaData   = '  . ( !empty($activityEntity->MetaData ) ? self::getDbObject()->prepare( '%s', $activityEntity->MetaData ) : 'null' );

		$sqlQuery .= ' WHERE ActivityId = %d';

		return self::executePreparedQuery( self::getDbObject()->prepare( $sqlQuery, array($activityEntity->ActivityId)) );

	}

	public static function countActivityByEntityProperties(ActivityEntity $activityEntity)
	{
		$arrProperties = \array_filter(\get_object_vars($activityEntity), 'is_scalar');

		$selectStatement  = 'SELECT COUNT(*) FROM ' . self::getActivityTableName() . ' WHERE 1 AND ';
		$selectStatement .= implode('=%s AND ', \array_keys($arrProperties)) . '=%s ';

		$arrParams = array_values($arrProperties);

		return (int)self::getDbObject()->get_var(self::getDbObject()->prepare($selectStatement, $arrParams));

	}

	public static function deleteActivity($activityKey)
	{
		if(null === ($activityEntity = self::getActivityByKey($activityKey)))
			return;

		$sqlQuery = "DELETE FROM " . self::getActivityTableName() . " WHERE ActivityId = %d";

		self::executePreparedQuery( self::getDbObject()->prepare( $sqlQuery, array($activityEntity->ActivityId)) );

		if(null === ($wpPost = WpPostRepository::findByPostId($activityEntity->PostTypeId)) || empty($wpPost->post_type))
			return;

		if($wpPost->post_type !== PostTypeController::POST_TYPE_ACTIVITY)
			return;

		WpPostRepository::delete($wpPost->ID);

	}

	/**
	 * @param $rowEntityInfo
	 *
	 * @return ActivityEntity | null
	 */
	private static function getEntityFromResultRow($rowEntityInfo /** @var $rowEntityInfo ActivityEntity */)
	{
		if(empty($rowEntityInfo->ActivityId))
			return null;

		$activityEntity = new ActivityEntity();

		$activityEntity->ActivityId   = (int)$rowEntityInfo->ActivityId;
		$activityEntity->PostTypeId   = (int)$rowEntityInfo->PostTypeId;
		$activityEntity->TargetId     = (int)$rowEntityInfo->TargetId;
		$activityEntity->UserId       = (int)$rowEntityInfo->UserId;
		$activityEntity->StatusId     = (int)$rowEntityInfo->StatusId;
		$activityEntity->ActionTypeId = (int)$rowEntityInfo->ActionTypeId;
		$activityEntity->TargetTypeId = (int)$rowEntityInfo->TargetTypeId;

		$activityEntity->PostFormatTypeId = (int)$rowEntityInfo->PostFormatTypeId;

		$activityEntity->CreatedDate  = $rowEntityInfo->CreatedDate;

		$activityEntity->MetaData = empty($rowEntityInfo->MetaData) ? new ActivityMetaData() : \maybe_unserialize($rowEntityInfo->MetaData);

		return $activityEntity;
	}


	public static function saveActivityRepost($activityId, $userId)
	{
		$arrRepostData = array(
			'UserId'       => $userId,
			'ActivityId'   => $activityId,
			'RepostedDate' => MchWpUtils::getSiteCurrentDateTime(),

		);

		return (false === self::getDbObject()->insert(self::getActivityRepostsTableName(), $arrRepostData)) ? 0 : self::getDbObject()->insert_id;

	}

	public static function createActivityTable()
	{
		if ( parent::tableExists( self::getActivityTableName() ) ) {
			return false;
		}

		global $wpdb;

		function_exists('dbDelta') || require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$createTableStatement = "CREATE TABLE " . self::getActivityTableName() . " (
                                                                  ActivityId int unsigned NOT NULL auto_increment,
                                                                  TargetTypeId tinyint unsigned NOT NULL,
                                                                  ActionTypeId tinyint unsigned NOT NULL,
                                                                  UserId  int unsigned NOT NULL,
                                                                  TargetId int unsigned NULL,
                                                                  PostTypeId int unsigned NULL,
                                                                  CreatedDate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                                                  PostFormatTypeId tinyint NOT NULL DEFAULT 0,
                                                                  StatusId tinyint unsigned NULL,
                                                                  MetaData mediumtext NULL DEFAULT NULL,
                                                                  PRIMARY KEY  (ActivityId),
                                                                  INDEX idx_uc_user (UserId, CreatedDate DESC),
                                                                  INDEX idx_uc_target (TargetTypeId, TargetId, CreatedDate DESC)
                                                                )";

		$createTableStatement .= ! empty( $wpdb->charset ) ? " DEFAULT CHARACTER SET {$wpdb->charset}" : '';
		$createTableStatement .= ! empty( $wpdb->collate ) ? " COLLATE {$wpdb->collate}" : '';

		$result = dbDelta( $createTableStatement );

		return ! empty( $result ) ? true : false;
	}

	public static function createActivityRepostsTable()
	{
		if ( parent::tableExists( self::getActivityRepostsTableName() ) ) {
			return false;
		}

		global $wpdb;

		function_exists('dbDelta') || require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$createTableStatement = "CREATE TABLE " . self::getActivityRepostsTableName() . " (
                                                                  ActivityRepostId int unsigned NOT NULL auto_increment,
                                                                  ActivityId int unsigned NOT NULL,
                                                                  UserId  int unsigned NOT NULL,
                                                                  RepostedDate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                                                  PRIMARY KEY  (ActivityRepostId),
                                                                  INDEX idx_act_repost_user (UserId),
                                                                  INDEX idx_act_repost_date (RepostedDate),
                                                                  INDEX idx_act_repost_activity (ActivityId)
                                                                )";

		$createTableStatement .= ! empty( $wpdb->charset ) ? " DEFAULT CHARACTER SET {$wpdb->charset}" : '';
		$createTableStatement .= ! empty( $wpdb->collate ) ? " COLLATE {$wpdb->collate}" : '';

		$result = dbDelta( $createTableStatement );

		return ! empty( $result ) ? true : false;
	}



}