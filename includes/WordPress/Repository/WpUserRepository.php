<?php
namespace UltraCommunity\MchLib\WordPress\Repository;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\Utils\MchWpUtils;

final class WpUserRepository
{
	public static function getUsersByIds(array $arrUserIds)
	{
		$arrUserIds = array_filter($arrUserIds, function($primaryKey){ return MchValidator::isPositiveInteger($primaryKey);});

		if(empty($arrUserIds))
			return array();

		$arrArguments = array('count_total' => false, 'include' => $arrUserIds, );

		return (array)(new \WP_User_Query($arrArguments))->get_results();

	}

	public static function getUsersByRole($roleKey, $arrAdditionalArguments = array())
	{
		$arrArguments = array(
				'count_total' => false,
				//'blog_id'     => (0 === $blogId) ? get_current_blog_id() : (int)$blogId,
				'role'        => $roleKey,
				'orderby'     => 'ID',
				'order'       => 'DESC',

		);

		if(empty($roleKey))
		{
			unset($arrArguments['role']);
		}

		$arrArguments = wp_parse_args( $arrAdditionalArguments, $arrArguments );

		$wpUsersQuery = new \WP_User_Query($arrArguments);

		return (array)$wpUsersQuery->get_results();

	}

	/**
	 * @param $userId
	 * @param string|array $postType
	 *
	 * @return mixed
	 */
	public static function getNumberOfPublishedPosts($userId, $arrPostType = array())
	{
		return \count_user_posts($userId, (array)$arrPostType, true);
	}

	public static function getNumberOfApprovedComments($userId)
	{
		return (int)get_comments(array(
				'status'  => 'approve',
				'count'   => true,
				'user_id' => $userId
		));
	}

	public static function getUserApprovedComments($userId, $pageNumber = 1, $commentsPerPage = 10, $commentType = 'comment')
	{
		if(($pageNumber = (int)$pageNumber) < 1)
			$pageNumber = 1;

		$commentsPerPage = (int)$commentsPerPage;

		return get_comments(array('status'=>'approve', 'number' => $commentsPerPage, 'offset' => ($pageNumber * $commentsPerPage) - $commentsPerPage, 'user_id' => $userId, 'type' => $commentType));
	}

	public static function getUserRoles($userIdORWpUser)
	{

		$wpUserInfo = ($userIdORWpUser instanceof \WP_User) ? $userIdORWpUser: self::getUserById($userIdORWpUser);
		if(empty($wpUserInfo->roles))
			return array();

		$arrUserRoles = array();
		$wpRolesObject = wp_roles();

		foreach ($wpUserInfo->roles as $userRoleSlug)
		{
			if(!isset($wpRolesObject->role_names[$userRoleSlug]))
				continue;

			$arrUserRoles[$userRoleSlug] = $wpRolesObject->role_names[$userRoleSlug]; //translate_user_role( $wpRolesObject->role_names[$userRoleSlug] );
		}

		return $arrUserRoles;
	}

	public static function getUserRoleDescription($userRoleSlug)
	{
		$wpRolesObject = wp_roles();
		return isset($wpRolesObject->role_names[$userRoleSlug]) ? translate_user_role($wpRolesObject->role_names[$userRoleSlug]) : null;
	}

	/**
	 * @param        $userId
	 * @param string $metaKey
	 * @param bool   $single
	 *
	 * @return mixed
	 */
	public static function getUserMeta($userId, $metaKey = '', $single = true)
	{
		$arrUserMeta =  \get_user_meta($userId, $metaKey, $single);

		if(empty($arrUserMeta)){
			return null;
		}

		if(empty($metaKey))
		{
			$arrUserMeta =  array_map(function($arrMetaInfo){return isset($arrMetaInfo[0]) ? $arrMetaInfo[0] : $arrMetaInfo;}, $arrUserMeta);
		}

		return $arrUserMeta;

	}

	/**
	 * @param $userId
	 *
	 * @return null|\WP_User
	 */
	public static function getUserById($userId)
	{
		return false !== ( $user = self::getUserBy('id', (int)$userId) ) ? $user : null;
	}

	public static function getUserByEmail($email)
	{
		return false !== ( $user = self::getUserBy('email', $email) ) ? $user : null;
	}

	public static function getUserByUserName($userName)
	{
		return false !== ( $user = self::getUserBy('login', $userName) ) ? $user : null;
	}

	public static function getUserByNiceName($userName)
	{
		return false !== ( $user = self::getUserBy('slug', $userName) ) ? $user : null;
	}

	/**
	 * @param $field
	 * @param $value
	 *
	 * @return false|\WP_User
	 */
	private static function getUserBy($field, $value)
	{
		return empty($value) ? false : \get_user_by( $field, $value );
	}

	/**
	 * @param \WP_User $wpUser
	 *
	 * @return int|\WP_Error
	 */
	public static function saveUser(\WP_User $wpUser)
	{
		if(!empty($wpUser->ID) && (null !== $oldUserInfo = self::getUserById($wpUser->ID)))
		{
			empty($oldUserInfo->user_registered) ?: $wpUser->user_registered = $oldUserInfo->user_registered;

		}

		return wp_insert_user($wpUser); // does an update if ID is passed



		if($oldUserInfo instanceof \WP_User && $oldUserInfo->exists()) // updating
		{
			if($oldUserInfo->user_registered !== $wpUser->user_registered)
			{
				if(empty($wpUser->user_registered))
				{
					$wpUser->user_registered = $oldUserInfo->user_registered;
				}
				else
				{
					$registerTimeStamp = MchValidator::isInteger($wpUser->user_registered) ? absint($wpUser->user_registered) : strtotime($wpUser->user_registered);

					$wpUser->user_registered = empty($registerTimeStamp) ? $oldUserInfo->user_registered : gmdate( 'Y-m-d H:i:s', $registerTimeStamp );
					if(strpos($wpUser->user_registered, '1970') === 0)
					{
						$wpUser->user_registered = $oldUserInfo->user_registered;
					}
				}

				if($oldUserInfo->user_registered != $wpUser->user_registered)
				{
					$oldRegisteredDate = \DateTime::createFromFormat('Y-m-d', $oldUserInfo->user_registered);
					$newRegisteredDate = \DateTime::createFromFormat('Y-m-d', $wpUser->user_registered);

					$oldRegisteredDate->setTime(0, 0, 0);
					$newRegisteredDate->setTime(0, 0, 0);

					($oldRegisteredDate != $newRegisteredDate) ?: $wpUser->user_registered = $oldUserInfo->user_registered;

				}

			}
		}
		elseif(!empty($wpUser->user_registered))
		{
			$registerTimeStamp = MchValidator::isInteger($wpUser->user_registered) ? absint($wpUser->user_registered) : strtotime($wpUser->user_registered);

			$wpUser->user_registered = empty($registerTimeStamp) ? null : gmdate( 'Y-m-d H:i:s', $registerTimeStamp );
			if(strpos($wpUser->user_registered, '1970') === 0)
			{
				$wpUser->user_registered = null;
			}
		}


	}

	/**
	 * @param $userId
	 * @param $metaKey
	 * @param $metaValue
	 *
	 * @return bool|int
	 */
	public static function saveUserMeta($userId, $metaKey, $metaValue)
	{
		return update_user_meta( $userId, $metaKey, $metaValue ); // returns Meta ID if the key didn't exist; true on successful update; false on failure or if $meta_value is the same as the existing meta value in the database.
	}

	/**
	 * @param        $userId
	 * @param        $metaKey
	 * @param string $metaValue
	 *
	 * @return bool
	 */
	public static function deleteUserMeta($userId, $metaKey, $metaValue = '')
	{
		return delete_user_meta($userId, $metaKey, $metaValue);
	}

	/**
	 * @param $userName
	 *
	 * @return bool
	 */
	public static function userNameExists($userName)
	{
		return (bool)username_exists($userName);
	}

	/**
	 * @param $userEmailAddress
	 *
	 * @return bool
	 */
	public static function userEmailExists($userEmailAddress)
	{
		return (bool)email_exists($userEmailAddress);
	}


	public static function deleteAllUserCommentsByCommentType($userId, $arrCommentType = array())
	{
		if(empty($arrCommentType))
			return;

		$query    = new \WP_Comment_Query;
		$comments = $query->query( array (
				'user_id' => $userId,
				'type__in'=> $arrCommentType,
				'fields'  => 'ids',
				'status'  => 'all',
		) );

		$children = array ();
		foreach ( $comments as $comment ) {
			$children = array_merge( $children,  self::getUserCommentRepliesIds( $comment ) );
		}

		$final = array_filter( array_unique( array_merge( $comments, $children ) ) );
		foreach ( $final as $comment ) {
			wp_delete_comment( $comment, true );
		}

	}


	private static function getUserCommentRepliesIds( $comment_id )
	{
		$query    = new \WP_Comment_Query;
		$comments = $query->query( array (
				'parent' => $comment_id,
				'fields' => 'ids',
				'status' => 'all',
		) );

		if ( empty( $comments ) ) {
			return $comments;
		}
		$children = array ();
		foreach ( $comments as $comment ) {
			$children = array_merge( $children, self::getUserCommentRepliesIds( $comment ) );
		}

		return array_filter( array_unique( array_merge( $comments, $children ) ) );
	}

}

