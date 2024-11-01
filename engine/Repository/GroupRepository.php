<?php


namespace UltraCommunity\Repository;

use UltraCommunity\Entities\GroupEntity;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Entities\GroupUserEntity;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\Repository\WpPostRepository;

final class GroupRepository extends BaseRepository {
	private static $groupMetaKey = 'ultracomm-group-info';

	public static function saveGroupUser(GroupUserEntity $groupUserEntity)
	{
		!empty($groupUserEntity->JoinedDate) ?: $groupUserEntity->JoinedDate = current_time( 'mysql' );

		$sqlQuery = '';
		if(!empty($groupUserEntity->GroupUserId))
		{
			$sqlQuery .= 'UPDATE ' . self::getGroupUsersTableName() .  ' SET GroupId = %d , UserId = %d, JoinedDate = %s, UserStatusId = %d, UserTypeId = %d WHERE GroupUserId = %d';

			$arrParams = array(
				$groupUserEntity->GroupId, $groupUserEntity->UserId, $groupUserEntity->JoinedDate, $groupUserEntity->UserStatusId, $groupUserEntity->UserTypeId, $groupUserEntity->GroupUserId
			);

			return self::executePreparedQuery( self::getDbObject()->prepare( $sqlQuery, $arrParams) );
		}


		return (false === self::getDbObject()->insert(self::getGroupUsersTableName(), array_filter((array)$groupUserEntity))) ? 0 : self::getDbObject()->insert_id;

	}

	public static function deleteGroupUsersByGroupId( $groupId ) {
		$sqlQuery = "DELETE FROM "  . self::getGroupUsersTableName() . " WHERE GroupId = %d";

		return self::executePreparedQuery( self::getDbObject()->prepare( $sqlQuery, array( absint( $groupId ) ) ) );
	}


	public static function deleteGroupUser($groupId, $userId) {
		$sqlQuery = "DELETE FROM "  . self::getGroupUsersTableName() . " WHERE GroupId = %d AND UserId = %d" ;

		return self::executePreparedQuery( self::getDbObject()->prepare( $sqlQuery, array( absint( $groupId ), absint($userId) ) ) );

	}


	/**
	 * @param $groupId
	 * @param int $pageNumber
	 * @param int $recordsPerPage
	 * @param null $statusId
	 *
	 * @return GroupUserEntity[]
	 */
	public static function getGroupUsers($groupId, $pageNumber = 1, $recordsPerPage = 10, $statusId = null, $orderType = 'ASC')
	{
		( ( $pageNumber = (int) $pageNumber ) > 0 ) ?: $pageNumber = 1;

		$sqlQuery = "SELECT * FROM " . self::getGroupUsersTableName() . " WHERE GroupId = %d ";

		$sqlQuery .= !empty( $statusId ) ? ' AND UserStatusId = %d' : '';

		$sqlQuery .= " ORDER BY GroupUserId $orderType ";
		$sqlQuery .= ' LIMIT %d, %d';

		$arrParams = array( (int) $groupId );

		empty( $statusId ) ?: $arrParams[] = (int) $statusId;

		$arrParams[] = ( $pageNumber - 1 ) * $recordsPerPage;
		$arrParams[] = $recordsPerPage;

		$arrGroupUserEntities = self::executePreparedQuery( self::getDbObject()->prepare( $sqlQuery, $arrParams ) );
		foreach ( $arrGroupUserEntities as &$entityInfo ) {
			$groupUserEntity               = new GroupUserEntity( $entityInfo->GroupUserId, $entityInfo->GroupId, $entityInfo->UserId );
			$groupUserEntity->JoinedDate   = $entityInfo->JoinedDate;
			$groupUserEntity->UserStatusId = (int) $entityInfo->UserStatusId;
			$groupUserEntity->UserTypeId   = (int) $entityInfo->UserTypeId;
			$entityInfo = $groupUserEntity;
		}

		return $arrGroupUserEntities;
	}


	public static function getGroupUserStatusId($groupKey, $userKey)
	{
		$groupUserEntity = self::getGroupUserEntity($groupKey, $userKey);
		return isset($groupUserEntity) ? (int)$groupUserEntity->UserStatusId : 0;
	}

	public static function getGroupUserTypeId($groupKey, $userKey)
	{
		$groupUserEntity = self::getGroupUserEntity($groupKey, $userKey);
		return isset($groupUserEntity) ? (int)$groupUserEntity->UserTypeId : 0;
	}

	/**
	 * @param $groupKey
	 * @param $userKey
	 *
	 * @return GroupUserEntity | null
	 */
	public static function getGroupUserEntity($groupKey, $userKey)
	{
		$arrGroupUsersEntities = self::getGroupUsersEntities($groupKey, array($userKey));
		$groupUserEntity       = reset($arrGroupUsersEntities);
		
		return empty($groupUserEntity) ? null : $groupUserEntity;


		if(empty($userKey) || empty($groupKey)){
			return null;
		}

		$userId  =  parent::getUserIdFromKey($userKey);
		$groupId =  parent::getGroupIdFromKey($groupKey);
		if(empty($userId) || empty($groupId)){
			return null;
		}

		if(isset($arrCache[$groupId][$userId]))
			return $arrCache[$groupId][$userId];

		$sqlQuery = "SELECT * FROM " . self::getGroupUsersTableName() . " WHERE GroupId = %d AND UserId = %d";

		foreach ( self::executePreparedQuery( self::getDbObject()->prepare( $sqlQuery, array($groupId, $userId) ) ) as $entityInfo ) {
			$groupUserEntity                 = new GroupUserEntity( $entityInfo->GroupUserId, $entityInfo->GroupId, $entityInfo->UserId );
			$groupUserEntity->JoinedDate     = $entityInfo->JoinedDate;
			$groupUserEntity->UserStatusId   = (int) $entityInfo->UserStatusId;
			$groupUserEntity->UserTypeId     = (int) $entityInfo->UserTypeId;

			$arrCache[$groupId][$userId] = $groupUserEntity;
		}

		isset($arrCache[$groupId][$userId]) ?: $arrCache[$groupId][$userId] = new GroupUserEntity();

		return $arrCache[$groupId][$userId];

	}

	public static function getGroupUsersEntities($groupKey, array $arrUserKeys )
	{

		if(null === ($groupId = parent::getGroupIdFromKey($groupKey)))
			return array();

		foreach ($arrUserKeys as $index => &$userKey) {
			$userKey = parent::getUserIdFromKey($userKey);
		}

		unset($userKey);

		$arrUserKeys = \array_filter($arrUserKeys);
		if(empty($arrUserKeys))
			return array();

		$arrMemberEntities = array();

		static $arrMemberEntitiesCache = array();
		isset($arrMemberEntitiesCache[$groupId]) ?: $arrMemberEntitiesCache[$groupId] = array();

		foreach ($arrUserKeys as $index => $userKey)
		{
			if(!isset($arrMemberEntitiesCache[$groupId][$userKey]))
				continue;

			(0 === $arrMemberEntitiesCache[$groupId][$userKey]) ?: $arrMemberEntities[$userKey] = $arrMemberEntitiesCache[$groupId][$userKey];

			//$arrMemberEntities[$userKey] = (0 === $arrMemberEntitiesCache[$groupId][$userKey]) ? null : $arrMemberEntitiesCache[$groupId][$userKey];
			unset($arrUserKeys[$index]);
		}

		if(empty($arrUserKeys))
			return $arrMemberEntities;

		$sqlQuery  = "SELECT * FROM " . self::getGroupUsersTableName() . " WHERE GroupId = %d AND UserId IN (";
		$sqlQuery .= implode(',', array_map(function ($userKey){return self::getDbObject()->prepare('%d', $userKey);}, $arrUserKeys)) . ')';

		$arrUserKeys = array_flip($arrUserKeys);
		foreach ( self::executePreparedQuery( self::getDbObject()->prepare( $sqlQuery, array($groupId) ) ) as $entityInfo ) {
			$groupUserEntity                 = new GroupUserEntity( $entityInfo->GroupUserId, $entityInfo->GroupId, $entityInfo->UserId );
			$groupUserEntity->JoinedDate     = $entityInfo->JoinedDate;
			$groupUserEntity->UserStatusId   = (int) $entityInfo->UserStatusId;
			$groupUserEntity->UserTypeId     = (int) $entityInfo->UserTypeId;

			$arrMemberEntitiesCache[$groupId][$groupUserEntity->UserId] = $groupUserEntity;
			$arrMemberEntities[$groupUserEntity->UserId] = $groupUserEntity;
			unset($arrUserKeys[$groupUserEntity->UserId]);
		}

		foreach ($arrUserKeys as $userKey => $someValue){
			$arrMemberEntitiesCache[$groupId][$userKey] = 0;
		}

		return $arrMemberEntities;

	}

	/**
	 * @param       $userKey
	 * @param null  $userTypeId
	 * @param array $arrUserStatusId
	 * @param int   $pageNumber
	 * @param int   $recordsPerPage
	 *
	 * @return GroupUserEntity[]
	 */
	public static function getGroupUserEntitiesByUser($userKey, $userTypeId = null, $pageNumber = 1, $recordsPerPage = 12, array $arrAdditionalArguments = array(), $justCount = false)
	{
		if(! ( $userId = (int)self::getUserIdFromKey($userKey)  )){
			return array();
		}
		
		if(empty($arrAdditionalArguments['arrGroupType']))
			unset($arrAdditionalArguments['arrGroupType']);
		
		if(empty($arrAdditionalArguments['arrUserStatus']))
			unset($arrAdditionalArguments['arrUserStatus']);
		
		
		static $arrRecordsCounter = array();
		
		$recordsCounterKey = md5(json_encode(array($userTypeId, $arrAdditionalArguments)));
		
//		isset($arrRecordsCounter[$userId][$recordsCounterKey]) ?: $arrRecordsCounter[$userId][$recordsCounterKey] = null;
		
		if($justCount && isset($arrRecordsCounter[$userId][$recordsCounterKey]))
			return $arrRecordsCounter[$userId][$recordsCounterKey];
		
		$arrQueryParams     = array();
		$groupUserTableName = self::getGroupUsersTableName();
		$postMetaTableName  = self::getTablePrefix()  . 'postmeta';

		$arrUserStatusId = isset($arrAdditionalArguments['arrUserStatus']) ? array_map('absint', (array)$arrAdditionalArguments['arrUserStatus']) : array();
		$arrGroupTypeId  = isset($arrAdditionalArguments['arrGroupType'])  ? array_map('absint', (array)$arrAdditionalArguments['arrGroupType'])  : array();

		$sqlQuery = "SELECT SQL_CALC_FOUND_ROWS $groupUserTableName.* FROM $groupUserTableName";

		$whereClause = " WHERE UserId = %d ";
		$arrQueryParams[] = $userId;

		if(MchValidator::isInteger($userTypeId)){
			$whereClause   .= ' AND UserTypeId = %d';
			$arrQueryParams[] = (int)$userTypeId;
		}
//		elseif (is_array($userTypeId))
//		{
//			foreach ($userTypeId as &$uType){$uType = self::getDbObject()->prepare('%d', $uType);}
//
//			$whereClause   .= ' AND UserTypeId IN (' . implode(',', $userTypeId) . ') ';
//		}
		
		if(!empty($arrGroupTypeId))
		{
			$sqlQuery    .= " INNER JOIN $postMetaTableName ON ( $groupUserTableName.GroupId = $postMetaTableName.post_id )";
			$whereClause .= " AND $postMetaTableName.meta_key = '" . self::$groupMetaKey  ."' AND $postMetaTableName.meta_value REGEXP '". '.*"Type";i:('.implode('|', $arrGroupTypeId).').*' ."' ";
		}

		if(!empty($arrUserStatusId)){

			$whereClause   .= ' AND UserStatusId IN(';
			$arrPlaceholders = array_fill(0, count($arrUserStatusId), '%d');
			$whereClause   .= implode(',', $arrPlaceholders) . ')';
			$arrQueryParams = array_merge($arrQueryParams, $arrUserStatusId);
		}

		$sqlQuery .= $whereClause . ' ORDER BY GroupUserId DESC LIMIT %d, %d';

		($pageNumber     = absint($pageNumber))     > 0 ?:  $pageNumber = 1;
		($recordsPerPage = absint($recordsPerPage)) > 0 ?:  $recordsPerPage = 12;

		$arrQueryParams[] = ( $pageNumber - 1) * $recordsPerPage;
		$arrQueryParams[] = $recordsPerPage;

		$arrGroupUserEntities = array();

		foreach ( self::executePreparedQuery( self::getDbObject()->prepare( $sqlQuery, $arrQueryParams) )  as $entityInfo ) {
			$groupUserEntity                 = new GroupUserEntity( $entityInfo->GroupUserId, $entityInfo->GroupId, $entityInfo->UserId );
			$groupUserEntity->JoinedDate     = $entityInfo->JoinedDate;
			$groupUserEntity->UserStatusId   = (int) $entityInfo->UserStatusId;
			$groupUserEntity->UserTypeId     = (int) $entityInfo->UserTypeId;
			
			$arrGroupUserEntities[] = $groupUserEntity;
		}
		
		$arrRecordsCounter[$userId][$recordsCounterKey] = self::getLastQueryTotalFoundRows();
		
		return $justCount ? $arrRecordsCounter[$userId][$recordsCounterKey] :  $arrGroupUserEntities;
	}

	public static function getGroupNumberOfUsers( $groupId, $statusId = null ) {

		$arrGroupUsers = self::getGroupsNumberOfUsers( array( (int) $groupId ), $statusId );

		return isset( $arrGroupUsers[ $groupId ] ) ? (int)$arrGroupUsers[ $groupId ] : 0;
	}


	public static function getGroupsNumberOfUsers( $arrGroupIds, $statusId = null ) {
		static $arrCachedData = array();

		$statusId = (int)$statusId;
		$arrGroupUsers = array();

		$sqlInClause = null;
		foreach ( $arrGroupIds as $index => $groupId )
		{
			if ( isset( $arrCachedData[ $groupId ][ $statusId ] ) )
			{
				$arrGroupUsers[ $groupId ] = $arrCachedData[ $groupId ][ $statusId ];
				unset( $arrGroupIds[ $index ] );
				continue;
			}

			$arrCachedData[ $groupId ][ $statusId ] = 0;
			$sqlInClause .= self::getDbObject()->prepare( '%d', (int) $groupId ) . ',';
		}

		if ( empty( $sqlInClause ) ) {
			return $arrGroupUsers;
		}

		$sqlInClause = rtrim( $sqlInClause, ',' );
		$sqlInClause = filter_var( $sqlInClause, FILTER_VALIDATE_INT ) ? '= ' . $sqlInClause : 'IN (' . $sqlInClause . ')';

		$sqlQuery = "SELECT GroupId, COUNT(*) AS Users FROM " . self::getGroupUsersTableName() . " WHERE GroupId $sqlInClause ";

		$arrParams = array();
		if(!empty($statusId))
		{
			$sqlQuery .= ' AND UserStatusId = %d ';
			$arrParams[] = $statusId;
		}

		$sqlQuery .= ' AND 1 = %d '; //we need at least one placeholder
		$arrParams[] = 1;

		$sqlQuery .= ' GROUP BY GroupId';

		//echo $sqlQuery;exit;

		foreach ( self::executePreparedQuery( self::getDbObject()->prepare( $sqlQuery, $arrParams ) ) as $groupInfo ) {
			$arrGroupUsers[ $groupInfo->GroupId ]              = $groupInfo->Users;
			$arrCachedData[ $groupInfo->GroupId ][ $statusId ] = $groupInfo->Users;
		}

		return $arrGroupUsers;

	}
	
	public static function createGroupUsersTable() {
		if ( parent::tableExists( self::getGroupUsersTableName() ) ) {
			return false;
		}

		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$createTableStatement = "CREATE TABLE " . self::getGroupUsersTableName() . " (
                                                                  GroupUserId int unsigned NOT NULL auto_increment,
                                                                  GroupId int unsigned NOT NULL,
                                                                  UserId  int unsigned NOT NULL,
                                                                  JoinedDate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                                                  UserTypeId tinyint unsigned NOT NULL,
                                                                  UserStatusId tinyint unsigned NOT NULL,
                                                                  PRIMARY KEY  (GroupUserId),
                                                                  UNIQUE INDEX idx_uc_group_user (GroupId, UserId)
                                                                )";

		$createTableStatement .= ! empty( $wpdb->charset ) ? " DEFAULT CHARACTER SET {$wpdb->charset}" : '';
		$createTableStatement .= ! empty( $wpdb->collate ) ? " COLLATE {$wpdb->collate}" : '';

		$result = dbDelta( $createTableStatement );

		return ! empty( $result ) ? true : false;
	}

	/**
	 *
	 * @return array | \UltraCommunity\Entities\GroupEntity[]
	 */
	public static function getGroupEntities( $pageNumber = 1, $postsPerPage = 12, $arrAdditionalArguments = array() )
	{

		($pageNumber = (int)$pageNumber) ?: $pageNumber = 1;

		$arrAdditionalArguments['meta_query']     = array();
		$arrAdditionalArguments['paged']          = (int) $pageNumber;
		$arrAdditionalArguments['posts_per_page'] = (int) $postsPerPage;
		$arrAdditionalArguments['post_type']      = PostTypeController::POST_TYPE_GROUP;


		$arrIncludeGroups = isset($arrAdditionalArguments['arrIncludeGroups']) ? array_map('absint', (array)$arrAdditionalArguments['arrIncludeGroups']) : array();
		$arrExcludeGroups = isset($arrAdditionalArguments['arrExcludeGroups']) ? array_map('absint', (array)$arrAdditionalArguments['arrExcludeGroups']) : array();
		$arrGroupType     = isset($arrAdditionalArguments['arrGroupType'])     ? array_map('absint', (array)$arrAdditionalArguments['arrGroupType'])     : array();

		if(!empty($arrExcludeGroups))
		{
			$arrAdditionalArguments['post__not_in'] = $arrExcludeGroups;
			unset($arrAdditionalArguments['arrExcludeGroups']);
		}

		if(!empty($arrIncludeGroups))
		{
			$arrAdditionalArguments['meta_query'][] = array(
							'value' => 'UC-FORCED-POST-IDS',
							'compare' => 'IN'
					);
			$arrAdditionalArguments['meta_query']['relation'] = 'OR';

			$arrAdditionalArguments['ULTRACOMM-QUERY_KEY'] = 'UC-FORCED-POST-IDS';

			MchWpUtils::addFilterHook('posts_clauses', function ($arrQueryInfo, $wpQueryObject) use($arrIncludeGroups){

				if(empty($arrQueryInfo['where']) || empty($wpQueryObject->query['ULTRACOMM-QUERY_KEY']) || $wpQueryObject->query['ULTRACOMM-QUERY_KEY'] != 'UC-FORCED-POST-IDS')
					return $arrQueryInfo;

				if(false === stripos($arrQueryInfo['where'], "wp_postmeta.meta_value IN ('UC-FORCED-POST-IDS')"))
					return $arrQueryInfo;

				$arrQueryInfo['where'] = str_ireplace(" wp_postmeta.meta_value IN ('UC-FORCED-POST-IDS')", self::getTablePrefix() . 'postmeta.post_id IN (' . implode(',', $arrIncludeGroups) . ') ', $arrQueryInfo['where']);

				return $arrQueryInfo;

			}, PHP_INT_MAX, 2);

		}

		if(!empty($arrGroupType))
		{

			$arrAdditionalArguments['meta_query'][] = array(
					'key'     => self::$groupMetaKey,
					'value'   =>'.*"Type";i:('.implode('|', $arrGroupType).').*' ,
					'compare' => 'REGEXP'
			);

			unset($arrAdditionalArguments['arrGroupsType']);
		}

//		if(empty($arrAdditionalArguments['meta_query']))
//		{
//			unset($arrAdditionalArguments['meta_query']);
//		}

		$wpPostsQuery = WpPostRepository::getPostsPreparedQuery( $pageNumber, $postsPerPage, $arrAdditionalArguments );

		$arrGroups = array();
		while ( $wpPostsQuery->have_posts() )
		{
			$wpPostsQuery->the_post();
			$groupEntity = GroupEntity::fromWPPost( get_post() );
			$groupMeta   = WpPostRepository::getPostMeta( $groupEntity->Id, self::$groupMetaKey, true );

			if ( empty( $groupMeta->Type ) ) {
				continue;
			}

			$groupEntity->GroupTypeId                   = (int)$groupMeta->Type;
			//$groupEntity->GroupPrivacyActivityView    = empty($groupMeta->PrivacyActivityView)    ? GroupEntity::GROUP_PRIVACY_ACTIVITY_VIEW_EVERYBODY      : (int)$groupMeta->PrivacyActivityView;
			$groupEntity->GroupPrivacyActivityPost    = empty($groupMeta->PrivacyActivityPost)    ? GroupEntity::GROUP_PRIVACY_ACTIVITY_POST_GROUP_MEMBERS    : (int)$groupMeta->PrivacyActivityPost;
			$groupEntity->GroupPrivacyActivityComment = empty($groupMeta->PrivacyActivityComment) ? GroupEntity::GROUP_PRIVACY_ACTIVITY_COMMENT_GROUP_MEMBERS : (int)$groupMeta->PrivacyActivityComment;

			empty($groupMeta->PictureFileName) ?: $groupEntity->PictureFileName = $groupMeta->PictureFileName;
			empty($groupMeta->CoverFileName)   ?: $groupEntity->CoverFileName   = $groupMeta->CoverFileName;

			$arrGroups[ $groupEntity->Id ] = $groupEntity;
		}

		wp_reset_postdata();

		return $arrGroups;
	}

	/**
	 *
	 * @return array | \UltraCommunity\Entities\GroupEntity[]
	 */

	public static function getGroupsByUserId( $userId, $pageNumber = 1, $postsPerPage = 12, $arrAdditionalArguments = array() ) {
		$arrAdditionalArguments['author'] = (int) $userId;

		return self::getGroupEntities( $pageNumber, $postsPerPage, $arrAdditionalArguments );
	}


	/**
	 *
	 * @return null | \UltraCommunity\Entities\GroupEntity
	 */
	public static function getGroupEntityBy( $groupKey ) {
		if ( empty( $groupKey ) ) {
			return null;
		}

		$wpPostInfo = null;
		switch ( true ) {
			case ( false !== \filter_var( $groupKey, \FILTER_VALIDATE_INT ) ) :
				$wpPostInfo = WpPostRepository::findByPostId( $groupKey );
				break;

			case $groupKey instanceof \WP_Post :
				$wpPostInfo = ! empty( $groupKey->ID ) ? WpPostRepository::findByPostId( $groupKey->ID ) : WpPostRepository::findByPostSlug( $groupKey->post_name, PostTypeController::POST_TYPE_GROUP );
				break;

			case $groupKey instanceof GroupEntity :
				$wpPostInfo = ! empty( $groupKey->Id ) ? WpPostRepository::findByPostId( $groupKey->Id ) : WpPostRepository::findByPostSlug( $groupKey->Slug, PostTypeController::POST_TYPE_GROUP );
				break;

			default :
				! is_string( $groupKey ) ?: $wpPostInfo = WpPostRepository::findByPostSlug( $groupKey, PostTypeController::POST_TYPE_GROUP );
				break;
		}

		if ( empty( $wpPostInfo->post_type ) || $wpPostInfo->post_type !== PostTypeController::POST_TYPE_GROUP ) {
			return null;
		}

		$groupEntity = GroupEntity::fromWPPost( $wpPostInfo );

		$groupMeta = WpPostRepository::getPostMeta( $groupEntity->Id, self::$groupMetaKey, true );

		if ( empty( $groupMeta->Type ) ) {
			return null;
		}

		$groupEntity->GroupTypeId = (int)$groupMeta->Type;
		//$groupEntity->GroupPrivacyActivityView    = empty($groupMeta->PrivacyActivityView)    ? GroupEntity::GROUP_PRIVACY_ACTIVITY_VIEW_EVERYBODY   : (int)$groupMeta->PrivacyActivityView;
		$groupEntity->GroupPrivacyActivityPost    = empty($groupMeta->PrivacyActivityPost)    ? GroupEntity::GROUP_PRIVACY_ACTIVITY_POST_GROUP_MEMBERS : (int)$groupMeta->PrivacyActivityPost;
		$groupEntity->GroupPrivacyActivityComment = empty($groupMeta->PrivacyActivityComment) ? GroupEntity::GROUP_PRIVACY_ACTIVITY_COMMENT_GROUP_MEMBERS : (int)$groupMeta->PrivacyActivityComment;

		empty($groupMeta->PictureFileName) ?: $groupEntity->PictureFileName = $groupMeta->PictureFileName;
		empty($groupMeta->CoverFileName)   ?: $groupEntity->CoverFileName   = $groupMeta->CoverFileName;

		return $groupEntity;
	}

	public static function save( GroupEntity $groupEntity )
	{
		$arrPostArgs                = array();
		$arrPostArgs['ID']          = empty( $groupEntity->Id ) ? 0 : (int) $groupEntity->Id;
		$arrPostArgs['post_author'] = empty( $groupEntity->AdminUserId ) ? 0 : (int) $groupEntity->AdminUserId;

		$arrPostArgs['post_content'] = $groupEntity->Description;
		$arrPostArgs['post_title']   = $groupEntity->Name;

		$arrPostArgs['post_type'] = PostTypeController::POST_TYPE_GROUP;

		$arrPostArgs['post_status']    = 'publish';
		$arrPostArgs['comment_status'] = 'closed';
		$arrPostArgs['ping_status']    = 'closed';


		$groupMeta       = new \stdClass();
		$groupMeta->Type                   = empty( $groupEntity->GroupTypeId )               ? GroupEntity::GROUP_TYPE_PUBLIC                            : (int)$groupEntity->GroupTypeId;
		//$groupMeta->PrivacyActivityView    = empty($groupEntity->GroupPrivacyActivityView)    ? GroupEntity::GROUP_PRIVACY_ACTIVITY_VIEW_EVERYBODY      : (int)$groupEntity->GroupPrivacyActivityView;
		$groupMeta->PrivacyActivityPost    = empty($groupEntity->GroupPrivacyActivityPost)    ? GroupEntity::GROUP_PRIVACY_ACTIVITY_POST_GROUP_MEMBERS    : (int)$groupEntity->GroupPrivacyActivityPost;
		$groupMeta->PrivacyActivityComment = empty($groupEntity->GroupPrivacyActivityComment) ? GroupEntity::GROUP_PRIVACY_ACTIVITY_COMMENT_GROUP_MEMBERS : (int)$groupEntity->GroupPrivacyActivityComment;

		empty($groupEntity->PictureFileName) ?: $groupMeta->PictureFileName = $groupEntity->PictureFileName;
		empty($groupEntity->CoverFileName)   ?: $groupMeta->CoverFileName = $groupEntity->CoverFileName;

		$arrPostArgs['meta_input'] = array( self::$groupMetaKey => $groupMeta );


		return WpPostRepository::save($arrPostArgs);
	}

	public static function delete($groupId)
	{
		return WpPostRepository::delete($groupId);
	}


//	public static function saveGroupDiscussion()
//	{
//
//	}
}