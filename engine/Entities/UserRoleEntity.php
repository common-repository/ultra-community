<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Entities;

use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Modules\UserRole\UserRoleAdminModule;
use UltraCommunity\PostsType\UserRolePostType;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\MchLib\WordPress\Repository\WpPostRepository;

final class UserRoleEntity implements \Serializable
{
	public $Title = null;
	public $MappedWpRole = null;
	public $UserCanEditOwnProfile = null;
	public $UserCanEditOtherProfiles = null;

	public $UserCanDeleteOwnProfile = null;
	public $UserCanDeleteOtherProfiles = null;

	public $UserCanAccessWPDashboard = null;
	public $UserCanViewAdminToolbar = null;
	public $UserCanEditDisplayName = null;

	/**
	 * @var UserRolePostType
	 */
	private $userRolePostType   = null;

	public function __construct($userRolePostTypeId = null)
	{
		$this->setCustomPostTypeId($userRolePostTypeId);
	}


	public function setCustomPostTypeId($userRolePostTypeId)
	{
		foreach (get_object_vars($this) as $objectProperty => $propValue) {
			$this->{$objectProperty} = null;
		}

		empty($userRolePostTypeId) ?: $this->initializeProperties($userRolePostTypeId);

	}

	public function getCustomPostType()
	{
		return $this->userRolePostType;
	}

	public function getCustomPostTypeId()
	{
		return null !== $this->userRolePostType ? $this->userRolePostType->PostId : 0;
	}

	private function initializeProperties($userRolePostTypeId)
	{

		if( empty($userRolePostTypeId) || !is_numeric($userRolePostTypeId)){
			$this->setCustomPostTypeId(null);
			return;
		}

		$userRolePostType = PostTypeController::getMappedPostTypeInstance(WpPostRepository::findByPostId($userRolePostTypeId));

		if( (! $userRolePostType instanceof UserRolePostType ) || empty($userRolePostType->PostId)){
			$this->setCustomPostTypeId(null);
			return;
		}

		$this->userRolePostType = $userRolePostType;
		/**
		 * @var \UltraCommunity\Modules\UserRole\UserRolePublicModule
		 */
		$publicModuleInstance = PostTypeController::getAssociatedPublicModuleInstance($userRolePostType);
		if( null === $publicModuleInstance){
			$this->setCustomPostTypeId(null);
			return;
		}

		$this->Title = $publicModuleInstance->getOption(UserRoleAdminModule::OPTION_ROLE_TITLE);

		$this->UserCanEditOwnProfile      = (bool) $publicModuleInstance->getOption(UserRoleAdminModule::OPTION_CAN_EDIT_OWN_PROFILE)     ;
		$this->UserCanEditOtherProfiles   = (bool) $publicModuleInstance->getOption(UserRoleAdminModule::OPTION_CAN_EDIT_USER_PROFILES)   ;
		//$this->UserCanDeleteOwnProfile    = (bool) $publicModuleInstance->getOption(UserRoleAdminModule::OPTION_CAN_DELETE_OWN_PROFILE)   ;
		//$this->UserCanDeleteOtherProfiles = (bool) $publicModuleInstance->getOption(UserRoleAdminModule::OPTION_CAN_DELETE_USER_PROFILES) ;
		$this->UserCanAccessWPDashboard   = (bool) $publicModuleInstance->getOption(UserRoleAdminModule::OPTION_CAN_ACCESS_WP_DASHBOARD)  ;
		$this->UserCanViewAdminToolbar    = (bool) $publicModuleInstance->getOption(UserRoleAdminModule::OPTION_CAN_VIEW_ADMIN_TOOLBAR)   ;
		//$this->UserCanEditDisplayName     = (bool) $publicModuleInstance->getOption(UserRoleAdminModule::OPTION_CAN_EDIT_DISPLAY_NAME)    ;

	}

	public function hasValidUserRole()
	{
		return (null !== $this->userRolePostType);
	}

	public function serialize()
	{
//		if(!$this->hasValidUserRole()){
//			$this->setCustomPostTypeId(PostTypeController::getDefaultMemberUserRole()->PostId);
//		}
		
		$arrSerialize = array( $this->getCustomPostTypeId());
		
		return implode('|', array_filter($arrSerialize));

		//return $this->hasValidUserRole() ? (string)$this->getCustomPostTypeId() : null;
	}

	public function unserialize( $serialized )
	{
		$arrSerializedPieces = explode('|', $serialized);
		empty($arrSerializedPieces[0]) ?: $this->setCustomPostTypeId($arrSerializedPieces[0]);
//		empty($arrSerializedPieces[1]) ?: $this->MappedWpRole = $arrSerializedPieces[1];

	}

}