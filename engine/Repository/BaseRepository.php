<?php
namespace UltraCommunity\Repository;

use UltraCommunity\Entities\GroupEntity;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Entities\GroupUserEntity;
use UltraCommunity\Entities\UserEntity;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\Repository\WpPostRepository;
use UltraCommunity\MchLib\WordPress\Repository\WpUserRepository;


abstract class BaseRepository
{

	protected function __construct() {
	}

	protected static function executePreparedQuery($sqlPreparedQuery)
	{
		global $wpdb;
		return null !== ($queryResult = $wpdb->get_results($sqlPreparedQuery)) ? (array)$queryResult : array();
	}


	protected static function getTablePrefix()
	{
		global $wpdb;
		return $wpdb->prefix;
	}

	/**
	 * @return \wpdb
	 */
	protected static function getDbObject()
	{
		global $wpdb;
		return $wpdb;
	}

	public static function getLastQueryTotalFoundRows()
	{
		global $wpdb;
		return (int) $wpdb->get_var('SELECT FOUND_ROWS()');

	}



	public static function getActivityIdFromKey($activityKey)
	{
		if(empty($activityKey))
			return null;

		switch(true)
		{
			case !empty($activityKey->ActivityId) : // instance of ActivityEntity
				return (int)$activityKey->ActivityId;

			case (false !== \filter_var($activityKey, \FILTER_VALIDATE_INT, array('options' => array( 'min_range' => 0)))) :
				return (int)$activityKey;

		}

		return null;
	}


	public static function getGroupIdFromKey($groupKey)
	{
		if (empty($groupKey))
			return null;

		$wpPostInfo = null;
		switch (true)
		{
			case (false !== \filter_var($groupKey, \FILTER_VALIDATE_INT, array('options' => array( 'min_range' => 0)))) :
				return (int)$groupKey;

			case $groupKey instanceof \WP_Post :
				if(!empty($groupKey->ID))
					return (int)$groupKey->ID;

				$wpPostInfo = WpPostRepository::findByPostSlug($groupKey->post_name, PostTypeController::POST_TYPE_GROUP);
				break;

			case $groupKey instanceof GroupEntity :

				if(!empty($groupKey->Id))
					return (int)$groupKey->Id;

				//$wpPostInfo = WpPostRepository::findByPostSlug($groupKey->Slug, PostTypeController::POST_TYPE_GROUP);
				break;

			case $groupKey instanceof GroupUserEntity :

				if(!empty($groupKey->GroupId))
					return (int)$groupKey->GroupId;

				break;

			default :
				!is_string($groupKey) ?: $wpPostInfo = WpPostRepository::findByPostSlug($groupKey, PostTypeController::POST_TYPE_GROUP);
				break;
		}

//		if(empty($wpPostInfo->post_type) || $wpPostInfo->post_type !== PostTypeController::POST_TYPE_GROUP){
//			return null;
//		}

		return empty($wpPostInfo->ID) ? null : (int)$wpPostInfo->ID;
	}

	public static function getUserIdFromKey($userKey)
	{
		if(empty($userKey))
			return null;

		$wpUserInfo = null;
		if(false !== \filter_var($userKey, \FILTER_VALIDATE_INT, array('options' => array( 'min_range' => 0)))){
			return (int)$userKey;
		}

		switch (true)
		{
			case $userKey instanceof \WP_User :
				switch (true)
				{
					case ( !empty($userKey->ID) )            : return (int)$userKey->ID;//$wpUserInfo = WpUserRepository::getUserById($userKey->ID);break;
					case ( !empty($userKey->user_login) )    : $wpUserInfo = WpUserRepository::getUserByUserName($userKey->user_login); break;
					case ( !empty($userKey->user_email) )    : $wpUserInfo = WpUserRepository::getUserByEmail($userKey->user_email); break;
					case ( !empty($userKey->user_nicename) ) : $wpUserInfo = WpUserRepository::getUserByNiceName($userKey->user_nicename); break;

				}
			break;

			case $userKey instanceof UserEntity :
				switch (true)
				{
					case ( !empty($userKey->Id) )            : return (int)$userKey->Id;//$wpUserInfo = WpUserRepository::getUserById($userKey->Id);break;
					case ( !empty($userKey->UserName) )      : $wpUserInfo = WpUserRepository::getUserByUserName($userKey->UserName); break;
					case ( !empty($userKey->Email) )         : $wpUserInfo = WpUserRepository::getUserByEmail($userKey->Email); break;
					case ( !empty($userKey->NiceName) )      : $wpUserInfo = WpUserRepository::getUserByNiceName($userKey->NiceName); break;

				}
			break;

			case $userKey instanceof \WP_Post :
				switch (true)
				{
					case ( !empty($userKey->post_author) )  : return (int)$userKey->post_author;
				}
			break;

			case $userKey instanceof \WP_Comment :

				if ( !empty($userKey->user_id) && (false !== \filter_var($userKey->user_id, \FILTER_VALIDATE_INT, array('options' => array( 'min_range' => 0)))) )
					return (int)$userKey->user_id;

				empty($userKey->comment_author_email) ?: $wpUserInfo = WpUserRepository::getUserByEmail($userKey->comment_author_email);

			break;

			case \is_string($userKey) :

				if(false !== \filter_var($userKey, \FILTER_VALIDATE_EMAIL)){
					$wpUserInfo = WpUserRepository::getUserByEmail($userKey);
				}
				else {
					$wpUserInfo = WpUserRepository::getUserByUserName($userKey);
				}

			break;
		}

		return empty($wpUserInfo->ID) ? null : (int)$wpUserInfo->ID;
	}



	protected static function getMethodCacheKey($methodName, $arrParameters)
	{
		return md5($methodName . json_encode(array_filter($arrParameters)));
	}


	protected static function getUserRelationsTableName()
	{
		return self::getTablePrefix() . 'uc_user_relations';
	}

	protected static function getActivityTableName()
	{
		return self::getTablePrefix() . 'uc_user_activity';
	}

	protected static function getActivityRepostsTableName()
	{
		return self::getTablePrefix() . 'uc_user_activity_reposts';
	}

	protected static function getUserNotificationsTableName()
	{
		return self::getTablePrefix() . 'uc_user_notifications';
	}

	protected static function getGroupUsersTableName() {
		return self::getTablePrefix() . 'uc_group_users';
	}


	public static function deleteCreatedTablesOnUninstall()
	{
		$arrCreatedTables = array(
			self::getUserRelationsTableName(), self::getActivityTableName(),
			self::getActivityRepostsTableName(), self::getUserNotificationsTableName(),
			self::getGroupUsersTableName(),
		);

		if( !defined( 'WP_UNINSTALL_PLUGIN' ) || !current_user_can('delete_plugins') )
			return;

		foreach ($arrCreatedTables as $createdTable)
		{
			self::executePreparedQuery( "DROP TABLE IF EXISTS $createdTable" );
		}

	}

	
	public static function handlePluginVersionChanges($oldPluginVersion, $newPluginVersion = null)
	{
		if(version_compare($oldPluginVersion, '2.1.0', '<'))
		{

			if(self::tableExists($userRelationsTableName = self::getUserRelationsTableName()))
			{
				self::dropTableIndex($userRelationsTableName, 'idx_uc_relations');

				self::tableHasIndex($userRelationsTableName, 'idx_uc_puser_rel') ?: self::getDbObject()->get_results("CREATE INDEX idx_uc_puser_rel ON $userRelationsTableName (PrimaryUserId, RelationTypeId, StatusId, CreatedDate DESC)");
				self::tableHasIndex($userRelationsTableName, 'idx_uc_puser_rel') ?: self::getDbObject()->get_results("CREATE INDEX idx_uc_suser_rel ON $userRelationsTableName (SecondaryUserId, RelationTypeId, StatusId, CreatedDate DESC)");

			}

			if(self::tableExists($userActivityTableName = self::getActivityTableName()))
			{
				self::getDbObject()->get_results("ALTER TABLE $userActivityTableName ADD COLUMN PostFormatTypeId TINYINT NOT NULL DEFAULT 0 AFTER CreatedDate");

				self::dropTableIndex($userActivityTableName, 'idx_uc_activity');

				self::tableHasIndex($userRelationsTableName, 'idx_uc_user')   ?: self::getDbObject()->get_results("CREATE INDEX idx_uc_user   ON $userRelationsTableName (UserId, CreatedDate DESC)");
				self::tableHasIndex($userRelationsTableName, 'idx_uc_target') ?: self::getDbObject()->get_results("CREATE INDEX idx_uc_target ON $userRelationsTableName (TargetTypeId, TargetId, CreatedDate DESC)");

			}
			
		}

		
	}


	public static function cleanUpTables()
	{
		global $wpdb;
//		$wpdb->prefix;


//		$wpdb->posts;
//		$wpdb->postmeta;
//		$wpdb->comments;
//		$wpdb->commentmeta;
//		$wpdb->terms;
//		$wpdb->term_taxonomy;
//		$wpdb->term_relationships;
//		$wpdb->users;
//		$wpdb->usermeta;
//		$wpdb->links;
//		$wpdb->options;

		$wpUsersTable = $wpdb->users;
		$wpPostsTable = $wpdb->posts;
		
		$notificationsTable     = self::getUserNotificationsTableName();
		$userRelationsTable     = self::getUserRelationsTableName();
		$activityTable          = self::getActivityTableName();
		$activityRepostsTable   = self::getActivityRepostsTableName();
		$userNotificationsTable = self::getUserNotificationsTableName();
		$groupUsersTable        = self::getGroupUsersTableName();

		
		$arrCreatedTables = array_flip($wpdb->get_col("SHOW TABLES LIKE '{$wpdb->prefix}uc_%'"));



################## Notifications ########################################
if(isset($arrCreatedTables[$notificationsTable]))
{
	$query = <<<SQL
DELETE $notificationsTable FROM $notificationsTable
LEFT JOIN $wpUsersTable wpu1 ON (wpu1.ID = UserId)
LEFT JOIN $wpUsersTable wpu2 ON (wpu2.ID = InitiatorUserId)
WHERE wpu1.ID IS NULL OR wpu2.ID IS NULL;
SQL;

	 $wpdb->query($query);
}



################## User Groups ########################################
//if(isset($arrCreatedTables[$groupUsersTable]))
//{
//	$query = <<<SQL
//DELETE $wpPostsTable FROM $wpPostsTable
//LEFT JOIN $groupUsersTable ON (GroupId = ID)
//WHERE post_type = 'uc-user-group' AND GroupUserId IS NULL;
//SQL;
//
//	$wpdb->query($query);
//}


	}





	protected static function tableExists($tableName)
	{
		global $wpdb;
		return ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $tableName)) === $tableName);
	}

	private static function tableHasIndex($tableName, $indexName)
	{
//		if(!self::tableExists($tableName))
//			return false;

		foreach( self::getDbObject()->get_results("SHOW INDEXES FROM $tableName")  as $stdResultRow)
		{
			if(empty($stdResultRow->Key_name) || $stdResultRow->Key_name !== $indexName)
				continue;

			return true;
		}

		return false;
	}


	private static function dropTableIndex($tableName, $indexName)
	{
		if(!self::tableHasIndex($tableName, $indexName))
			return;

		self::getDbObject()->get_results("DROP INDEX $indexName ON $tableName");

	}
}
