<?php

namespace UltraCommunity\FrontPages;

use UltraCommunity\Controllers\FrontPageController;
use UltraCommunity\Controllers\GroupController;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Controllers\TemplatesController;
use UltraCommunity\Controllers\UserController;
use UltraCommunity\Controllers\UserRoleController;
use UltraCommunity\Entities\GroupEntity;
use UltraCommunity\Entities\PageActionEntity;
use UltraCommunity\Entities\UserEntity;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Modules\Appearance\Directories\DirectoriesAppearancePublicModule;
use UltraCommunity\Modules\Appearance\GroupProfile\GroupProfileAppearanceAdminModule;
use UltraCommunity\Modules\Appearance\UserProfile\UserProfileAppearanceAdminModule;
use UltraCommunity\Modules\Appearance\UserProfile\UserProfileAppearancePublicModule;
use UltraCommunity\Modules\Forms\BaseForm\BaseFormAdminModule;
use UltraCommunity\Modules\Forms\FormFields\BaseField;
use UltraCommunity\Modules\GroupsDirectory\GroupsDirectoryAdminModule;
use UltraCommunity\Modules\GroupsDirectory\GroupsDirectoryPublicModule;
use UltraCommunity\Modules\MembersDirectory\MembersDirectoryAdminModule;
use UltraCommunity\Modules\MembersDirectory\MembersDirectoryPublicModule;
use UltraCommunity\Modules\UserRole\UserRolePublicModule;
use UltraCommunity\Repository\GroupRepository;
use UltraCommunity\Repository\UserRepository;
use UltraCommunity\UltraCommException;
use UltraCommunity\UltraCommHelper;
use UltraCommunity\UltraCommHooks;
use UltraCommunity\UltraCommUtils;

class GroupsDirectoryPage extends BasePage
{
	private $directoryPageNumber = null;
	private $directoryPublicModuleInstance = null;
	private $userCanSeeDirectoryListings = true;

	public function __construct($pageId = null, $directoryPageNumber = 1)
	{
		parent::__construct($pageId);

		$this->directoryPageNumber  = (int)$directoryPageNumber;
		!empty($this->directoryPageNumber) ?: $this->directoryPageNumber = 1;

	}

	public function processRequest()
	{
	}

	public function isAuthenticationRequired()
	{

	}

	public function getShortCodeContent($arrAttributes, $shortCodeText = null, $shortCodeName = null)
	{
		$customPostType = empty($arrAttributes['id']) ? null : PostTypeController::getPostTypeInstanceByPostId($arrAttributes['id']);

		(null !== $customPostType) ?: $customPostType = $this->getModuleCustomPostType();

		if(null === $customPostType)
		{ // fallback to default
			foreach(PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_GROUPS_DIRECTORY) as $directoryPostType)
			{
				if(null === ($directoryPublicModuleInstance =  PostTypeController::getAssociatedPublicModuleInstance($directoryPostType)) || !$directoryPublicModuleInstance->getOption(GroupsDirectoryAdminModule::OPTION_IS_DEFAULT_GROUPS_DIRECTORY))
					continue;

				$this->directoryPublicModuleInstance = $directoryPublicModuleInstance;
				$customPostType = $directoryPostType;
				break;
			}
		}

		if(null === $customPostType)
			return null;

		$this->setModuleCustomPostType($customPostType);

		$this->directoryPublicModuleInstance = PostTypeController::getAssociatedPublicModuleInstance($customPostType);
		$this->userCanSeeDirectoryListings   = true;

		$arrAllowedUserRoles = (array)$this->directoryPublicModuleInstance->getOption(GroupsDirectoryAdminModule::OPTION_ALLOWED_USER_ROLES);

		if(!empty($arrAllowedUserRoles) && !MchWpUtils::isAdminLoggedIn())
		{
			foreach(UserRoleController::getUserRolePostTypes(UserController::getLoggedInUser()) as $userRolePostType)
			{
				if($this->userCanSeeDirectoryListings = \in_array($userRolePostType->PostId, $arrAllowedUserRoles))
					break;
			}
		}


		if(!$this->userCanSeeDirectoryListings)
		{
			$pageNoticeItem = new \stdClass();
			$pageNoticeItem->Type = 'info';
			$pageNoticeItem->Message = esc_html__('You cannot see this group directory', 'ultra-community');

			return TemplatesController::getTemplateOutputContent(TemplatesController::PAGE_CONTENT_NOTICE, array('pageNotice' => $pageNoticeItem));
		}

		return TemplatesController::getTemplateOutputContent('directories/groups-directory.php', $this->getTemplateArguments());

	}


	private function getGroupEntitiesToList($recordsPerPage = 0, $pageNumber = 0)
	{

		if(!$this->userCanSeeDirectoryListings){
			return array();
		}

		$pageNumber       = empty($pageNumber)     ? $this->directoryPageNumber : (int)$pageNumber;
		$recordsPerPage   = empty($recordsPerPage) ? DirectoriesAppearancePublicModule::getRecordsPerPage(DirectoriesAppearancePublicModule::DIRECTORY_TYPE_GROUPS) : (int)$recordsPerPage;

		if(MchWpUtils::isAdminLoggedIn())
		{
			return GroupController::getDirectoryGroupEntities($pageNumber, $recordsPerPage);
		}

		$arrAllowedGroupTypes = array_flip((array)$this->directoryPublicModuleInstance->getOption(GroupsDirectoryAdminModule::OPTION_LIST_GROUPS_TYPE));
		
		$arrGroupEntities = array();

		if(empty($arrAllowedGroupTypes)) // default
		{
			$arrIncludeGroups = array_keys( GroupController::getUserSecretGroups(UserController::getLoggedInUserId(), 1, PHP_INT_MAX));
			$arrGroupEntities = GroupController::getDirectoryGroupEntities($pageNumber, $recordsPerPage, array(GroupEntity::GROUP_TYPE_PUBLIC, GroupEntity::GROUP_TYPE_PRIVATE), $arrIncludeGroups);
		}
		else
		{
			$arrIncludeGroups = array();
			if(!isset($arrAllowedGroupTypes[GroupEntity::GROUP_TYPE_PUBLIC]))
				$arrIncludeGroups = array_merge($arrIncludeGroups, array_keys(GroupController::getUserPublicGroups(UserController::getLoggedInUserId(), 1, PHP_INT_MAX)));

			if(!isset($arrAllowedGroupTypes[GroupEntity::GROUP_TYPE_PRIVATE]))
				$arrIncludeGroups = array_merge($arrIncludeGroups, array_keys(GroupController::getUserPrivateGroups(UserController::getLoggedInUserId(), 1, PHP_INT_MAX)));

			if(!isset($arrAllowedGroupTypes[GroupEntity::GROUP_TYPE_SECRET]))
				$arrIncludeGroups = array_merge($arrIncludeGroups, array_keys(GroupController::getUserSecretGroups(UserController::getLoggedInUserId(), 1, PHP_INT_MAX)));

			$arrGroupEntities = GroupController::getDirectoryGroupEntities($pageNumber, $recordsPerPage, array(GroupEntity::GROUP_TYPE_PUBLIC, GroupEntity::GROUP_TYPE_PRIVATE), $arrIncludeGroups);
		}


		return $arrGroupEntities;
	}

	public function getContentMarkup()
	{
		return $this->getShortCodeContent(array());
	}

	private function getTemplateArguments()
	{

		if(!$this->directoryPublicModuleInstance instanceof GroupsDirectoryPublicModule){
			throw new UltraCommException('Invalid request for Members Directory');
		}

		$pageNumber             = $this->getDirectoryPageNumber();
		$groupsPerPage          = DirectoriesAppearancePublicModule::getRecordsPerPage(DirectoriesAppearancePublicModule::DIRECTORY_TYPE_GROUPS);
		$totalGroups            = count($this->getGroupEntitiesToList(PHP_INT_MAX, 1));

		$arrArguments = array(
				'pageNumber'           => $pageNumber,
				'totalPages'           => ceil( $totalGroups / $groupsPerPage),
				'paginationType'       => DirectoriesAppearancePublicModule::getPaginationType(DirectoriesAppearancePublicModule::DIRECTORY_TYPE_GROUPS),
				'showStatsIcons'       => DirectoriesAppearancePublicModule::showStatsAsIcons(DirectoriesAppearancePublicModule::DIRECTORY_TYPE_GROUPS),
				'showCoverImage'       => DirectoriesAppearancePublicModule::showCoverImage(DirectoriesAppearancePublicModule::DIRECTORY_TYPE_GROUPS),
				'showStatsHighlighted' => DirectoriesAppearancePublicModule::showStatsHighlighted(DirectoriesAppearancePublicModule::DIRECTORY_TYPE_GROUPS),
				'showSquarePicture'    => DirectoriesAppearancePublicModule::showSquarePicture(DirectoriesAppearancePublicModule::DIRECTORY_TYPE_GROUPS),
		);


		if($arrArguments['totalPages'] < $pageNumber)
			return array();

		$arrItemsList = array();

		$postsStatsText   = esc_html__('Posts', 'ultra-community');
		$membersStatsText = esc_html__('Members', 'ultra-community');
		$groupText        = esc_html__('Group', 'ultra-community');

		$arrGroupStatsToShow = (array)$this->directoryPublicModuleInstance->getOption(GroupsDirectoryAdminModule::OPTION_GROUP_STATS_COUNTERS);

		foreach ($this->getGroupEntitiesToList() as $groupEntity)
		{
			$directoryItem = new \stdClass();
			$directoryItem->CoverUrl    = esc_url(UltraCommHelper::getGroupCoverUrl($groupEntity));
			$directoryItem->PictureUrl  = esc_url(UltraCommHelper::getGroupPictureUrl($groupEntity));

			$directoryItem->HeadLineUrl = UltraCommHelper::getGroupUrl($groupEntity);
			$directoryItem->HeadLine    = esc_html($groupEntity->Name);

			$directoryItem->Stats = array();


			foreach ($arrGroupStatsToShow as $statsKey)
			{
				$userStats = new \stdClass();
				switch ($statsKey)
				{
					case GroupsDirectoryAdminModule::GROUP_STATS_COUNTER_MEMBERS :
						$userStats->Text   = $membersStatsText;
						$userStats->Icon   = 'fa-users';
						$userStats->Url    = UltraCommHelper::getGroupUrl($groupEntity, GroupProfileAppearanceAdminModule::PROFILE_SECTION_MEMBERS);
						$userStats->Number = GroupController::getGroupStats($groupEntity, GroupProfileAppearanceAdminModule::GROUP_STATS_COUNTER_MEMBERS);

						break;

					case GroupsDirectoryAdminModule::GROUP_STATS_COUNTER_POSTS :
						$userStats->Text   = $postsStatsText;
						$userStats->Icon   = 'fa-pencil';
						$userStats->Url    = UltraCommHelper::getGroupUrl($groupEntity, GroupProfileAppearanceAdminModule::PROFILE_SECTION_ACTIVITY);
						$userStats->Number = GroupController::getGroupStats($groupEntity, GroupProfileAppearanceAdminModule::GROUP_STATS_COUNTER_POSTS);;

						break;
				}

				if(empty($userStats->Text))
					continue;

				$directoryItem->Stats[] = $userStats;
			}

			$directoryMainTagLineItem = new \stdClass();
			$directoryMainTagLineItem->Text = sprintf('%s %s', GroupEntity::getGroupTypeDescription($groupEntity->GroupTypeId), $groupText);

			$directoryMainTagLineItem->Icon = 'fa ' . $groupEntity::getGroupTypeIconClass($groupEntity->GroupTypeId);

			$directoryItem->ActionsList        = $this->getGroupActionsList($groupEntity);
			$directoryItem->MainTagLineItems   = array($directoryMainTagLineItem);
			$directoryItem->SecondTagLineItems = array();


			$arrItemsList[] = $directoryItem;

		}

		$arrArguments['directoryItems'] = $arrItemsList;


		return array('arrMembersDirectory' => $arrArguments);
	}



	private function getGroupActionsList(GroupEntity $groupEntity)
	{
		$arrActionsList = GroupController::getGroupUserPossibleActions($groupEntity, UserController::getLoggedInUser());

		return (array)apply_filters(UltraCommHooks::FILTER_GROUPS_DIRECTORY_PAGE_GROUP_ACTIONS_LIST, $arrActionsList, $groupEntity->GroupTypeId, $this);

	}

	public function getDirectoryPageNumber()
	{
		return $this->directoryPageNumber;
	}

	public function setDirectoryPageNumber($pageNumber)
	{
		$this->directoryPageNumber = (int)$pageNumber;
	}


	public function getHeaderMarkup()
	{
		return null;
	}

	public function getNavBarMarkup()
	{
		return null;
	}

	public function getSideBarMarkup()
	{
		return null;
	}

	public function getPageCustomCss()
	{
		$pageCustomCss = <<<PageCustomCss
.uch .uch-header,
.uch .uch-navbar
{
	display:none;
}
PageCustomCss;

		return $pageCustomCss . parent::getPageCustomCss() ;
	}
	
	public function getSubMenuTemplateArguments()
	{
		// TODO: Implement getSubMenuTemplateArguments() method.
	}
}