<?php

namespace UltraCommunity\Controllers;


use UltraCommunity\Entities\GroupEntity;
use UltraCommunity\Entities\GroupUserEntity;
use UltraCommunity\Entities\PageActionEntity;
use UltraCommunity\Entities\UserEntity;
use UltraCommunity\FrontPages\UserSettingsPage;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Modules\Appearance\GroupProfile\GroupProfileAppearanceAdminModule;
use UltraCommunity\Modules\GeneralSettings\Group\GroupSettingsPublicModule;
use UltraCommunity\Repository\BaseRepository;
use UltraCommunity\Repository\GroupRepository;
use UltraCommunity\UltraCommException;
use UltraCommunity\UltraCommHelper;
use UltraCommunity\UltraCommHooks;

class GroupController
{
	/**
	 * @var \UltraCommunity\Entities\GroupEntity
	 */
	private static $profiledGroup = null;

	public static function getProfiledGroup()
	{
		return self::$profiledGroup;
	}

	public static function setProfiledGroup($groupKey)
	{
		self::$profiledGroup = GroupRepository::getGroupEntityBy($groupKey);
	}

	public static function getGroupTypeId($groupKey)
	{
		if( null === ($groupEntity = self::getGroupEntityBy($groupKey)) )
			return 0;

		return (int)$groupEntity->GroupTypeId;
	}

	public static function getGroupUsers($groupKey, $pageNumber = 1, $recordsPerPage = 10, $statusId = null)
	{
		return GroupRepository::getGroupUsers(BaseRepository::getGroupIdFromKey($groupKey), $pageNumber, $recordsPerPage, $statusId);
	}

	public static function countGroupUsers($groupKey, $userStatus = GroupUserEntity::GROUP_USER_STATUS_ACTIVE)
	{
		if(null === ($groupId = GroupRepository::getGroupIdFromKey($groupKey)))
			return 0;

		return GroupRepository::getGroupNumberOfUsers($groupId, $userStatus);
	}
	
	
	/**
	 *
	 * @return array | \UltraCommunity\Entities\GroupEntity[]
	 */
	public static function getUserCreatedGroups($userKey, $pageNumber = 1, $postsPerPage = 12)
	{
		return GroupRepository::getGroupsByUserId(BaseRepository::getUserIdFromKey($userKey), $pageNumber, $postsPerPage);
	}

	/**
	 *
	 * @return array | \UltraCommunity\Entities\GroupEntity[]
	 */
	public static function getUserJoinedGroups($userKey, $pageNumber = 1, $recordsPerPage = 12)
	{
		$arrGroupUserEntities  = GroupRepository::getGroupUserEntitiesByUser($userKey, GroupUserEntity::GROUP_USER_TYPE_MEMBER, $pageNumber, $recordsPerPage, array('arrUserStatusId' => GroupUserEntity::GROUP_USER_STATUS_ACTIVE));

		$arrGroupEntities = array();

		foreach ($arrGroupUserEntities as $groupUserEntity){
			if($groupEntity = self::getGroupEntityBy($groupUserEntity->GroupId)){
				$arrGroupEntities[] = $groupEntity;
			}
		}

		return $arrGroupEntities;
	}

	/**
	 *
	 * @return array | \UltraCommunity\Entities\GroupEntity[]
	 */
	public static function getUserPrivateGroups($userKey, $pageNumber = 1, $recordsPerPage = 12)
	{
		return self::getUserAllGroups($userKey, $pageNumber, $recordsPerPage, array(GroupEntity::GROUP_TYPE_PRIVATE));
		
//		$arrArguments = array(
//			'arrGroupType'    => array(GroupEntity::GROUP_TYPE_PRIVATE),
//			'arrUserStatusId' => array(GroupUserEntity::GROUP_USER_STATUS_ACTIVE)
//		);
//
//		$arrGroupUserEntities  = GroupRepository::getGroupUserEntitiesByUser($userKey, null, $pageNumber, $recordsPerPage, $arrArguments);
//
//		$arrGroupEntities = array();
//
//		foreach ($arrGroupUserEntities as $groupUserEntity){
//			if($groupEntity = self::getGroupEntityBy($groupUserEntity->GroupId)){
//				$arrGroupEntities[$groupUserEntity->GroupId] = $groupEntity;
//			}
//		}
//
//		return $arrGroupEntities;
	}

	/**
	 *
	 * @return array | \UltraCommunity\Entities\GroupEntity[]
	 */
	public static function getUserSecretGroups($userKey, $pageNumber = 1, $recordsPerPage = 12)
	{
		return self::getUserAllGroups($userKey, $pageNumber, $recordsPerPage, array(GroupEntity::GROUP_TYPE_SECRET));
		
//		$arrArguments = array(
//				'arrGroupType'    => array(GroupEntity::GROUP_TYPE_SECRET),
//				'arrUserStatusId' => array(GroupUserEntity::GROUP_USER_STATUS_ACTIVE)
//		);
//
//		$arrGroupUserEntities  = GroupRepository::getGroupUserEntitiesByUser($userKey, null, $pageNumber, $recordsPerPage, $arrArguments);
//
//		$arrGroupEntities = array();
//
//		foreach ($arrGroupUserEntities as $groupUserEntity){
//			if($groupEntity = self::getGroupEntityBy($groupUserEntity->GroupId)){
//				$arrGroupEntities[$groupUserEntity->GroupId] = $groupEntity;
//			}
//		}
//
//		return $arrGroupEntities;
	}


	/**
	 *
	 * @return array | \UltraCommunity\Entities\GroupEntity[]
	 */
	public static function getUserPublicGroups($userKey, $pageNumber = 1, $recordsPerPage = 12)
	{
		return self::getUserAllGroups($userKey, $pageNumber, $recordsPerPage, array(GroupEntity::GROUP_TYPE_PUBLIC));
		
//		$arrArguments = array(
//				'arrGroupType'    => array(GroupEntity::GROUP_TYPE_PUBLIC),
//				'arrUserStatusId' => array(GroupUserEntity::GROUP_USER_STATUS_ACTIVE)
//		);
//
//		$arrGroupUserEntities  = GroupRepository::getGroupUserEntitiesByUser($userKey, null, $pageNumber, $recordsPerPage, $arrArguments);
//
//		$arrGroupEntities = array();
//
//		foreach ($arrGroupUserEntities as $groupUserEntity){
//			if($groupEntity = self::getGroupEntityBy($groupUserEntity->GroupId)){
//				$arrGroupEntities[$groupUserEntity->GroupId] = $groupEntity;
//			}
//		}
//
//		return $arrGroupEntities;
	}
	
	/**
	 *
	 * @return array | \UltraCommunity\Entities\GroupEntity[]
	 */
	public static function getUserAllGroups($userKey, $pageNumber = 1, $recordsPerPage = 12, $arrGroupType = array())
	{
		$arrArguments = array(
			'arrGroupType'    => $arrGroupType,
			'arrUserStatusId' => array(GroupUserEntity::GROUP_USER_STATUS_ACTIVE)
		);
		
		$arrGroupUserEntities  = GroupRepository::getGroupUserEntitiesByUser($userKey, null, $pageNumber, $recordsPerPage, $arrArguments);
	
		$arrGroupEntities = array();
		
		foreach ($arrGroupUserEntities as $groupUserEntity){
			if($groupEntity = self::getGroupEntityBy($groupUserEntity->GroupId)){
				$arrGroupEntities[$groupUserEntity->GroupId] = $groupEntity;
			}
		}
		
		return $arrGroupEntities;
		
	}
	
	/*
	 * @return int
	 */
	public static function countUserAllGroups($userKey, $arrGroupType = array())
	{
		$arrArguments = array(
			'arrGroupType'    => $arrGroupType,
			'arrUserStatusId' => array(GroupUserEntity::GROUP_USER_STATUS_ACTIVE)
		);
		
		return (int)GroupRepository::getGroupUserEntitiesByUser($userKey, null, 1, 1, $arrArguments, true);
		
	}
	
	public static function countUserPublicGroups($userKey)
	{
		return self::countUserAllGroups($userKey, array(GroupEntity::GROUP_TYPE_PUBLIC));
	}
	
	public static function countUserPrivateGroups($userKey)
	{
		return self::countUserAllGroups($userKey, array(GroupEntity::GROUP_TYPE_PRIVATE));
	}
	
	public static function countUserSecretGroups($userKey)
	{
		return self::countUserAllGroups($userKey, array(GroupEntity::GROUP_TYPE_SECRET));
	}
	
	public static function getGroupUserStatusId($groupKey, $userKey)
	{
		return GroupRepository::getGroupUserStatusId($groupKey, $userKey);
	}

	public static function getGroupUserTypeId($groupKey, $userKey)
	{
		return GroupRepository::getGroupUserTypeId($groupKey, $userKey);
	}

	public static function getGroupAdminUserId($groupKey)
	{
		if(null === ($groupEntity = self::getGroupEntityBy($groupKey))){
			return null;
		}

		return $groupEntity->AdminUserId;
	}

	public static function userCanSeeGroupProfile($userKey, $groupKey)
	{
		switch (self::getGroupTypeId($groupKey))
		{
			case  GroupEntity::GROUP_TYPE_SECRET :
				return GroupUserEntity::GROUP_USER_STATUS_ACTIVE === self::getGroupUserStatusId($groupKey, $userKey) || UserRoleController::userHasCapability($userKey, UserRoleController::USER_CAP_MANAGE_ULTRA_COMMUNITY);
			default :
				break;
		}
		
		return true;
		
	}
	
	
	public static function userCanSeeGroupProfileContent($userKey, $groupKey)
	{
		switch (self::getGroupTypeId($groupKey))
		{
			case  GroupEntity::GROUP_TYPE_SECRET :
			case  GroupEntity::GROUP_TYPE_PRIVATE :
				return GroupUserEntity::GROUP_USER_STATUS_ACTIVE === self::getGroupUserStatusId($groupKey, $userKey) || UserRoleController::userHasCapability($userKey, UserRoleController::USER_CAP_MANAGE_ULTRA_COMMUNITY);
			default :
				break;
		}

		return true;

	}

	public static function userCanSeeGroupName($userKey, $groupKey)
	{
		return self::userCanSeeGroupProfileContent($userKey, $groupKey);
	}

	public static function userCanSeeGroupDescription($userKey, $groupKey)
	{
		return self::userCanSeeGroupProfileContent($userKey, $groupKey);
	}

	public static function userCanSeeGroupMembers($userKey, $groupKey)
	{
		return self::userCanSeeGroupProfileContent($userKey, $groupKey);
		
//		switch (self::getGroupTypeId($groupKey))
//		{
//			case  GroupEntity::GROUP_TYPE_SECRET :
//				return GroupUserEntity::GROUP_USER_STATUS_ACTIVE === self::getGroupUserStatusId($groupKey, $userKey) || UserRoleController::userHasCapability($userKey, UserRoleController::USER_CAP_MANAGE_ULTRA_COMMUNITY);
//			default :
//				break;
//		}
//
//
//		return true;

	}


	public static function userCanLeaveGroup($userKey, $groupKey)
	{
		if(self::isUserGroupAdmin($userKey, $groupKey))
			return false;

		$userStatusId = self::getGroupUserStatusId($groupKey, $userKey);

		return 	$userStatusId === GroupUserEntity::GROUP_USER_STATUS_ACTIVE;

	}

	public static function userCanJoinGroup($userKey, $groupKey)
	{
		$userCanJoinGroup = false;
		
		$userStatusId = self::getGroupUserStatusId($groupKey, $userKey);

		if($userStatusId === GroupUserEntity::GROUP_USER_STATUS_INVITED) {
			$userCanJoinGroup = true;
			//return true;
			//return apply_filters(UltraCommHooks::FILTER_USER_CAN_JOIN_GROUP, true, $userKey, $groupKey);
		}
		elseif(self::getGroupTypeId($groupKey) == GroupEntity::GROUP_TYPE_SECRET){
			$userCanJoinGroup = false;
			//return false;
		}
		elseif(0 === GroupController::getGroupUserTypeId($groupKey, $userKey)){
			$userCanJoinGroup = true;
			//return true;
		}

		return apply_filters(UltraCommHooks::FILTER_USER_CAN_JOIN_GROUP, $userCanJoinGroup, $userKey, $groupKey);

	}

	public static function isUserGroupAdmin($userKey, $groupKey)
	{
		return GroupUserEntity::GROUP_USER_TYPE_ADMIN === self::getGroupUserTypeId($groupKey, $userKey);
	}

	public static function isUserGroupModerator($userKey, $groupKey)
	{
		return GroupUserEntity::GROUP_USER_TYPE_MODERATOR === self::getGroupUserTypeId($groupKey, $userKey);
	}

	public static function isUserGroupMember($userKey, $groupKey)
	{
		return GroupUserEntity::GROUP_USER_TYPE_MEMBER === self::getGroupUserTypeId($groupKey, $userKey);
	}

	public static function userJoinedGroup($userKey, $groupKey)
	{
		return GroupUserEntity::GROUP_USER_STATUS_ACTIVE === self::getGroupUserStatusId($groupKey, $userKey);
	}

	public static function userCanEditGroup($userKey, $groupKey)
	{
		return self::isUserGroupAdmin($userKey, $groupKey) || self::isUserGroupModerator($userKey, $groupKey) || UserRoleController::userHasCapability($userKey, UserRoleController::USER_CAP_MANAGE_ULTRA_COMMUNITY);
	}

	public static function userCanManageGroupUsers($userKey, $groupKey)
	{
		if(empty($userKey))
			return false;

		return self::isUserGroupAdmin($userKey, $groupKey)  || self::isUserGroupModerator($userKey, $groupKey) || UserRoleController::userHasCapability($userKey, UserRoleController::USER_CAP_MANAGE_ULTRA_COMMUNITY);
	}

	public static function userCanDeleteGroup($userKey, $groupKey)
	{
		return self::isUserGroupAdmin($userKey, $groupKey) || UserRoleController::userHasCapability($userKey, UserRoleController::USER_CAP_MANAGE_ULTRA_COMMUNITY);
	}

	public static function userCanCreateGroups($userKey)
	{
		if(empty($userKey))
			return false;
		
		$userCanCreateGroup = UserRoleController::userHasCapability($userKey, UserRoleController::USER_CAP_CREATE_USER_GROUPS);
		
		return apply_filters(UltraCommHooks::FILTER_USER_CAN_CREATE_GROUP, $userCanCreateGroup, $userKey);
		
	}

	public static function userCanControlGroupPrivacy($userKey)
	{
		if(empty($userKey) )
			return false;

		return UserRoleController::userHasCapability($userKey, UserRoleController::USER_CAP_CONTROL_GROUP_PRIVACY);

	}



	/**
	 *
	 * @return null | \UltraCommunity\Entities\GroupEntity
	 */
	public static function getGroupEntityBy($groupKey)
	{
		if(!empty($groupKey->Id) && !empty($groupKey->GroupTypeId))
			return $groupKey; // GroupEntity

		return GroupRepository::getGroupEntityBy($groupKey);
	}

	public static function getGroupUserEntity($groupKey, $userKey)
	{
		$groupUserEntity = GroupRepository::getGroupUserEntity($groupKey, $userKey);
		return empty($groupUserEntity->GroupUserId) ? null : $groupUserEntity;
	}

	public static function saveGroupUser(GroupUserEntity $groupUserEntity)
	{
		$groupEntity = self::getGroupEntityBy($groupUserEntity->GroupId);
		if(null === $groupEntity){
			throw new UltraCommException(__('Invalid group received', 'ultra-community'));
		}

		return GroupRepository::saveGroupUser($groupUserEntity);
	}


	/**
	 *
	 * @return array | \UltraCommunity\Entities\GroupEntity[]
	 */
	public static function getDirectoryGroupEntities($pageNumber, $groupsPerPage = 12, $arrGroupType = array(), $arrIncludeGroups = array(), $arrExcludeGroups = array())
	{

		$arrAdditionalArguments = array(
			'arrGroupType'     => $arrGroupType,
			'arrIncludeGroups' => $arrIncludeGroups,
			'arrExcludeGroups' => $arrExcludeGroups,
		);

		return GroupRepository::getGroupEntities($pageNumber, $groupsPerPage, $arrAdditionalArguments);
	}




	public static function saveGroup(GroupEntity $groupEntity)
	{

		$groupEntity->sanitizeFields();
		$isNewGroup = empty($groupEntity->Id);


		if(empty($groupEntity->AdminUserId) || !MchValidator::isInteger($groupEntity->AdminUserId)){
			throw new UltraCommException(__('Invalid Group Administrator UserId!', 'ultra-community'));
		}

		if(empty($groupEntity->Name)){
			throw new UltraCommException(__('Please specify the name of this group', 'ultra-community'));
		}

		if(empty($groupEntity->Description)){
			throw new UltraCommException(__('Please write a short description of this group', 'ultra-community'));
		}

		if(!in_array($groupEntity->GroupTypeId, array_keys(GroupEntity::getAllGroupTypes()))){
			$groupEntity->GroupTypeId = 0;
		}

		if(empty($groupEntity->GroupTypeId)){
			throw new UltraCommException(__('Please specify the type of this group', 'ultra-community'));
		}

		if($isNewGroup && !GroupController::userCanCreateGroups($groupEntity->AdminUserId)){
			MchWpUtils::sendAjaxErrorMessage(__('You are not allowed to create this group!', 'ultra-community'));
		}


		if(!$isNewGroup && !self::userCanEditGroup(UserController::getLoggedInUser(), $groupEntity)){
			throw new UltraCommException(__('You are not allowed to edit group settings!', 'ultra-community'));
		}


		do_action( $isNewGroup ? UltraCommHooks::ACTION_BEFORE_CREATE_USER_GROUP : UltraCommHooks::ACTION_BEFORE_SAVE_USER_GROUP, $groupEntity);

		$groupId = GroupRepository::save($groupEntity);

		!$isNewGroup ?: $groupEntity->Id = $groupId;

		do_action( $isNewGroup ? UltraCommHooks::ACTION_AFTER_USER_GROUP_CREATED : UltraCommHooks::ACTION_AFTER_USER_GROUP_SAVED, $groupEntity);


		if($isNewGroup)
		{
			$groupUserEntity = new GroupUserEntity();
			$groupUserEntity->GroupId      = $groupId;
			$groupUserEntity->UserId       = $groupEntity->AdminUserId;
			$groupUserEntity->UserStatusId = GroupUserEntity::GROUP_USER_STATUS_ACTIVE;
			$groupUserEntity->UserTypeId   = GroupUserEntity::GROUP_USER_TYPE_ADMIN;

			self::saveGroupUser($groupUserEntity);
		}

		return $groupId;
	}

	public static function deleteGroup($groupId)
	{
		do_action(UltraCommHooks::ACTION_BEFORE_DELETE_USER_GROUP, $groupId);

		if(!GroupController::userCanEditGroup(UserController::getLoggedInUser(), $groupId)){
			throw new UltraCommException(__('You are not allowed to delete this group!', 'ultra-community'));
		}

		if(MchUtils::isNullOrEmpty($groupEntity = self::getGroupEntityBy($groupId))){
			throw new UltraCommException(__('An error was encountered while trying to delete the group!', 'ultra-community'));
		}

		GroupRepository::delete($groupId);
		GroupRepository::deleteGroupUsersByGroupId($groupId);

		do_action(UltraCommHooks::ACTION_AFTER_USER_GROUP_DELETED, $groupId);
	}


	public static function initGroupHooks()
	{
		MchWpUtils::addFilterHook('pre_delete_post', function ($shouldDelete, $wpPost, $forceDelete){

			if(empty($wpPost->post_type) || $wpPost->post_type != PostTypeController::POST_TYPE_GROUP)
				return $shouldDelete;

			GroupRepository::deleteGroupUsersByGroupId($wpPost->ID);

			return $shouldDelete;

		}, 10,3);
	}

	public static function getGroupStats($groupKey, $section)
	{
		if($section ===  GroupProfileAppearanceAdminModule::GROUP_STATS_COUNTER_MEMBERS){ // already in cache
			return self::countGroupUsers($groupKey, GroupUserEntity::GROUP_USER_STATUS_ACTIVE);
		}

		$groupId = GroupRepository::getGroupIdFromKey($groupKey);
		if(empty($groupId))
			return 0;

		static $arrGroupStats = array();
		if(isset($arrGroupStats[$section][$groupId])) {
			return $arrGroupStats[$section][$groupId];
		}

		isset($arrGroupStats[$section]) ?: $arrGroupStats[$section] = array();

		switch ($section)
		{
			case GroupProfileAppearanceAdminModule::GROUP_STATS_COUNTER_POSTS :
				$arrGroupStats[$section][$groupId] = ActivityController::countActiveActivitiesByTargetId($groupId); break;
		}

		return isset($arrGroupStats[$section][$groupId]) ? $arrGroupStats[$section][$groupId] : $arrGroupStats[$section][$groupId] = 0;

	}

	public static function getGroupUserPossibleActions($groupKey, $userKey)
	{

		if(null === ($groupEntity = self::getGroupEntityBy($groupKey)))
				return array();

		$arrNavBarActions = array();

		if(self::userCanJoinGroup($userKey, $groupEntity))
		{
			$arrNavBarActions[]   = new PageActionEntity($groupEntity->Id, PageActionEntity::TYPE_GROUP_JOIN_FLAT_BUTTON);
		}
		elseif(self::userCanLeaveGroup($userKey, $groupEntity))
		{
			$arrNavBarActions[]   = new PageActionEntity($groupEntity->Id, PageActionEntity::TYPE_GROUP_LEAVE_FLAT_BUTTON);
		}

		if(self::userCanManageGroupUsers($userKey, $groupEntity))
		{
			$userEntity = UserController::getUserEntityBy($userKey);
			$arrNavBarActions[]   = new PageActionEntity($groupEntity->Id, PageActionEntity::TYPE_GROUP_MANAGE_FLAT_BUTTON, FrontPageController::getUserSettingsPageUrlBySection(UserSettingsPage::SETTINGS_SECTION_GROUPS_EDIT_GROUP, $userEntity->NiceName, $groupEntity->Id));
		}

		return $arrNavBarActions;


	}


}
