<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Repository;


use UltraCommunity\Controllers\UserRoleController;
use UltraCommunity\Entities\UserPrivacyEntity;
use UltraCommunity\Entities\UserReviewEntity;
use UltraCommunity\Entities\UserEntity;
use UltraCommunity\Entities\UserMetaEntity;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\Repository\WpPostRepository;
use UltraCommunity\MchLib\WordPress\Repository\WpUserRepository;

use UltraCommunity\Modules\UserRole\UserRoleAdminModule;
use UltraCommunity\UltraCommException;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\UltraCommHelper;

final class UserRepository extends BaseRepository
{
	CONST TOTAL_FOUND_ROWS = 'uc-result-found-rows';

	private static $userMetaKey = 'ultracomm-user-info';

	/**
	 * @param UserEntity $userEntity
	 *
	 * @return int - The userId being saved or 0 if something got wrong
	 * @throws UltraCommException
	 */
	public static function saveUser(UserEntity $userEntity)
	{
		$userEntity = clone $userEntity;

		if( ! $userEntity->UserMetaEntity instanceof UserMetaEntity ){
			WpUserRepository::deleteUserMeta((int)$userEntity->Id, self::$userMetaKey);
			return 0;
		}


		if(MchWpUtils::isWPError($saveResult =  WpUserRepository::saveUser($userEntity->toWPUser()) )){
			throw new UltraCommException($saveResult->get_error_message());
		}

		$userEntity->Id = (int)$saveResult;

		WpUserRepository::saveUserMeta($userEntity->Id, self::$userMetaKey, $userEntity);

		return $userEntity->Id;
	}


	/**
	 * @return \UltraCommunity\Entities\UserEntity
	 */
	public static function getUserEntityBy($userKey) //$useStringAsSlug = false
	{
		if(empty($userKey))
			return null;

		$wpUserInfo = WpUserRepository::getUserById(parent::getUserIdFromKey($userKey));

		if( empty($wpUserInfo->ID) )
			return null;

		$userEntity = WpUserRepository::getUserMeta($wpUserInfo->ID, self::$userMetaKey, true);
		
		$shouldSaveUserMeta = false;
		if(!($userEntity instanceof UserEntity))
		{
			$userEntity = new UserEntity();
			$shouldSaveUserMeta = true;
		}

		//($userEntity instanceof UserEntity) ?: $userEntity = new UserEntity();

		(null !== $userEntity->UserMetaEntity) ?: $userEntity->UserMetaEntity = new UserMetaEntity();

		!empty($userEntity->Id) ?: $userEntity->fromWPUser($wpUserInfo);

		$userEntity->Id = (int)$userEntity->Id;

		if($shouldSaveUserMeta && !empty($userEntity->Id))
		{
			!empty($userEntity->UserMetaEntity) ?: $userEntity->UserMetaEntity = new UserMetaEntity();
			$userEntity->UserMetaEntity->UserStatus = UserMetaEntity::USER_STATUS_APPROVED;
			
			WpUserRepository::saveUserMeta($userEntity->Id, self::$userMetaKey, $userEntity);
			
			//self::saveUser($userEntity);
			//UserRoleController::assignUltraCommRoleToUser($userEntity, UserRoleController::ROLE_MEMBER_SLUG);
		}

		return $userEntity;

	}


	public static function saveUserRegistrationEmailToken($userId, $token)
	{
		WpUserRepository::saveUserMeta($userId, 'uc-confirmation-token', $token);
	}

	public static function getUserRegistrationEmailToken($userId)
	{
		return WpUserRepository::getUserMeta($userId, 'uc-confirmation-token');
	}

	public static function deleteUserRegistrationEmailToken($userId)
	{
		WpUserRepository::deleteUserMeta($userId, 'uc-confirmation-token');
	}


	/**
	 * @param int $pageNumber
	 * @param int $resultsPerPage
	 *
	 * @return UserEntity[]
	 */
	public static function getAllUsers($pageNumber = 1, $resultsPerPage = 12, $arrAdditionalArguments = array())
	{
		$pageNumber = (int)$pageNumber;

		$arrArguments = array(
			'count_total' => false,
			'blog_id'     =>  get_current_blog_id(),
			'orderby'     => 'ID',
			'order'       => 'DESC',
		);

		if($pageNumber > 0)
		{
			$arrArguments['paged']  = $pageNumber;
			$arrArguments['number'] =  (int)$resultsPerPage;
			$arrArguments['count_total'] = true;
		}

		$arrArguments = wp_parse_args( $arrAdditionalArguments, $arrArguments );

		$wpUsersQuery = new \WP_User_Query($arrArguments);

		$arrUserEntity  = array();
		foreach ((array)$wpUsersQuery->get_results() as $wpUser)
		{
			if (null !== ($userEntity = self::getUserEntityBy($wpUser->ID)))
				$arrUserEntity[] = $userEntity;

		}

		if(isset($arrUserEntity[0])){
			$arrUserEntity[self::TOTAL_FOUND_ROWS] = $wpUsersQuery->get_total();
		}

		return $arrUserEntity;
	}


	public static function getUsersForDirectory($pageNumber = 1, $resultsPerPage = 12, $arrUserRoleIds = array(), $arrExcludeMemberIds = array(), $arrIncludeMemberIds = array(), $arrAdditionalArguments = array())
	{
		$pageNumber = (int)$pageNumber;
		$resultsPerPage = (int)$resultsPerPage;
		!empty($pageNumber)     ?: $pageNumber = 1;
		!empty($resultsPerPage) ?: $resultsPerPage = 12;

		$arrArguments = array(
			'count_total' => true,
			'blog_id'     =>  get_current_blog_id(),
			'paged'       => $pageNumber,
			'number'      => $resultsPerPage,
			'orderby'     => 'ID',
			'order'       => 'DESC',

			'meta_query'  =>
				array
				(

					array
					(
						'relation' => 'AND',
						array(
							'key'     => UserPrivacyEntity::META_KEY_HIDE_IN_DIRECTORIES,
							'compare' => 'NOT EXISTS'
						),
					),

					array
					(
						'relation' => 'OR',
						array(
							'key'     => self::$userMetaKey,
							'value'   =>'UserStatus";i:('.implode('|', array(UserMetaEntity::USER_STATUS_AWAITING_REVIEW, UserMetaEntity::USER_STATUS_AWAITING_EMAIL_CONFIRMATION)).')' ,
							'compare' => 'NOT REGEXP',
						),
						array(
							'key'     => self::$userMetaKey,
							'compare' => 'NOT EXISTS',
						)
					),

				)
		);


		if(!empty($arrUserRoleIds))
		{
			!empty($arrArguments['role__in']) ?: $arrArguments['role__in'] = array();
			foreach(PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_ROLE, false) as $userRolePostType)
			{
				if(empty($userRolePostType->PostId) || !in_array($userRolePostType->PostId, $arrUserRoleIds))
					continue;

				if(null === ($adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($userRolePostType)))
					continue;

				$arrArguments['role__in'][] =  $adminModuleInstance->getOption(UserRoleAdminModule::OPTION_ROLE_SLUG);
			}
		}


		if( !empty($arrExcludeMemberIds) && is_array($arrExcludeMemberIds))
		{
			$arrArguments['exclude'] = array();
			foreach($arrExcludeMemberIds as $memberId){
				(!MchValidator::isInteger($memberId)) ?: $arrArguments['exclude'][] = $memberId;
			}
		}

		if( !empty($arrIncludeMemberIds) && is_array($arrIncludeMemberIds))
		{
			$arrArguments['include'] = array();
			foreach($arrIncludeMemberIds as $memberId){
				(!MchValidator::isInteger($memberId)) ?: $arrArguments['include'][] = $memberId;
			}
		}

		$arrArguments = wp_parse_args( $arrAdditionalArguments, $arrArguments );

		$wpUsersQuery = new \WP_User_Query($arrArguments);

		$arrUserEntity  = array();
		$results = $wpUsersQuery->get_results();
		$arrUserEntity[self::TOTAL_FOUND_ROWS] = empty($results) ? 0 : $wpUsersQuery->get_total();

		foreach ($results as $resultObject)
		{
			if(! ($userEntity = self::getUserEntityBy($resultObject)))
				continue;

			$arrUserEntity[] = $userEntity;

		}

//		echo $wpUsersQuery->request;exit;

		return $arrUserEntity;

	}


	public static function getUsersByStatus($userStatus, $pageNumber = 0, $resultsPerPage = 10)
	{
		$pageNumber = (int)$pageNumber;

		$arrArguments = array(
			'count_total' => false,
			'blog_id'     =>  get_current_blog_id(),
			'orderby'     => 'ID',
			'order'       => 'DESC',

			'meta_query'=>
				array(
					array(
						'relation' => 'AND',
						array(
							'key'     => self::$userMetaKey,
							'value' =>'"UserStatus";i:' . ( (int)$userStatus ),
							'compare' => 'LIKE'
						),
					)
				)
		);

		if($pageNumber > 0)
		{
			$arrArguments['paged']  = $pageNumber;
			$arrArguments['number'] =  (int)$resultsPerPage;
			$arrArguments['count_total'] = true;
		}


		$wpUsersQuery = new \WP_User_Query();
		$wpUsersQuery->prepare_query($arrArguments);

		global $wpdb;
		$wpUsersQuery->query_fields = str_replace("{$wpdb->base_prefix}users.*", "{$wpdb->base_prefix}users.*, {$wpdb->base_prefix}usermeta.meta_value", $wpUsersQuery->query_fields);

		$sqlQuery = "SELECT $wpUsersQuery->query_fields $wpUsersQuery->query_from $wpUsersQuery->query_where $wpUsersQuery->query_orderby $wpUsersQuery->query_limit";

		$arrUserEntity  = array();

		$results = (array)$wpdb->get_results( $sqlQuery );

		$arrUserEntity[self::TOTAL_FOUND_ROWS] = empty($results) ? 0 : $wpdb->get_var('SELECT FOUND_ROWS()');

		foreach ($results as $resultObject)
		{
			$userEntity = maybe_unserialize($resultObject->meta_value);
			unset($resultObject->meta_value);
			$wpUserInfo = new \WP_User($resultObject);

			($userEntity instanceof UserEntity) ?: $userEntity = new UserEntity();

			null !== $userEntity->UserMetaEntity ?: $userEntity->UserMetaEntity = new UserMetaEntity();

			!empty($userEntity->Id) ?: $userEntity->fromWPUser($wpUserInfo);
			$arrUserEntity[] = $userEntity;

		}

		return $arrUserEntity;
	}

	/**
	 * @param $userKey
	 *
	 * @return UserReviewEntity[]
	 */
	public static function getUserReviews($userKey)
	{
		$arrUserReviews = self::getUsersReviews(array($userKey));
		return empty($arrUserReviews) ? array() : reset($arrUserReviews);
	}

	/**
	 * @param array $arrUsersKeys
	 *
	 * @return UserReviewEntity[]
	 */
	public static function getUsersReviews(array $arrUsersKeys)
	{
		static $arrUserCachedReviews = array();
		foreach ($arrUsersKeys as &$usersKey){
			$usersKey = self::getUserIdFromKey($usersKey);
		}


		$arrUsersKeys = array_fill_keys($arrUsersKeys, 0);

		$arrCheckAuthors = array_keys(array_diff_key($arrUsersKeys, $arrUserCachedReviews));
		$arrUserReviewsPostType = empty($arrCheckAuthors) ? array() : WpPostRepository::findByPostType(PostTypeController::POST_TYPE_USER_REVIEW, array(
			'author__in' => array_keys($arrUsersKeys), 'cache_results' => false, 'update_post_meta_cache' => false, 'update_post_term_cache' => false,
		));


		foreach ($arrUserReviewsPostType as $userReviewPostType)
		{
			$userReviewPostType->post_author = (int)$userReviewPostType->post_author;
			$arrUserCachedReviews[$userReviewPostType->post_author] = array();

			!($userReviewPostType->comment_count) ?: $arrUsersKeys[$userReviewPostType->post_author] = $userReviewPostType->ID;
		}

		$arrCommentsArgs = array(
			'post__in' => array_filter($arrUsersKeys),
			'type' => PostTypeController::POST_TYPE_USER_REVIEW,
			'status' => 1
		);

		if(!empty($arrCommentsArgs['post__in']))
		{
			foreach (get_comments($arrCommentsArgs) as $userReviewComment) {
				$userReviewEntity = new UserReviewEntity();

				$userReviewEntity->StarsRating = (int)get_comment_meta($userReviewComment->comment_ID, 'uc-stars-rating', true);

				if (empty($userReviewEntity->StarsRating))
					continue;

				$userReviewEntity->Content = esc_html(apply_filters('get_comment_text', $userReviewComment->comment_content, $userReviewComment));
				$userReviewEntity->ReviewerId = (int)$userReviewComment->user_id;
				$userReviewEntity->ReviewId = (int)$userReviewComment->comment_ID;

				$userId = array_search($userReviewComment->comment_post_ID, $arrUsersKeys);
				if (empty($userId))
					continue;

				$userReviewEntity->UserId = (int)$userId;
				$userReviewEntity->CreatedDate = mysql2date('F j, Y', $userReviewComment->comment_date);


				isset($arrUserCachedReviews[$userId]) ?: $arrUserCachedReviews[$userId] = array();

				$arrUserCachedReviews[$userId][] = $userReviewEntity;
			}
		}

		foreach (array_keys(array_diff_key($arrUsersKeys, $arrUserCachedReviews)) as $userId) {
			$arrUserCachedReviews[$userId] = array();
		}

		return array_intersect_key($arrUserCachedReviews, $arrUsersKeys);
	}


	public static function countSubmissionsByTypeAndStatus($userKey, $submissionPostType, $submissionPostStatus)
	{
		$userId = self::getUserIdFromKey($userKey);
		if(empty($userId) || empty($submissionPostType) || empty($submissionPostStatus))
			return 0;

		static $arrCounters = array();
		if(isset($arrCounters[$userId]))
		{
			return isset($arrCounters[$userId][$submissionPostType][$submissionPostStatus]) ? $arrCounters[$userId][$submissionPostType][$submissionPostStatus] : 0;
		}

		$arrCounters[$userId][$submissionPostType] = array();

		global $wpdb;

		$sqlQuery  = "SELECT post_status as PostStatus, COUNT(*) as Counter FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON (p.ID = pm.post_id) ";
		$sqlQuery .= "WHERE 1 = 1 AND pm.meta_key = '_is_uc_submission' AND post_author = %d AND post_type = %s AND post_status IN ('publish', 'pending', 'draft', 'future', 'private', 'trash') GROUP BY post_status";

		foreach (self::executePreparedQuery(self::getDbObject()->prepare($sqlQuery, array($userId, $submissionPostType))) as $resultEntity)
		{
			if(empty($resultEntity->PostStatus)) //|| empty($resultEntity->Counter)
				continue;

			$arrCounters[$userId][$submissionPostType][$resultEntity->PostStatus] = (int)$resultEntity->Counter;
		}

		foreach(array('publish', 'pending', 'draft', 'future', 'private', 'trash') as $postStatus){
			isset($arrCounters[$userId][$submissionPostType][$postStatus]) ?: $arrCounters[$userId][$submissionPostType][$postStatus] = 0;
		}

		return $arrCounters[$userId][$submissionPostType][$submissionPostStatus];


	}

}