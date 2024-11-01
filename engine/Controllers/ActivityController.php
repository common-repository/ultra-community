<?php

namespace UltraCommunity\Controllers;


use UltraCommunity\Entities\ActivityEntity;
use UltraCommunity\Entities\UserEntity;
use UltraCommunity\Entities\UserRelationEntity;
use UltraCommunity\MchLib\Utils\MchDirectoryUtils;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\Repository\WpPostRepository;
use UltraCommunity\Repository\ActivityRepository;
use UltraCommunity\Repository\BaseRepository;
use UltraCommunity\Repository\GroupRepository;
use UltraCommunity\Repository\UserRepository;
use UltraCommunity\UltraCommHelper;
use UltraCommunity\UltraCommHooks;
use UltraCommunity\UltraCommUtils;

class ActivityController
{

	public static function userCanManageActivity($userKey, $activityKey)
	{
		$userId = UserRepository::getUserIdFromKey($userKey);

		if(empty($userId))
			return false;

		if( MchWpUtils::isAdminUser($userId) )
			return true;


		$activityEntity = self::getActivityEntityByKey($activityKey);

		if(empty($activityEntity->UserId) || $userId !== (int)$activityEntity->UserId)
			return false;

		return UserRoleController::userHasCapability($userId, UserRoleController::USER_CAP_DELETE_ACTIVITY_POST);


	}

	public static function countActivityLikes($activityKey)
	{
		if ( ! ($activityEntity = self::getActivityEntityByKey($activityKey)) )
			return 0;

		return empty($activityEntity->MetaData->UserLikes) ? 0 : count((array)$activityEntity->MetaData->UserLikes);
	}

	public static function userLikedActivity($userKey, $activityKey)
	{
		if(empty($userKey) || empty($activityKey)){
			return false;
		}

		if ( ! ($activityEntity = self::getActivityEntityByKey($activityKey)) )
			return false;

		return isset($activityEntity->MetaData->UserLikes[UserRepository::getUserIdFromKey($userKey)]);

	}

	public static function userCanRePostActivity($userKey, $activityKey)
	{
		if(! ($userId = ActivityRepository::getUserIdFromKey($userKey)) )
			return false;

		if(! ($activityEntity = self::getActivityEntityByKey($activityKey)) )
			return false;

		if($activityEntity->UserId === $userId)
			return false;

		return ! self::userRePostedActivity($userId, $activityEntity->ActivityId);

	}

	public static function userRePostedActivity($userId, $activityId)
	{
		return isset(self::getUserRePostedActivityKeys($userId)[$activityId]);
	}

	public static function countActivityReposts($activityKey)
	{
		return ActivityRepository::countActivityReposts($activityKey);
	}

	public static function getUserRePostedActivityKeys($userKey)
	{
		if(! ($userId = ActivityRepository::getUserIdFromKey($userKey)) )
			return array();

		static $arrCachedActivities = array();
		if(isset($arrCachedActivities[$userId])){
			return $arrCachedActivities[$userId];
		}

		return  $arrCachedActivities[$userId] = ActivityRepository::getUserRePostedActivityIds($userKey);

	}

	/*
	 * @return ActivityEntity|null
	 */
	public static function getActivityEntityByKey($activityKey)
	{
		if($activityKey instanceof ActivityEntity && !empty($activityKey->ActivityId))
			return $activityKey;

		return ActivityRepository::getActivityByKey($activityKey);
	}

	public static function saveActivity(ActivityEntity $activityEntity)
	{
		$isUpdate = !empty($activityEntity->ActivityId);

		if($isUpdate)
		{
			do_action(UltraCommHooks::ACTION_ACTIVITY_BEFORE_UPDATE, $activityEntity);
		}
		else
		{
			do_action(UltraCommHooks::ACTION_ACTIVITY_BEFORE_PUBLISH, $activityEntity);
		}

		$result =  ActivityRepository::saveActivity($activityEntity);

		if($isUpdate)
		{
			do_action(UltraCommHooks::ACTION_ACTIVITY_AFTER_UPDATED, $activityEntity);
		}
		else
		{
			$activityEntity->ActivityId = $result;

			do_action(UltraCommHooks::ACTION_ACTIVITY_AFTER_PUBLISHED, $activityEntity);

		}

		return $activityEntity->ActivityId;
	}

//	/**
//	 * @param int            $pageNumber
//	 * @param int            $recordsPerPage
//	 * @return ActivityEntity[]
//	 */
//	public static function getGroupActivityList($groupKey, $pageNumber = 1, $recordsPerPage = 10)
//	{
//
//		$activityEntity = new ActivityEntity();
//		$activityEntity->TargetTypeId = ActivityEntity::ACTIVITY_TARGET_TYPE_GROUP;
//		$activityEntity->TargetId     = UserRepository::getGroupIdFromKey($groupKey);
//		$activityEntity->StatusId     = ActivityEntity::ACTIVITY_STATUS_ACTIVE;
//
//		return ActivityRepository::findByEntityProperties($activityEntity, $pageNumber, $recordsPerPage, array());
//
//
//	}

	/**
	 * @param int            $pageNumber
	 * @param int            $recordsPerPage
	 * @return ActivityEntity[]
	 */
	public static function getUserProfileActivityFeed($userKey, $pageNumber = 1, $recordsPerPage = 10)
	{
		return ActivityRepository::getUserProfileActivityFeed($userKey, $pageNumber, $recordsPerPage);
	}


	/**
	 * @param int            $pageNumber
	 * @param int            $recordsPerPage
	 * @return ActivityEntity[]
	 */
	public static function getUserProfileActivityList($userKey, $pageNumber = 1, $recordsPerPage = 10)
	{
		$activityEntity = new ActivityEntity();
		$activityEntity->UserId       = UserRepository::getUserIdFromKey($userKey);
		$activityEntity->StatusId     = ActivityEntity::ACTIVITY_STATUS_ACTIVE;

		return ActivityRepository::findByEntityProperties($activityEntity, $pageNumber, $recordsPerPage, array());
	}


	/**
	 * @param int            $pageNumber
	 * @param int            $recordsPerPage
	 * @return ActivityEntity[]
	 */
	public static function getGroupProfileActivityList($groupKey, $pageNumber = 1, $recordsPerPage = 10)
	{
		$activityEntity = new ActivityEntity();
		$activityEntity->TargetTypeId = ActivityEntity::ACTIVITY_TARGET_TYPE_GROUP;
		$activityEntity->TargetId     = GroupRepository::getGroupIdFromKey($groupKey);
		$activityEntity->StatusId     = ActivityEntity::ACTIVITY_STATUS_ACTIVE;

		return ActivityRepository::findByEntityProperties($activityEntity, $pageNumber, $recordsPerPage, array());

	}

	public static function countActivityComments($activityKey)
	{
		$activityEntity = self::getActivityEntityByKey($activityKey);
		if(empty($activityEntity->PostTypeId))
			return 0;

		$wpPost = WpPostRepository::findByPostId($activityEntity->PostTypeId);

		return empty($wpPost->comment_count) ? 0 : (int)$wpPost->comment_count;

		return (int)get_comments_number( $activityEntity->PostTypeId );
	}

	public static function getActivityComments($activityKey)
	{
		if(0 === self::countActivityComments($activityKey))
			return array();

		$activityEntity = self::getActivityEntityByKey($activityKey);


		return  (array)get_comments(array(
			'post_id' => $activityEntity->PostTypeId,
			'type' => PostTypeController::POST_TYPE_ACTIVITY,
			'status'  => 'approve',
			'no_found_rows' => false,
			'update_comment_meta_cache' => false,
		));

	}

	public static function countActiveActivitiesByTargetId($targetId)
	{
		$activityEntity = new ActivityEntity();
		$activityEntity->TargetId     = (int)$targetId;
		$activityEntity->StatusId     = ActivityEntity::ACTIVITY_STATUS_ACTIVE;

		return ActivityRepository::countActivityByEntityProperties($activityEntity);
	}

	public static function initActivityHooks()
	{

		MchWpUtils::addActionHook(UltraCommHooks::ACTION_AFTER_USER_PROFILE_PHOTO_CHANGED, function ($userKey){
			$userId = UserRepository::getUserIdFromKey($userKey);
			if(empty($userId))
				return;

			$activityEntity = new ActivityEntity();
			$activityEntity->UserId = $activityEntity->TargetId = $userId;
			$activityEntity->TargetTypeId = ActivityEntity::ACTIVITY_TARGET_TYPE_USER;
			$activityEntity->ActionTypeId = ActivityEntity::ACTION_TYPE_USER_PROFILE_PHOTO_CHANGED;

			ActivityController::saveActivity($activityEntity);
		});

		MchWpUtils::addActionHook(UltraCommHooks::ACTION_AFTER_USER_PROFILE_COVER_CHANGED, function ($userKey){
			$userId = UserRepository::getUserIdFromKey($userKey);
			if(empty($userId))
				return;

			$activityEntity = new ActivityEntity();
			$activityEntity->UserId = $activityEntity->TargetId = $userId;
			$activityEntity->TargetTypeId = ActivityEntity::ACTIVITY_TARGET_TYPE_USER;
			$activityEntity->ActionTypeId = ActivityEntity::ACTION_TYPE_USER_PROFILE_COVER_CHANGED;

			ActivityController::saveActivity($activityEntity);
		});

		MchWpUtils::addActionHook(UltraCommHooks::ACTION_AFTER_USER_GROUP_CREATED, function ($groupKey){
			$groupEntity = GroupController::getGroupEntityBy($groupKey);
			if(empty($groupEntity->AdminUserId))
				return;

			$activityEntity = new ActivityEntity();
			$activityEntity->UserId = $groupEntity->AdminUserId;
			$activityEntity->TargetId = $groupEntity->Id;
			$activityEntity->TargetTypeId = ActivityEntity::ACTIVITY_TARGET_TYPE_USER;
			$activityEntity->ActionTypeId = ActivityEntity::ACTION_TYPE_NEW_GROUP_CREATED;

			ActivityController::saveActivity($activityEntity);
		});


		foreach(array('wp_trash_post', 'before_delete_post') as $postHook)
		{
			MchWpUtils::addActionHook($postHook, function ($wpPostId){
				$wpPost = get_post($wpPostId);
				if(empty($wpPost->ID) || empty($wpPost->post_type))
					return;

				if($wpPost->post_type == 'post')
				{
					$activityEntity = new ActivityEntity();
					$activityEntity->TargetTypeId = ActivityEntity::ACTIVITY_TARGET_TYPE_USER;
					$activityEntity->ActionTypeId = ActivityEntity::ACTION_TYPE_NEW_BLOG_POST;
					$activityEntity->UserId       = (int)$wpPost->post_author;
					$activityEntity->TargetId     = (int)$wpPost->ID;

					foreach(ActivityRepository::findByEntityProperties($activityEntity) as $activityEntity){
						ActivityRepository::deleteActivity($activityEntity);
						MchDirectoryUtils::deleteDirectory(UltraCommUtils::getActivityAttachmentsBaseDirectoryPath($activityEntity));
					}
				}

				if($wpPost->post_type == PostTypeController::POST_TYPE_ACTIVITY)
				{
					$activityEntity = new ActivityEntity();
					$activityEntity->PostTypeId     = (int)$wpPost->ID;

					foreach(ActivityRepository::findByEntityProperties($activityEntity) as $activityEntity)
					{
						ActivityRepository::deleteActivity($activityEntity);
						MchDirectoryUtils::deleteDirectory(UltraCommUtils::getActivityAttachmentsBaseDirectoryPath($activityEntity));
					}
				}


				if($wpPost->post_type == PostTypeController::POST_TYPE_GROUP)
				{
					$activityEntity = new ActivityEntity();
					$activityEntity->TargetId   = (int)$wpPost->ID;

					foreach(ActivityRepository::findByEntityProperties($activityEntity) as $activityEntity)
					{
						ActivityRepository::deleteActivity($activityEntity);
						MchDirectoryUtils::deleteDirectory(UltraCommUtils::getActivityAttachmentsBaseDirectoryPath($activityEntity));
					}

				}


			});
		}

		MchWpUtils::addActionHook('transition_post_status', function($newPostStatus, $oldPostStatus, $wpPost){

			if(empty($wpPost->post_type) || empty($wpPost->ID) || !in_array($wpPost->post_type, UltraCommHelper::getUserDisplayablePostTypes()) )
				return;

			$activityEntity = new ActivityEntity();
			$activityEntity->TargetTypeId = ActivityEntity::ACTIVITY_TARGET_TYPE_USER;
			$activityEntity->ActionTypeId = ActivityEntity::ACTION_TYPE_NEW_BLOG_POST;
			$activityEntity->UserId       = (int)$wpPost->post_author;
			$activityEntity->TargetId     = (int)$wpPost->ID;

			$activityPostType = PostTypeController::getPostTypeInstance(PostTypeController::POST_TYPE_ACTIVITY);
			$activityPostType->UserId = $activityEntity->UserId;

			$arrActivityEntities = ActivityRepository::findByEntityProperties($activityEntity);

			if($newPostStatus == $oldPostStatus && 'publish' == $newPostStatus)
			{
				if( empty($arrActivityEntities) ){
					!empty($activityEntity->PostTypeId) ?: $activityEntity->PostTypeId = PostTypeController::publishPostType($activityPostType);
					ActivityController::saveActivity($activityEntity);
				}

				return;
			}

			if('publish' === $newPostStatus)
			{

				if( empty($arrActivityEntities) && empty($wpPost->post_password) ){
					!empty($activityEntity->PostTypeId) ?: $activityEntity->PostTypeId = PostTypeController::publishPostType($activityPostType);
					ActivityController::saveActivity($activityEntity);
					return;
				}

				if (!empty( $wpPost->post_password))
				{
					foreach($arrActivityEntities as $activityEntity){
						ActivityRepository::deleteActivity($activityEntity);
					}
					return;
				}

			}
			elseif('publish' === $oldPostStatus)
			{
				foreach($arrActivityEntities as $activityEntity){
					ActivityRepository::deleteActivity($activityEntity);
				}
			}


		}, 10, 3);

		MchWpUtils::addActionHook(UltraCommHooks::ACTION_USER_RELATION_AFTER_SAVE, function (UserRelationEntity $userRelationEntity = null){

			if(empty($userRelationEntity->RelationId) || empty($userRelationEntity->PrimaryUserId) ||  empty($userRelationEntity->SecondaryUserId))
				return;

			if((int)$userRelationEntity->StatusId !== UserRelationEntity::RELATION_STATUS_ACTIVE)
				return;

			$activityEntity = new ActivityEntity();
			$activityEntity->UserId       = $userRelationEntity->PrimaryUserId;
			$activityEntity->TargetId     = $userRelationEntity->SecondaryUserId;
			$activityEntity->TargetTypeId = ActivityEntity::ACTIVITY_TARGET_TYPE_USER;

			switch ($userRelationEntity->RelationTypeId)
			{
				case UserRelationEntity::RELATION_TYPE_FRIENDSHIP :
					$activityEntity->ActionTypeId = ActivityEntity::ACTION_TYPE_USER_FRIENDSHIP;break;

				case UserRelationEntity::RELATION_TYPE_FOLLOWING :
					$activityEntity->ActionTypeId = ActivityEntity::ACTION_TYPE_USER_FOLLOWING;break;

			}

			if(empty($activityEntity->ActionTypeId))
				return;

			ActivityController::saveActivity($activityEntity);

		});

	}


	public static function getActivityRenderTitle(ActivityEntity $activityEntity = null)
	{

		if(empty($activityEntity->UserId))
			return null;

		$userEntity = (isset($activityEntity->UserEntity) && $activityEntity->UserEntity instanceof UserEntity) ? $activityEntity->UserEntity :  UserController::getUserEntityBy($activityEntity->UserId);

		if(empty($userEntity->Id))
			return null;

		$headerTitle  = '<a href="' . UltraCommHelper::getUserProfileUrl($userEntity) . '">';
		$headerTitle .= esc_html(UltraCommHelper::getUserDisplayName($userEntity)) . '</a>';
		$headerTitle .= ' ';

		$groupEntity = ($activityEntity->TargetTypeId === ActivityEntity::ACTIVITY_TARGET_TYPE_GROUP) ? GroupController::getGroupEntityBy($activityEntity->TargetId) : null;

		switch ($activityEntity->ActionTypeId)
		{
			case ActivityEntity::ACTION_TYPE_NEW_BLOG_POST :
				$headerTitle .= esc_html__('published a new blog post', 'ultra-community'); break;

			case ActivityEntity::ACTION_TYPE_USER_PROFILE_PHOTO_CHANGED :
				$headerTitle .= esc_html__('changed profile photo', 'ultra-community'); break;

			case ActivityEntity::ACTION_TYPE_USER_PROFILE_COVER_CHANGED :
				$headerTitle .= esc_html__('changed profile cover', 'ultra-community'); break;

			case ActivityEntity::ACTION_TYPE_NEW_USER_REGISTRATION :
				$headerTitle .= esc_html__('joined us', 'ultra-community'); break;

			case ActivityEntity::ACTION_TYPE_NEW_GROUP_CREATED :
				$headerTitle .= esc_html__('created a new group', 'ultra-community'); break;

			case ActivityEntity::ACTION_TYPE_USER_FOLLOWING :

				if( ! ($targetUserEntity = UserController::getUserEntityBy($activityEntity->TargetId)) )
				    break;

				$headerTitle .= esc_html__('is now following', 'ultra-community') . ' <a href="' . UltraCommHelper::getUserProfileUrl($targetUserEntity) . '">' . UltraCommHelper::getUserDisplayName($targetUserEntity) . '</a>';

				break;

			case ActivityEntity::ACTION_TYPE_USER_FRIENDSHIP :

				if( ! ($targetUserEntity = UserController::getUserEntityBy($activityEntity->TargetId)) )
					break;

				$headerTitle .= esc_html__('and', 'ultra-community') . ' <a href="' . UltraCommHelper::getUserProfileUrl($targetUserEntity) . '">' . UltraCommHelper::getUserDisplayName($targetUserEntity) . '</a> ' . esc_html__('are friends now', 'ultra-community');

				break;

			case ActivityEntity::ACTION_TYPE_NEW_WALL_POST :

				if( !empty($groupEntity->Name) && GroupController::getProfiledGroup() === null)
				{
					$headerTitle .= esc_html__('posted in group', 'ultra-community') . ' ';
					$headerTitle .= '<a href="' . UltraCommHelper::getGroupUrl($groupEntity) . '">';
					$headerTitle .= esc_html($groupEntity->Name) . '</a>';
				}

				break;

		}


		return $headerTitle;

	}



	public static function getActivityPostAllowedTags()
	{
		$arrAllowedTags = array(
			'a' => array(
				'href' => true,
				'title' => true,
			),
			'abbr' => array(
				'title' => true,
			),
			'acronym' => array(
				'title' => true,
			),
			'b' => array(),
			'blockquote' => array(
				'cite' => true,
			),
			'cite' => array(),
			'del' => array(
				'datetime' => true,
			),
			'em' => array(),
			'i' => array(),
			'q' => array(
				'cite' => true,
			),
			'strong' => array(),
		);

		$arrAllowedTags['a']['rel']      = array();
		$arrAllowedTags['img']           = array();
		$arrAllowedTags['img']['src']    = array();
		$arrAllowedTags['img']['alt']    = array();
		$arrAllowedTags['img']['width']  = array();
		$arrAllowedTags['img']['height'] = array();
		$arrAllowedTags['span']          = array();
		$arrAllowedTags['ul'] = array();
		$arrAllowedTags['ol'] = array();
		$arrAllowedTags['li'] = array();

		return $arrAllowedTags;
	}


}


