<?php
/**
 * Copyright (c) 2017 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Tasks;


use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Entities\UserEntity;
use UltraCommunity\Entities\UserMetaEntity;
use UltraCommunity\Entities\UserRoleEntity;
use UltraCommunity\MchLib\Tasks\MchWpTask;
use UltraCommunity\MchLib\Tasks\MchWpTaskScheduler;
use UltraCommunity\MchLib\Utils\MchFileUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\Repository\WpUserRepository;
use UltraCommunity\Modules\UserRole\UserRoleAdminModule;
use UltraCommunity\Repository\UserRepository;

class UserRolesMapperTask extends MchWpTask
{
	public function __construct()
	{
		parent::__construct(1, false);
	}

	public function run()
	{
		$arrAllUsers = UserRepository::getAllUsers(1, 1);
		$totalUsers = isset($arrAllUsers[UserRepository::TOTAL_FOUND_ROWS]) ? $arrAllUsers[UserRepository::TOTAL_FOUND_ROWS] : 1;
		$totalIterations = ceil($totalUsers/1000);

		for ($i = 1; $i <= $totalIterations; ++$i)
		{
			$arrUsers = UserRepository::getAllUsers($i, 1000);
			unset($arrUsers[UserRepository::TOTAL_FOUND_ROWS]);
			foreach ($arrUsers as $userEntity)
			{
				self::synchronizeUserRoles($userEntity);
			}
		}

	}

	public static function synchronizeUserRoles($userKey)
	{

//		$userEntity = ($userKey instanceof UserEntity) ? $userKey : UserRepository::getUserEntityBy($userKey);
//		if(null === $userEntity)
//			return;
//
//		if(MchWpUtils::isAdminUser($userEntity->Id))
//			return;
//
//		unset($userKey);
//
//		static $arrWpRoleUltraCommRole = null;
//		if(null === $arrWpRoleUltraCommRole)
//		{
//			$arrWpRoleUltraCommRole = array();
//			foreach ( PostTypeController::getPublishedPosts( PostTypeController::POST_TYPE_USER_ROLE ) as $userRolePostType ) {
//				if ( null === ( $userRolePublicModuleInstance = PostTypeController::getAssociatedPublicModuleInstance( $userRolePostType ) ) ) {
//					continue;
//				}
//
//				unset( $userRolePublicModuleInstance );
//			}
//		}
//
//		static $defaultUserRolePostType = null;
//
//		(null !== $defaultUserRolePostType) ?: $defaultUserRolePostType = PostTypeController::getDefaultMemberUserRole();
//
//
//		if(!$userEntity->UserRoleEntity->hasValidUserRole())
//		{
//			UserRepository::saveUser($userEntity);
//
//			$userEntity = UserRepository::getUserEntityBy($userEntity->Id);
//		}
//
//		if( MchWpUtils::isAdminUser($userEntity->Id) || $userEntity->UserMetaEntity->UserStatus == UserMetaEntity::USER_STATUS_AWAITING_EMAIL_CONFIRMATION || $userEntity->UserMetaEntity->UserStatus == UserMetaEntity::USER_STATUS_AWAITING_REVIEW)
//			return;
//
//		$userRolePublicModuleInstance = PostTypeController::getAssociatedPublicModuleInstance($userEntity->UserRoleEntity->getCustomPostType());
//
//		$userWpRole = WpUserRepository::getUserRoleKey($userEntity->Id);
//
//		if(empty($arrMappedUserRoles) && !empty($userEntity->UserRoleEntity->MappedWpRole))
//		{
//
//			if(isset($arrWpRoleUltraCommRole[$userWpRole]))
//			{
//				$userEntity->UserRoleEntity->MappedWpRole = $userWpRole;
//				$userEntity->UserRoleEntity->setCustomPostTypeId($arrWpRoleUltraCommRole[$userWpRole]);
//			}
//			else
//			{
//				$userEntity->UserRoleEntity->MappedWpRole = null;
//				$userEntity->UserRoleEntity->setCustomPostTypeId($defaultUserRolePostType->PostId);
//			}
//
//		}
//
//		if(empty($arrMappedUserRoles) && empty($userEntity->UserRoleEntity->MappedWpRole))
//		{
//			if(isset($arrWpRoleUltraCommRole[$userWpRole]))
//			{
//				$userEntity->UserRoleEntity->MappedWpRole = $userWpRole;
//				$userEntity->UserRoleEntity->setCustomPostTypeId($arrWpRoleUltraCommRole[$userWpRole]);
//			}
//			else
//			{
//				$userEntity->UserRoleEntity->MappedWpRole = null;
//				$userEntity->UserRoleEntity->setCustomPostTypeId($defaultUserRolePostType->PostId);
//			}
//		}
//
//
//		if(!empty($arrMappedUserRoles))
//		{
//
//			if(in_array($userWpRole, $arrMappedUserRoles)
//			   && $userWpRole === $userEntity->UserRoleEntity->MappedWpRole
//			   && isset($arrWpRoleUltraCommRole[$userWpRole])
//			   && $arrWpRoleUltraCommRole[$userWpRole] == $userRolePublicModuleInstance->getCustomPostTypeId()
//			){
//
//				return;
//			};
//
//			if(isset($arrWpRoleUltraCommRole[$userWpRole]))
//			{
//				$userEntity->UserRoleEntity->setCustomPostTypeId($arrWpRoleUltraCommRole[$userWpRole]);
//				$userEntity->UserRoleEntity->MappedWpRole = $userWpRole;
//			}
//			else
//			{
//				$userEntity->UserRoleEntity->MappedWpRole = null;
//				$userEntity->UserRoleEntity->setCustomPostTypeId($defaultUserRolePostType->PostId);
//			}
//
//		}
//
//		if(empty($userEntity->UserMetaEntity->UserStatus)) {
//			$userEntity->UserMetaEntity->UserStatus = UserMetaEntity::USER_STATUS_APPROVED;
//		}
//
//
//
//		UserRepository::saveUser($userEntity);
//

	}

}