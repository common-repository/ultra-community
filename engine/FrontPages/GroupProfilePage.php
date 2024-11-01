<?php
namespace UltraCommunity\FrontPages;

use UltraCommunity\Controllers\ActivityController;
use UltraCommunity\Controllers\FrontPageController;
use UltraCommunity\Controllers\GroupController;
use UltraCommunity\Controllers\TemplatesController;
use UltraCommunity\Controllers\UserController;
use UltraCommunity\Controllers\UserRelationsController;
use UltraCommunity\Controllers\WidgetsController;
use UltraCommunity\Entities\GroupEntity;
use UltraCommunity\Entities\GroupUserEntity;
use UltraCommunity\Entities\PageActionEntity;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Modules\Appearance\General\GeneralAppearancePublicModule;
use UltraCommunity\Modules\Appearance\GroupProfile\GroupProfileAppearanceAdminModule;
use UltraCommunity\Modules\Appearance\GroupProfile\GroupProfileAppearancePublicModule;
use UltraCommunity\Modules\GeneralSettings\Group\GroupSettingsPublicModule;
use UltraCommunity\Repository\GroupRepository;
use UltraCommunity\UltraCommHelper;
use UltraCommunity\UltraCommHooks;


class GroupProfilePage extends BasePage
{
	private $activeSectionSlug       = null;
	private $activeSectionIdentifier = null;
	private $activeSectionPageNumber = 1;

//	private $headerStyleVersion      = 3;

	public function __construct($pageId = null)
	{
		parent::__construct($pageId);
	}

	public function getSubMenuTemplateArguments()
	{
		return array();
	}


	public function getShortCodeContent($arrAttributes, $shortCodeText = null, $shortCodeName = null)
	{

		if(!GroupController::userCanSeeGroupProfileContent(UserController::getLoggedInUser(), GroupController::getProfiledGroup()))
		{
			$pageNoticeItem = new \stdClass();
			$pageNoticeItem->Type = 'info';

			$pageNoticeItem->Message  = esc_html__('This is a', 'ultra-community');
			$pageNoticeItem->Message .= ' ' . GroupEntity::getGroupTypeDescription(GroupController::getProfiledGroup()->GroupTypeId);
			$pageNoticeItem->Message .= ' ' . esc_html__('group!', 'ultra-community');
			$pageNoticeItem->Message .= ' ' . esc_html__('In order to access it please send a Join Request!', 'ultra-community');

			return TemplatesController::getTemplateOutputContent(TemplatesController::PAGE_CONTENT_NOTICE, array('pageNotice' => $pageNoticeItem));

		}

		switch($this->getActiveSectionSlug())
		{
			case GroupProfileAppearanceAdminModule::PROFILE_SECTION_ACTIVITY : return $this->getGroupActivityMarkup();
			case GroupProfileAppearanceAdminModule::PROFILE_SECTION_MEMBERS  : return $this->getGroupMembersMarkup();
			case GroupProfileAppearanceAdminModule::PROFILE_SECTION_ABOUT    : return $this->getGroupAboutMarkup();

		}

		return null;
	}


	private function getGroupActivityMarkup()
	{
		$currentPage    = $this->getActiveSectionPageNumber();
		$recordsPerPage = 10;

		$arrActivityEntities = array();
		if($this->getActiveSectionIdentifier())
		{
			$arrActivityEntities[] = ActivityController::getActivityEntityByKey($this->getActiveSectionIdentifier());
		}
		else
		{
			$arrActivityEntities = ActivityController::getGroupProfileActivityList(GroupController::getProfiledGroup(), $currentPage, $recordsPerPage);
		}

		$arrTemplateArguments = array(
			'currentPage'           => $currentPage,
			'displaySingleActivity' => !!$this->getActiveSectionIdentifier(),
			'arrActivityEntities'   => $arrActivityEntities,
		);

		MchWpUtils::addFilterHook(UltraCommHooks::FILTER_ACTIVITY_SHOW_NEW_POST_FORM, function ($showPostForm) use($arrTemplateArguments){
			return !empty($arrTemplateArguments['displaySingleActivity']) ? false : $showPostForm;
		});

		return TemplatesController::getTemplateOutputContent(TemplatesController::GROUP_PROFILE_ACTIVITY_TEMPLATE, $arrTemplateArguments);

	}

	private function getGroupAboutMarkup()
	{
		$arrTemplateArguments = array(
			'groupEntity' => GroupController::getProfiledGroup()
		);

		return TemplatesController::getTemplateOutputContent(TemplatesController::GROUP_PROFILE_ABOUT_TEMPLATE, $arrTemplateArguments);

	}


	private function getGroupMembersMarkup()
	{
		$membersPerPage = 12;
		$totalMembers   = GroupController::countGroupUsers(GroupController::getProfiledGroup(), GroupUserEntity::GROUP_USER_STATUS_ACTIVE);

		$arrTemplateArguments = array('currentPage' => $this->getActiveSectionPageNumber(), 'totalPages' => ceil($totalMembers / $membersPerPage));

		$arrGroupMembers = array();

		foreach(GroupController::getGroupUsers(GroupController::getProfiledGroup(), $this->getActiveSectionPageNumber(), $membersPerPage, GroupUserEntity::GROUP_USER_STATUS_ACTIVE) as $groupUserEntity)
		{
			if(null === ($userEntity = UserController::getUserEntityBy($groupUserEntity->UserId)))
				continue;

			$userEntity->Description = UltraCommHelper::getUserShortDescription($userEntity);
			$userEntity->AvatarUrl   = UltraCommHelper::getUserAvatarUrl($userEntity);
			$userEntity->ProfileUrl  = UltraCommHelper::getUserProfileUrl($userEntity);
			$userEntity->DisplayName = UltraCommHelper::getUserDisplayName($userEntity);
			$userEntity->JoinedDate  = mysql2date('M j, Y', $groupUserEntity->JoinedDate);

			$userEntity->Actions = UserRelationsController::getUserRelationsPossiblePageActions(UserController::getLoggedInUser(), $userEntity, false);

			unset($userEntity->UserMetaEntity, $userEntity->Password, $userEntity->Email,  $userEntity->WebSiteUrl, $userEntity->IsUltraCommUser);

			$arrGroupMembers[] = $userEntity;
		}

		$arrTemplateArguments['arrGroupMembers'] = $arrGroupMembers;

		return TemplatesController::getTemplateOutputContent(TemplatesController::GROUP_PROFILE_MEMBERS_TEMPLATE, $arrTemplateArguments);
	}


	public function isAuthenticationRequired()
	{
		return GroupController::getProfiledGroup() && GroupController::getProfiledGroup()->GroupTypeId == GroupEntity::GROUP_TYPE_SECRET;
	}

	public function processRequest()
	{
		if(!GroupController::userCanSeeGroupProfile(UserController::getLoggedInUser(), GroupController::getProfiledGroup())){
			MchWpUtils::redirectToUrl(FrontPageController::getPageUrl(FrontPageController::PAGE_GROUPS_DIRECTORY));
		}

		if(empty($this->activeSectionSlug)){
			foreach(GroupProfileAppearancePublicModule::getActiveGroupProfileSections() as $sectionSlug){
				$this->activeSectionSlug = $sectionSlug; break;
			}
		}
		elseif(null === GroupProfileAppearancePublicModule::getGroupProfileSectionNameBySlug($this->activeSectionSlug)){
			FrontPageController::redirectTo404Page();
		}

		MchWpUtils::addFilterHook(UltraCommHooks::FILTER_PAGE_SIDEBAR_HTML_CLASSES, function($arrSideBarClasses){
			$arrSideBarClasses[] = ('left' === GroupProfileAppearancePublicModule::getSideBarPosition()) ? 'uch-left-sidebar' : 'uch-right-sidebar';
			return $arrSideBarClasses;
		});


		foreach(array(UltraCommHooks::FILTER_PAGE_HEADER_HTML_CLASSES, UltraCommHooks::FILTER_PAGE_NAVBAR_HTML_CLASSES) as $filterName) {
			MchWpUtils::addFilterHook($filterName, function ($arrElementClasses){
				$arrElementClasses[] = "uc-header-style-" . GroupProfileAppearancePublicModule::getHeaderStyleId();
				return $arrElementClasses;
			});

		}
		
		$activeSectionSlug = $this->activeSectionSlug;
		MchWpUtils::addFilterHook('get_pagenum_link', function ($pageLink) use($activeSectionSlug){
			return UltraCommHelper::getGroupUrl(GroupController::getProfiledGroup(), $activeSectionSlug);
		});


		
	}

	public function getHeaderMarkup()
	{
		$arrHeaderArguments = array(
			'head-line'      => GroupController::getProfiledGroup()->Name,
			'cover-url'      => UltraCommHelper::getGroupCoverUrl(GroupController::getProfiledGroup()),
			'picture-url'    => UltraCommHelper::getGroupPictureUrl(GroupController::getProfiledGroup()),
			'circled-avatar' => GroupProfileAppearancePublicModule::showCircledPictureInHeader(),
		);


		$arrHeaderArguments['stats-list'] = array();
		foreach(GroupProfileAppearancePublicModule::getGroupProfileHeaderStatsCounters() as $counterSlug){
			$arrHeaderArguments['stats-list'][] = array('content' => GroupProfileAppearancePublicModule::getGroupStatsCounterNameBySlug($counterSlug), 'number' => GroupController::getGroupStats(GroupController::getProfiledGroup()->Id, $counterSlug));
		}

		$arrHeaderArguments['meta-list'] = array();
		$arrTagLineItems = array(
			'content' => GroupEntity::getGroupTypeDescription(GroupController::getProfiledGroup()->GroupTypeId) . ' ' . esc_html__('Group', 'ultra-community'),
			'icon'    => GroupEntity::getGroupTypeIconClass(GroupController::getProfiledGroup()->GroupTypeId),
		);
		$arrHeaderArguments['meta-list'][] = $arrTagLineItems;


		return MchUtils::captureOutputBuffer(function() use ($arrHeaderArguments){
			TemplatesController::loadPageHeaderTemplate(GroupProfileAppearancePublicModule::getHeaderStyleId(), $arrHeaderArguments);
		});



	}


	public function getNavBarMarkup()
	{
		$arrNavBarArguments = array(
				'mainMenuAdditionalHtmlClasses' => GeneralAppearancePublicModule::showNavigationIconsOnTop() ?   'uc-menu-stacked' : null,
				'sectionsListsOutput' => ''
		);


		$arrNavBarMenuItems = array();

		$arrNavBarMenuItems = array();
		foreach(GroupProfileAppearancePublicModule::getActiveGroupProfileSections() as $sectionSlug )
		{

			$navBarMenuItem = new \stdClass();
			$navBarMenuItem->Url        = UltraCommHelper::getGroupUrl(GroupController::getProfiledGroup(), $sectionSlug, 1, true);
			$navBarMenuItem->Name       = GroupProfileAppearancePublicModule::getGroupProfileSectionNameBySlug($sectionSlug);
			$navBarMenuItem->IsActive   = $sectionSlug === $this->activeSectionSlug;

			$navBarMenuItem->IconClass  = GroupProfileAppearancePublicModule::getGroupProfileSectionFontAwesomeIcon($sectionSlug);
			$navBarMenuItem->IconOutput = null;

			if(strpos($navBarMenuItem->IconClass, '<svg') === 0)
			{
				$navBarMenuItem->IconOutput = $navBarMenuItem->IconClass;
				$navBarMenuItem->IconClass  = null;
			}
			else
			{
				$navBarMenuItem->IconClass  = 'fa ' . $navBarMenuItem->IconClass;
			}

			$arrNavBarMenuItems[] = $navBarMenuItem;

		}
//
//
//
//
//
//		$sectionsListsOutput = '';
//
//		foreach(GroupProfileAppearancePublicModule::getActiveGroupProfileSections() as $sectionSlug )
//		{
//			$sectionName =  GroupProfileAppearancePublicModule::getGroupProfileSectionNameBySlug($sectionSlug);
//
//			$sectionUrl  = UltraCommHelper::getGroupUrl(GroupController::getProfiledGroup(), $sectionSlug, 1, true);
//
//
//			$iconClass = GroupProfileAppearancePublicModule::getGroupProfileSectionFontAwesomeIcon($sectionSlug);
//
//			$urlClass  = 'uc-hvr-underline-from-center';
//			$listClass = '';
//			if($sectionSlug === $this->activeSectionSlug){
//				$urlClass  .= ' uc-hvr-underline-active';
//				$listClass .= ' active-section';
//			}
//
//			$sectionListOutput  = "<li class=\"uc-grid-cell uc-grid-cell--autoSize $listClass\">";
//			$sectionListOutput .= "<a class=\"$urlClass\" href=\"{$sectionUrl}\">";
//			$sectionListOutput .= "<i class=\"fa $iconClass\" aria-hidden=\"true\"></i><span>{$sectionName}</span>";
//			$sectionListOutput .= '</a>';
//			$sectionListOutput .= '</li>';
//
//			$sectionsListsOutput .= $sectionListOutput;
//
//
//		}
//
		$arrNavBarArguments['arrNavBarMenuItems']  = $arrNavBarMenuItems;

		$arrNavBarArguments['afterMainMenuOutputContent'] = $this->getAfterMainMenuOutputContent();

		return TemplatesController::getTemplateOutputContent(TemplatesController::USER_PROFILE_NAV_BAR_TEMPLATE, $arrNavBarArguments);

	}

	private function getAfterMainMenuOutputContent()
	{
		$arrNavBarActions = GroupController::getGroupUserPossibleActions(GroupController::getProfiledGroup(), UserController::getLoggedInUser());
		return TemplatesController::getTemplateOutputContent(TemplatesController::GROUP_PROFILE_NAV_BAR_USER_ACTIONS_TEMPLATE, array('arrNavBarActions' => $arrNavBarActions));

	}

	public function getSideBarMarkup()
	{
		if(!GroupController::userCanSeeGroupProfileContent(UserController::getLoggedInUser(), GroupController::getProfiledGroup()))
			return null;

		return MchUtils::captureOutputBuffer(function (){
			foreach ((array)GroupProfileAppearancePublicModule::getInstance()->getOption(GroupProfileAppearanceAdminModule::OPTION_SIDE_BAR_WIDGETS) as $widgetId){
				$widgetItemInfo = new \stdClass();
				$widgetItemInfo->GroupEntity = GroupController::getProfiledGroup();
				WidgetsController::renderWidgetContent($widgetId, $widgetItemInfo);
			}
		}, true);
	}


	public function getContentMarkup()
	{
		return $this->getShortCodeContent(array());
	}


	public function setActiveSectionSlug($activeSectionSlug)
	{
		$this->activeSectionSlug = $activeSectionSlug;
	}

	public function getActiveSectionSlug()
	{
		return $this->activeSectionSlug;
	}

	public function setActiveSectionPageNumber($pageNumber)
	{
		$this->activeSectionPageNumber = (int)$pageNumber;
		$this->activeSectionPageNumber > 1 ?: $this->activeSectionPageNumber = 1;
	}

	public function getActiveSectionPageNumber()
	{
		return $this->activeSectionPageNumber;
	}

	public function setActiveSectionIdentifier($activeSectionIdentifier)
	{
		$this->activeSectionIdentifier = $activeSectionIdentifier;
	}

	public function getActiveSectionIdentifier()
	{
		return $this->activeSectionIdentifier;
	}


	public function getPageCustomCss()
	{
		return  parent::getPageCustomCss() ;
	}

}