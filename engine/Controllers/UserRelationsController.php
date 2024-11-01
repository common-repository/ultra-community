<?php
namespace UltraCommunity\Controllers;

use UltraCommunity\Entities\PageActionEntity;
use UltraCommunity\Entities\UserRelationEntity;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\WordPress\Repository\WpUserRepository;
use UltraCommunity\Modules\UserFollowers\UserFollowersPublicModule;
use UltraCommunity\Modules\UserFriends\UserFriendsPublicModule;
use UltraCommunity\Repository\UserRelationsRepository;
use UltraCommunity\UltraCommHooks;

class UserRelationsController
{

	public static function getUserActiveFriends($userKey)
	{
		return ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FRIENDS) ?  UserFriendsPublicModule::getUserActiveFriends($userKey) : array();
	}

	public static function activeFriendshipExistsBetween($primaryUserKey, $secondaryUserKey)
	{
		return ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FRIENDS) ?  UserFriendsPublicModule::activeFriendshipExistsBetween($primaryUserKey, $secondaryUserKey) : false;
	}

	public static function getFriendshipPendingRequests($primaryUserKey)
	{
		return ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FRIENDS) ?  UserFriendsPublicModule::getFriendshipPendingRequests($primaryUserKey) : array();
	}


	public static function countFriendshipPendingRequests($primaryUserKey)
	{
		return count(self::getFriendshipPendingRequests($primaryUserKey));
	}

	public static function countFriends($primaryUserKey)
	{
		
		return UserRelationsRepository::countUserRelationsByType($primaryUserKey, UserRelationEntity::RELATION_TYPE_FRIENDSHIP);
	}

	public static function countFollowers($primaryUserKey)
	{
		return UserRelationsRepository::countUserRelationsByType($primaryUserKey, UserRelationEntity::RELATION_TYPE_FOLLOWED);
	}

	public static function countFollowing($primaryUserKey)
	{
		return UserRelationsRepository::countUserRelationsByType($primaryUserKey, UserRelationEntity::RELATION_TYPE_FOLLOWING);
	}

	public static function userCanSendFriendshipRequest($primaryUserKey, $secondaryUserKey)
	{
		if(self::isUserBlocking($secondaryUserKey, $primaryUserKey))
			return false;

		return ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FRIENDS) ?  UserFriendsPublicModule::userCanSendFriendshipRequest($primaryUserKey, $secondaryUserKey) : false;
	}

	public static function userCanSendUnFriendRequest($primaryUserKey, $secondaryUserKey)
	{
		return ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FRIENDS) ?  UserFriendsPublicModule::userCanSendUnFriendRequest($primaryUserKey, $secondaryUserKey) : false;
	}

	public static function userCanCancelFriendshipRequest($primaryUserKey, $secondaryUserKey)
	{
		return ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FRIENDS) ?  UserFriendsPublicModule::userCanCancelFriendshipRequest($primaryUserKey, $secondaryUserKey) : false;
	}

	public static function getUserActiveFollowingUsers($userKey)
	{
		return ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FOLLOWERS) ? UserFollowersPublicModule::getUserActiveFollowingUsers($userKey) : array();
	}

	public static function getUserActiveFollowers($userKey)
	{
		return ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FOLLOWERS) ? UserFollowersPublicModule::getUserActiveFollowers($userKey) : array();
	}

	public static function isUserFollowing($primaryUserKey, $secondaryUserKey)
	{
		return ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FOLLOWERS) ? UserFollowersPublicModule::isUserFollowing($primaryUserKey, $secondaryUserKey) : false;
	}

	public static function isUserFollowedBy($primaryUserKey, $secondaryUserKey)
	{
		return ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FOLLOWERS) ? UserFollowersPublicModule::isUserFollowedBy($primaryUserKey, $secondaryUserKey) : false;
	}

	public static function userCanFollow($primaryUserKey, $secondaryUserKey)
	{
		if(self::isUserBlocking($secondaryUserKey, $primaryUserKey))
			return false;

		return ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FOLLOWERS) ? UserFollowersPublicModule::userCanFollow($primaryUserKey, $secondaryUserKey) : false;
	}

	public static function userCanUnFollow($primaryUserKey, $secondaryUserKey)
	{

		return ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FOLLOWERS) ? UserFollowersPublicModule::userCanUnFollow($primaryUserKey, $secondaryUserKey) : false;
	}

	public static function isUserBlocking($primaryUserKey, $secondaryUserKey)
	{
		$secondaryUserKey = UserRelationsRepository::getUserIdFromKey($secondaryUserKey);
		if(empty($secondaryUserKey))
			return false;

		$arrBlockedUsers = self::getUserRelations($primaryUserKey, UserRelationEntity::RELATION_TYPE_BLOCKING);

		return !empty($arrBlockedUsers[$secondaryUserKey]);
	}

	public static function isUserBlockedBy($primaryUserKey, $secondaryUserKey)
	{
		$secondaryUserKey = UserRelationsRepository::getUserIdFromKey($secondaryUserKey);
		$primaryUserKey   = UserRelationsRepository::getUserIdFromKey($primaryUserKey);
		if(empty($secondaryUserKey) || empty($primaryUserKey))
			return false;

		$arrBlockedUsers = self::getUserRelations($secondaryUserKey, UserRelationEntity::RELATION_TYPE_BLOCKING);

		return !empty($arrBlockedUsers[$primaryUserKey]);
	}

	public static function userCanBlock($primaryUserKey, $secondaryUserKey)
	{
		if(!ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FOLLOWERS) && !ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FRIENDS))
			return false;

		$primaryUserKey   = UserRelationsRepository::getUserIdFromKey($primaryUserKey);
		$secondaryUserKey = UserRelationsRepository::getUserIdFromKey($secondaryUserKey);
		if(empty($secondaryUserKey) || UserRoleController::userHasCapability($secondaryUserKey, UserRoleController::USER_CAP_MANAGE_ULTRA_COMMUNITY))
			return false;

		return !self::isUserBlocking($primaryUserKey, $secondaryUserKey);

	}

	public static function getUserRelations($userKey, $relationTypeId, $relationStatusId = UserRelationEntity::RELATION_STATUS_ACTIVE)
	{
		$userId = UserRelationsRepository::getUserIdFromKey($userKey);
		if(empty($userId) || (!ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FOLLOWERS) && !ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FRIENDS)))
			return array();

		static $arrUserCachedRelations = array();

		if(isset($arrUserCachedRelations[$userId][$relationTypeId][$relationStatusId]))
			return $arrUserCachedRelations[$userId][$relationTypeId][$relationStatusId];

		$arrUserCachedRelations[$userId][UserRelationEntity::RELATION_TYPE_FRIENDSHIP][UserRelationEntity::RELATION_STATUS_ACTIVE]  = array();
		$arrUserCachedRelations[$userId][UserRelationEntity::RELATION_TYPE_FRIENDSHIP][UserRelationEntity::RELATION_STATUS_PENDING] = array();

		$arrUserCachedRelations[$userId][UserRelationEntity::RELATION_TYPE_FOLLOWING][UserRelationEntity::RELATION_STATUS_ACTIVE] = array();
		$arrUserCachedRelations[$userId][UserRelationEntity::RELATION_TYPE_BLOCKING][UserRelationEntity::RELATION_STATUS_ACTIVE] = array();

		$arrUserCachedRelations[$userId][9][UserRelationEntity::RELATION_STATUS_ACTIVE] = array();

		foreach (UserRelationsRepository::getUserRelations($userId) as $userRelationEntity)
		{
			$userRelationEntity->RelationTypeId = (int)$userRelationEntity->RelationTypeId;
			$userRelationEntity->StatusId       = (int)$userRelationEntity->StatusId;

			if($userRelationEntity->RelationTypeId === UserRelationEntity::RELATION_TYPE_FRIENDSHIP)
			{
				$userIdAsKey = ($userRelationEntity->PrimaryUserId == $userId) ? $userRelationEntity->SecondaryUserId : $userRelationEntity->PrimaryUserId;

				$arrUserCachedRelations[$userId][$userRelationEntity->RelationTypeId][$userRelationEntity->StatusId][$userIdAsKey] = ($userRelationEntity->StatusId === UserRelationEntity::RELATION_STATUS_ACTIVE) ? $userRelationEntity->RelationId : $userRelationEntity;

			}
			elseif($userRelationEntity->RelationTypeId === UserRelationEntity::RELATION_TYPE_FOLLOWING)
			{
				if($userRelationEntity->PrimaryUserId == $userId)
				{
					$arrUserCachedRelations[$userId][$userRelationEntity->RelationTypeId][$userRelationEntity->StatusId][$userRelationEntity->SecondaryUserId] = $userRelationEntity->RelationId;
				}
				else
				{
					$arrUserCachedRelations[$userId][9][$userRelationEntity->StatusId][$userRelationEntity->PrimaryUserId] = $userRelationEntity->RelationId;
				}

			}
			elseif($userRelationEntity->RelationTypeId === UserRelationEntity::RELATION_TYPE_BLOCKING)
			{
				if($userRelationEntity->PrimaryUserId == $userId)
				{
					$arrUserCachedRelations[$userId][$userRelationEntity->RelationTypeId][$userRelationEntity->StatusId][$userRelationEntity->SecondaryUserId] = $userRelationEntity->RelationId;
				}

			}
		}

		return 	$arrUserCachedRelations[$userId][$relationTypeId][$relationStatusId];
	}

	public static function getUserRelationEntityById($relationId)
	{
		return UserRelationsRepository::getRelationByPK($relationId);
	}

	public static function saveUserRelation(UserRelationEntity $userRelationEntity)
	{
		if(!ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FOLLOWERS) && !ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FRIENDS))
			return 0;

		if(empty($userRelationEntity->PrimaryUserId) || empty($userRelationEntity->SecondaryUserId))
			return 0;

		if(!WpUserRepository::getUserById($userRelationEntity->PrimaryUserId) || !WpUserRepository::getUserById($userRelationEntity->SecondaryUserId))
			return 0;

		$relationId = UserRelationsRepository::saveUserRelation($userRelationEntity);

		!empty($userRelationEntity->RelationId) ?: $userRelationEntity->RelationId = $relationId;

		\do_action(UltraCommHooks::ACTION_USER_RELATION_AFTER_SAVE, $userRelationEntity);

		return $relationId;
	}


	public static function removeUserRelation($relationKey)
	{
		if(!ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FOLLOWERS) && !ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FRIENDS))
			return 0;

		$userRelationEntity = MchValidator::isInteger($relationKey) ? UserRelationsRepository::getRelationByPK($relationKey) : null;
		if(null === $userRelationEntity)
		{
			$userRelationEntity = empty($relationKey->RelationId) ? null : UserRelationsRepository::getRelationByPK($relationKey->RelationId);
		}

		if(empty($userRelationEntity->RelationId))
			return 0;

		UserRelationsRepository::deleteRelationByPK($userRelationEntity->RelationId);

		\do_action(UltraCommHooks::ACTION_USER_RELATION_AFTER_DELETE, $userRelationEntity);
	}


	/**
	 * @param $primaryUserKey
	 * @param $secondaryUserKey
	 * @param bool|false $includeUnActions
	 *
	 * @return PageActionEntity[]
	 */
	public static function getUserRelationsPossiblePageActions($primaryUserKey, $secondaryUserKey, $includeUnActions = false)
	{
		$primaryUserEntity   = UserController::getUserEntityBy($primaryUserKey);
		$secondaryUserEntity = UserController::getUserEntityBy($secondaryUserKey);

		$arrActions = array();

		if(UserRelationsController::userCanSendFriendshipRequest($primaryUserEntity, $secondaryUserEntity))
		{
			$arrActions[]   = new PageActionEntity($secondaryUserEntity->Id, PageActionEntity::TYPE_USER_ADD_FRIEND_FLAT_BUTTON);
		}

		if(UserRelationsController::userCanFollow($primaryUserEntity, $secondaryUserEntity))
		{
			$arrActions[]   = new PageActionEntity($secondaryUserEntity->Id,  PageActionEntity::TYPE_USER_FOLLOW_FLAT_BUTTON);
		}

		if($includeUnActions) //|| UserController::isCurrentUserBrowsingOwnProfile()
		{
			if (UserRelationsController::userCanUnFollow($primaryUserEntity, $secondaryUserEntity)) {
				$arrActions[] = new PageActionEntity($secondaryUserEntity->Id, PageActionEntity::TYPE_USER_UN_FOLLOW_FLAT_BUTTON);
			}

			if (UserRelationsController::userCanSendUnFriendRequest($primaryUserEntity, $secondaryUserEntity)) {
				$arrActions[] = new PageActionEntity($secondaryUserEntity->Id, PageActionEntity::TYPE_USER_UN_FRIEND_FLAT_BUTTON);
			}
		}


		//print_r($arrActions);exit;
		return $arrActions;
	}


}