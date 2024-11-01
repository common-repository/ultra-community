<?php

namespace UltraCommunity\FrontPages;

use UltraCommunity\Controllers\ActivityController;
use UltraCommunity\Controllers\FrontPageController;
use UltraCommunity\Controllers\GroupController;
use UltraCommunity\Controllers\ModulesController;
use UltraCommunity\Controllers\TemplatesController;
use UltraCommunity\Controllers\UserRelationsController;
use UltraCommunity\Controllers\UserRoleController;
use UltraCommunity\Entities\GroupEntity;
use UltraCommunity\Entities\PageActionEntity;
use UltraCommunity\MchLib\Utils\MchMinifier;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Controllers\UserController;
use UltraCommunity\Modules\Appearance\UserProfile\UserProfileAppearanceAdminModule;
use UltraCommunity\Modules\Appearance\UserProfile\UserProfileAppearancePublicModule;
use UltraCommunity\Modules\Forms\FormFields\BaseField;
use UltraCommunity\Modules\Forms\FormFields\ProfileSectionField;
use UltraCommunity\Modules\Forms\FormFields\SocialNetworkUrlField;
use UltraCommunity\UltraCommHelper;
use UltraCommunity\UltraCommHooks;

class UserSettingsPage extends BasePage //UserProfilePage
{

	CONST SETTINGS_SECTION_PROFILE_AVATAR_PICTURE = 'profile-picture';
	CONST SETTINGS_SECTION_PROFILE_COVER_PICTURE  = 'profile-cover';
	CONST SETTINGS_SECTION_PROFILE_FORM_SECTION   = 'profile-form-section';

	CONST SETTINGS_SECTION_GROUPS_CREATE_GROUP  = 'create-group';
	CONST SETTINGS_SECTION_GROUPS_EDIT_GROUP    = 'edit-group';
	CONST SETTINGS_SECTION_GROUPS_GROUP_DELETE  = 'delete-group';
	CONST SETTINGS_SECTION_GROUPS_GROUP_PICTURE = 'picture-group';
	CONST SETTINGS_SECTION_GROUPS_GROUP_COVER   = 'cover-group';
	CONST SETTINGS_SECTION_GROUPS_GROUP_MEMBERS = 'members-group';

	CONST SETTINGS_SECTION_FRIENDS_REQUESTS     = 'friend-requests';
	CONST SETTINGS_SECTION_FRIENDS_MY_FRIENDS   = 'friends';
	CONST SETTINGS_SECTION_FOLLOW_FOLLOWING     = 'following';
	CONST SETTINGS_SECTION_FOLLOW_FOLLOWERS     = 'followers';

	CONST SETTINGS_SECTION_GROUPS_MY_GROUPS     = 'my-groups';
	CONST SETTINGS_SECTION_GROUPS_JOINED_GROUPS = 'joined-groups';

	CONST SETTINGS_SECTION_ACCOUNT_CHANGE_PASSWORD   = 'change-password';
	CONST SETTINGS_SECTION_ACCOUNT_DELETE            = 'delete-account';
	CONST SETTINGS_SECTION_ACCOUNT_PRIVACY           = 'privacy';

	CONST SETTINGS_SECTION_WALL_ACTIVITY        = 'wall-activity';

	private $activeSection = null;
	private $activeSectionResourceIdentifier = null;

	private $activeSectionPageNumber = 0;

	public function __construct($pageId = null)
	{
		parent::__construct($pageId);

	}

	public static function getAllSettingsSections()
	{
		return \apply_filters(UltraCommHooks::FILTER_ALL_USER_SETTINGS_SECTIONS, array_merge(
				self::getQuickLinksSettingsSections(),
				self::getProfileSettingsSections(),
				self::getAccountSettingsSections(),
				self::getGroupsSettingsSections(),
				self::getUserConnectionsSettingsSections()
			)
		);
	}

	public static function getQuickLinksSettingsSections($justKeys = false)
	{
		$arrQuickLinksSections = array(
			self::SETTINGS_SECTION_WALL_ACTIVITY   => false,
		);

		if(GroupController::userCanCreateGroups(UserController::getProfiledUser()))
		{
			$arrQuickLinksSections[self::SETTINGS_SECTION_GROUPS_CREATE_GROUP] = false;
		}
		
		$arrQuickLinksSections = (array)apply_filters(UltraCommHooks::FILTER_USER_QUICK_LINKS_SETTINGS_SECTIONS, $arrQuickLinksSections);

		return $justKeys ? array_keys($arrQuickLinksSections) : $arrQuickLinksSections;

	}

	public static function getProfileSettingsSections($justKeys = false)
	{

		$arrProfileSections = array(

				self::SETTINGS_SECTION_PROFILE_COVER_PICTURE   => false,
				self::SETTINGS_SECTION_PROFILE_AVATAR_PICTURE  => false,
				self::SETTINGS_SECTION_PROFILE_FORM_SECTION    => true,
				//self::SETTINGS_SECTION_ACCOUNT_PRIVACY         => false,
		);

		$arrProfileSections = (array)apply_filters(UltraCommHooks::FILTER_USER_PROFILE_SETTINGS_SECTIONS, $arrProfileSections);

		return $justKeys ? array_keys($arrProfileSections) : $arrProfileSections;

	}

	public static function getAccountSettingsSections($justKeys = false)
	{
		$arrSections =   array(

			self::SETTINGS_SECTION_ACCOUNT_PRIVACY         => false,
			self::SETTINGS_SECTION_ACCOUNT_CHANGE_PASSWORD => false,
			self::SETTINGS_SECTION_ACCOUNT_DELETE          => false,

		);

		return $justKeys ? array_keys($arrSections) : $arrSections;

	}


	public static function getGroupsSettingsSections($justKeys = false)
	{

		$arrGroupsSections =   array(
				self::SETTINGS_SECTION_GROUPS_CREATE_GROUP  => false,
				self::SETTINGS_SECTION_GROUPS_MY_GROUPS     => false,
				self::SETTINGS_SECTION_GROUPS_JOINED_GROUPS => false,
				self::SETTINGS_SECTION_GROUPS_EDIT_GROUP    => true,
				self::SETTINGS_SECTION_GROUPS_GROUP_PICTURE => true,
				self::SETTINGS_SECTION_GROUPS_GROUP_COVER   => true,
				self::SETTINGS_SECTION_GROUPS_GROUP_MEMBERS => true,
				self::SETTINGS_SECTION_GROUPS_GROUP_DELETE  => true,

		);

		return $justKeys ? array_keys($arrGroupsSections) : $arrGroupsSections;
	}

	public static function getUserConnectionsSettingsSections($justKeys = false)
	{

		$arrUserRelationSections =   array(

			self::SETTINGS_SECTION_FOLLOW_FOLLOWERS   => false,
			self::SETTINGS_SECTION_FOLLOW_FOLLOWING   => false,

			self::SETTINGS_SECTION_FRIENDS_MY_FRIENDS => false,
			self::SETTINGS_SECTION_FRIENDS_REQUESTS   => false,

		);

		if(!ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FRIENDS))
		{
			unset($arrUserRelationSections[self::SETTINGS_SECTION_FRIENDS_MY_FRIENDS], $arrUserRelationSections[self::SETTINGS_SECTION_FRIENDS_REQUESTS]);
		}

		if(!ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FOLLOWERS))
		{
			unset($arrUserRelationSections[self::SETTINGS_SECTION_FOLLOW_FOLLOWING], $arrUserRelationSections[self::SETTINGS_SECTION_FOLLOW_FOLLOWERS]);
		}

		return $justKeys ? array_keys($arrUserRelationSections) : $arrUserRelationSections;

	}


	public function getSectionDescription($sectionKey)
	{
		$sectionName = null;

		switch ($sectionKey)
		{

			case self::SETTINGS_SECTION_PROFILE_AVATAR_PICTURE  : $sectionName =  esc_html__('Profile Picture', 'ultra-community');break;
			case self::SETTINGS_SECTION_PROFILE_COVER_PICTURE   : $sectionName =  esc_html__('Profile Cover', 'ultra-community');break;

			case self::SETTINGS_SECTION_ACCOUNT_CHANGE_PASSWORD : $sectionName =  esc_html__('Change Password', 'ultra-community');break;
			case self::SETTINGS_SECTION_ACCOUNT_DELETE          : $sectionName =  esc_html__('Delete Account', 'ultra-community');break;
			case self::SETTINGS_SECTION_ACCOUNT_PRIVACY         : $sectionName =  esc_html__('Account Privacy', 'ultra-community');break;

			case self::SETTINGS_SECTION_GROUPS_MY_GROUPS        : $sectionName =  esc_html__('Manage Groups', 'ultra-community');break;
			case self::SETTINGS_SECTION_GROUPS_CREATE_GROUP     : $sectionName =  esc_html__('Create Group', 'ultra-community');break;
			case self::SETTINGS_SECTION_GROUPS_JOINED_GROUPS    : $sectionName =  esc_html__('Joined Groups', 'ultra-community');break;

			case self::SETTINGS_SECTION_GROUPS_EDIT_GROUP       : $sectionName =  esc_html__('Settings', 'ultra-community');break;
			case self::SETTINGS_SECTION_GROUPS_GROUP_PICTURE    : $sectionName =  esc_html__('Picture', 'ultra-community');break;
			case self::SETTINGS_SECTION_GROUPS_GROUP_COVER      : $sectionName =  esc_html__('Cover', 'ultra-community');break;
			case self::SETTINGS_SECTION_GROUPS_GROUP_MEMBERS    : $sectionName =  esc_html__('Members', 'ultra-community');break;
			case self::SETTINGS_SECTION_GROUPS_GROUP_DELETE     : $sectionName =  esc_html__('Delete', 'ultra-community');break;


			case self::SETTINGS_SECTION_FRIENDS_REQUESTS   : $sectionName =  esc_html__('Friend Requests', 'ultra-community');break;
			case self::SETTINGS_SECTION_FRIENDS_MY_FRIENDS : $sectionName =  esc_html__('My Friends', 'ultra-community');break;
			case self::SETTINGS_SECTION_FOLLOW_FOLLOWING   : $sectionName =  esc_html__('Following', 'ultra-community');break;
			case self::SETTINGS_SECTION_FOLLOW_FOLLOWERS   : $sectionName =  esc_html__('Followers', 'ultra-community');break;

			case self::SETTINGS_SECTION_WALL_ACTIVITY      : $sectionName =  esc_html__('Wall Activity', 'ultra-community');break;


		}

		return esc_html(MchWpUtils::applyFilters(UltraCommHooks::FILTER_USER_PROFILE_SETTINGS_SECTION_NAME, $sectionName, $sectionKey));

	}

	private function getSectionIconClass($sectionKey)
	{

		$iconClass = null;
		switch($sectionKey)
		{
			case self::SETTINGS_SECTION_GROUPS_EDIT_GROUP       : $iconClass =  'fa fa-cog'; break;
			case self::SETTINGS_SECTION_GROUPS_GROUP_PICTURE    : $iconClass =  'fa fa-user-circle-o'; break;
			case self::SETTINGS_SECTION_GROUPS_GROUP_COVER      : $iconClass =  'fa fa-image'; break;
			case self::SETTINGS_SECTION_GROUPS_GROUP_MEMBERS    : $iconClass =  'fa fa-users'; break;
			case self::SETTINGS_SECTION_GROUPS_GROUP_DELETE     : $iconClass =  'fa fa-trash'; break;

			case self::SETTINGS_SECTION_GROUPS_CREATE_GROUP     : $iconClass =  'fa fa-plus'; break;
			case self::SETTINGS_SECTION_GROUPS_MY_GROUPS        : $iconClass =  'fa fa-cogs'; break;
			case self::SETTINGS_SECTION_GROUPS_JOINED_GROUPS    : $iconClass =  'fa fa-list-ul'; break;

			case self::SETTINGS_SECTION_WALL_ACTIVITY           : $iconClass =  'fa fa-bars'; break;

			case self::SETTINGS_SECTION_ACCOUNT_CHANGE_PASSWORD : $iconClass =  'fa fa-lock'; break;
			case self::SETTINGS_SECTION_ACCOUNT_DELETE          : $iconClass =  'fa fa-trash'; break;
			case self::SETTINGS_SECTION_ACCOUNT_PRIVACY         : $iconClass =  'fa fa-user-secret'; break;

			case self::SETTINGS_SECTION_FRIENDS_REQUESTS        : $iconClass =  'fa fa-user-plus'; break;
			case self::SETTINGS_SECTION_FRIENDS_MY_FRIENDS      : $iconClass =  'fa fa-handshake-o'; break;
			case self::SETTINGS_SECTION_FOLLOW_FOLLOWING        : $iconClass =  'fa fa-user-circle'; break;
			case self::SETTINGS_SECTION_FOLLOW_FOLLOWERS        : $iconClass =  'fa fa-user-circle-o'; break;

		}

		return MchWpUtils::applyFilters(UltraCommHooks::FILTER_USER_PROFILE_SETTINGS_SECTION_ICON, $iconClass, $sectionKey);

	}

	private static function isGroupSection($sectionKey)
	{
		return isset(self::getGroupsSettingsSections()[$sectionKey]);
	}

	private static function isProfileSection($sectionKey)
	{
		return isset(self::getProfileSettingsSections()[$sectionKey]);
	}

	private static function isAccountSection($sectionKey)
	{
		return isset(self::getAccountSettingsSections()[$sectionKey]);
	}

	public static function sectionRequiresIdentifierInUrl($sectionKey)
	{
		return !empty(self::getAllSettingsSections()[$sectionKey]);
	}

	public function getShortCodeContent($arrAttributes, $shortCodeText = null, $shortCodeName = null)
	{
		return null;
	}

	private function getUserConnectionsCounter($connectionSection)
	{
		switch($connectionSection)
		{
			case self::SETTINGS_SECTION_FRIENDS_REQUESTS   : return UserRelationsController::countFriendshipPendingRequests(UserController::getProfiledUser());
			case self::SETTINGS_SECTION_FRIENDS_MY_FRIENDS : return UserRelationsController::countFriends(UserController::getProfiledUser());
			case self::SETTINGS_SECTION_FOLLOW_FOLLOWING   : return UserRelationsController::countFollowing(UserController::getProfiledUser());
			case self::SETTINGS_SECTION_FOLLOW_FOLLOWERS   : return UserRelationsController::countFollowers(UserController::getProfiledUser());
		}

		return 0;

	}

	public function processRequest()
	{
		UserController::currentUserCanEditProfile() ?: FrontPageController::redirectToLogInPage();

		if($this->getActiveSection() === self::SETTINGS_SECTION_PROFILE_FORM_SECTION)
		{
			if(null === $this->getActiveSectionResourceIdentifier())
			{
				foreach(self::getUserProfileFormFields() as $profileFormField)
				{
					if( ! $profileFormField instanceof ProfileSectionField )
						continue;

					$this->setActiveSectionResourceIdentifier($profileFormField->UniqueId);
					break;
				}
			}

			if(null === $this->getActiveSectionResourceIdentifier()) // the form is empty
			{
				$this->setActiveSection(self::SETTINGS_SECTION_PROFILE_AVATAR_PICTURE);
			}

		}

		if(self::sectionRequiresIdentifierInUrl($this->getActiveSection()))
		{
			if(MchUtils::isNullOrEmpty($this->getActiveSectionResourceIdentifier())){
				MchWpUtils::redirectTo404();
			}

			if(self::isGroupSection($this->getActiveSection()))
			{
				if(!GroupController::userCanEditGroup(UserController::getLoggedInUser(), $this->getActiveSectionResourceIdentifier())){
					MchWpUtils::redirectTo404();
				}

				GroupController::setProfiledGroup($this->getActiveSectionResourceIdentifier());
			}
		}


	}


	public function setActiveSection($activeSection)
	{
		$this->activeSection = $activeSection;
	}

	public function getActiveSection()
	{
		return $this->activeSection;
	}

	public function getActiveSectionSlug()
	{
		return $this->getActiveSection();
	}

	public function getActiveSectionResourceIdentifier()
	{
		return $this->activeSectionResourceIdentifier;
	}

	public function setActiveSectionResourceIdentifier($resourceIdentifier)
	{
		$resourceIdentifier = trim($resourceIdentifier);
		$this->activeSectionResourceIdentifier = empty($resourceIdentifier) ? null : $resourceIdentifier;
	}

	public function setActiveSectionPageNumber($pageNumber)
	{
		$this->activeSectionPageNumber = (MchValidator::isInteger($pageNumber) && $pageNumber > 0)  ? (int)$pageNumber : 0;
	}


	public function getActiveSectionPageNumber()
	{
		return $this->activeSectionPageNumber;
	}

	public function isAuthenticationRequired()
	{
		return true;
	}


	protected function getPageHiddenContent()
	{
		$outputContent = '';

		return $outputContent . parent::getPageHiddenContent();
	}


	public function getPageCustomCss()
	{
		return  parent::getPageCustomCss();
	}

	public function renderMarkup()
	{

		MchWpUtils::addFilterHook(UltraCommHooks::FILTER_PAGE_HOLDER_HTML_CLASSES, function($arrSideBarClasses){
			$arrSideBarClasses[] = 'uch-user-profile-settings';
			return $arrSideBarClasses;
		});

		foreach(array(UltraCommHooks::FILTER_PAGE_HEADER_HTML_CLASSES, UltraCommHooks::FILTER_PAGE_NAVBAR_HTML_CLASSES) as $filterName) {
			MchWpUtils::addFilterHook($filterName, function ($arrElementClasses){
				$arrElementClasses[] = "uc-header-style-" . UserProfileAppearancePublicModule::getHeaderStyleId();
				return $arrElementClasses;
			});

		}

		if(in_array(UserProfileAppearancePublicModule::getHeaderStyleId(), array(3)))
		{
			MchWpUtils::addFilterHook(UltraCommHooks::FILTER_PAGE_NAVBAR_OUTPUT_HTML, function($navBarOutputHtml){
				MchWpUtils::addActionHook(UltraCommHooks::ACTION_AFTER_PAGE_NAV_BAR_RENDERED, function($pageInstance) use($navBarOutputHtml){
					echo  empty($navBarOutputHtml) ? $pageInstance->getNavBarMarkup() : $navBarOutputHtml;
				});
				return ' ';
			});
		}


		MchWpUtils::addFilterHook(UltraCommHooks::FILTER_PAGE_SIDEBAR_HTML_CLASSES, function($arrSideBarClasses){
			$arrSideBarClasses[] = 'uch-left-sidebar';
			$arrSideBarClasses[] = 'uc-settings-sidebar';
			return $arrSideBarClasses;
		});

		MchWpUtils::addFilterHook(UltraCommHooks::FILTER_PAGE_CONTENT_HTML_CLASSES, function($arrSideBarClasses){
			$arrSideBarClasses[] = 'uc-user-settings-page-content';return $arrSideBarClasses;
		});


		parent::renderMarkup();

	}

	public function getHeaderMarkup()
	{
//		return parent::getHeaderMarkup();
		$userPublicProfilePage = new UserProfilePage();
		return $userPublicProfilePage->getHeaderMarkup();

	}

	public function getNavBarMarkup() {
		return null;
	}

	public function getSideBarMarkup()
	{
		$arrSideBarNavigationSections = array();

		//$arrSideBarNavigationSections['user-profile'] = $this->getUserProfileSections(true);

		$navigationSection = new \stdClass();
		$navigationSection->Title           = null; //esc_html__('Profile Settings', 'ultra-community');
		$navigationSection->SectionKey      = null;
		$navigationSection->IsExpanded      = null;
		$navigationSection->IconOutput      = null;
		$navigationSection->NavigationItems = array();


		foreach (array('quick-links-settings', 'profile-settings', 'groups-settings', 'connections-settings', 'account-settings') as $sectionKey)
		{
			if($sectionKey === 'quick-links-settings')
			{
				$navigationSection->Title           = null;
				$navigationSection->IsExpanded      = null;
				$navigationSection->IconOutput      = null;
				$navigationSection->SectionKey      = $sectionKey;

				$navigationSection->NavigationItems = array();

				foreach (self::getQuickLinksSettingsSections(true) as $quickLinksSettingsSection)
				{
					$navigationItem = new \stdClass();

					$navigationItem->IsActive    = ($this->getActiveSection() === $quickLinksSettingsSection);
					$navigationItem->Name        = $this->getSectionDescription($quickLinksSettingsSection);
					$navigationItem->Url         = FrontPageController::getUserSettingsPageUrlBySection($quickLinksSettingsSection, UserController::getProfiledUser()->NiceName);

					$navigationItem->IconClass   = $this->getSectionIconClass($quickLinksSettingsSection);
					$navigationItem->ItemKey     = $quickLinksSettingsSection;
					$navigationItem->Counter     = 0;

					$navigationSection->NavigationItems[] = $navigationItem;
				}

				$arrSideBarNavigationSections[$sectionKey] = clone $navigationSection;

			}


			if($sectionKey === 'profile-settings')
			{
				$navigationSection->Title           = esc_html__('Profile Settings', 'ultra-community');
				$navigationSection->SectionKey      = $sectionKey;
				$navigationSection->IsExpanded      = null;
				$navigationSection->IconOutput      = '<i class="fa fa-user"></i>';
				$navigationSection->NavigationItems = $this->getUserProfileSections(true);

				$arrSideBarNavigationSections[$sectionKey] = clone $navigationSection;
			}

			if($sectionKey === 'connections-settings')
			{
				$navigationSection->Title           = esc_html__('Connections', 'ultra-community');
				$navigationSection->SectionKey      = $sectionKey;
				$navigationSection->IsExpanded      = null;
				$navigationSection->IconOutput      = '<i class="fa fa-share-alt"></i>';
				$navigationSection->NavigationItems = array();


				foreach (self::getUserConnectionsSettingsSections(true) as $connectionSettingsSection)
				{
					$navigationItem = new \stdClass();

					$navigationItem->IsActive    = ($this->getActiveSection() === $connectionSettingsSection);
					$navigationItem->Name        = $this->getSectionDescription($connectionSettingsSection);
					$navigationItem->Url         = FrontPageController::getUserSettingsPageUrlBySection($connectionSettingsSection, UserController::getProfiledUser()->NiceName);

					$navigationItem->IconClass   = $this->getSectionIconClass($connectionSettingsSection);
					$navigationItem->ItemKey     = $connectionSettingsSection;
					$navigationItem->Counter     = $this->getUserConnectionsCounter($connectionSettingsSection);

					$navigationSection->NavigationItems[] = $navigationItem;
				}

				$arrSideBarNavigationSections[$sectionKey] = clone $navigationSection;

			}


			if($sectionKey === 'account-settings')
			{
				$navigationSection->Title           = esc_html__('Account Settings', 'ultra-community');
				$navigationSection->SectionKey      = $sectionKey;
				$navigationSection->IsExpanded      = null;
				$navigationSection->IconOutput      = '<i class="fa fa-cogs"></i>';
				$navigationSection->NavigationItems = array();

				
				foreach (self::getAccountSettingsSections(true) as $accountSettingsSection)
				{

					if($accountSettingsSection === self::SETTINGS_SECTION_ACCOUNT_DELETE && !UserRoleController::currentUserCanDeleteAccount()){
						continue;
					}
					
					if($accountSettingsSection === self::SETTINGS_SECTION_ACCOUNT_CHANGE_PASSWORD && !UserRoleController::currentUserCanChangePassword()){
						continue;
					}
					
					$navigationItem = new \stdClass();

					$navigationItem->IsActive    = ($this->getActiveSection() === $accountSettingsSection);
					$navigationItem->Name        = $this->getSectionDescription($accountSettingsSection);
					$navigationItem->Url         = FrontPageController::getUserSettingsPageUrlBySection($accountSettingsSection, UserController::getProfiledUser()->NiceName);

					$navigationItem->IconClass   = $this->getSectionIconClass($accountSettingsSection);
					$navigationItem->ItemKey     = $accountSettingsSection;
					$navigationItem->Counter     = 0;

					$navigationSection->NavigationItems[] = $navigationItem;
				}

				$arrSideBarNavigationSections[$sectionKey] = clone $navigationSection;


			}


			if($sectionKey === 'groups-settings')
			{
				$navigationSection->Title           = esc_html__('Groups Settings', 'ultra-community');
				$navigationSection->SectionKey      = $sectionKey;
				$navigationSection->IsExpanded      = null;
				$navigationSection->IconOutput      = PageActionEntity::getSvgIcon('group-users-solid');
				$navigationSection->NavigationItems = array();

				foreach (self::getGroupsSettingsSections() as $groupsSettingsSection => $requiresIdentifier)
				{
					if($requiresIdentifier)
						continue;

					$navigationItem = new \stdClass();

					$navigationItem->IsActive    = ($this->getActiveSection() === $groupsSettingsSection);
					$navigationItem->Name        = $this->getSectionDescription($groupsSettingsSection);
					$navigationItem->Url         = FrontPageController::getUserSettingsPageUrlBySection($groupsSettingsSection, UserController::getProfiledUser()->NiceName);

					$navigationItem->IconClass   = $this->getSectionIconClass($groupsSettingsSection);
					$navigationItem->ItemKey     = $groupsSettingsSection;
					$navigationItem->Counter     = 0;

					//$navigationItem->IconClass = empty($navigationItem->IconClass) ? null : "fa $navigationItem->IconClass";

					if($groupsSettingsSection === self::SETTINGS_SECTION_GROUPS_MY_GROUPS)
					{
						$navigationItem->Counter = uc_count_user_created_groups(UserController::getProfiledUser());
						$navigationItem->Counter = max($navigationItem->Counter, 0);
					}

					if($groupsSettingsSection === self::SETTINGS_SECTION_GROUPS_JOINED_GROUPS)
					{
						$navigationItem->Counter = uc_count_user_all_groups(UserController::getProfiledUser()) - uc_count_user_created_groups(UserController::getProfiledUser());
						$navigationItem->Counter = max($navigationItem->Counter, 0);
					}
					
					
					if($groupsSettingsSection === self::SETTINGS_SECTION_GROUPS_CREATE_GROUP) {
						if(!GroupController::userCanCreateGroups(UserController::getProfiledUser()))
							continue;
					}
					
					$navigationSection->NavigationItems[] = $navigationItem;
				}

				$arrSideBarNavigationSections[$sectionKey] = clone $navigationSection;
			}

		}
		
		$arrNavigationSections = (array)apply_filters(UltraCommHooks::FILTER_USER_SETTINGS_SIDEBAR_NAVIGATION_SECTIONS, $arrSideBarNavigationSections, $this);

		//print_r($arrNavigationSections);exit;

		uasort($arrNavigationSections, function($section1, $section2){

			if(!empty($section1->SectionKey) && ($section1->SectionKey === 'quick-links-settings'))
				return -100;

			!empty($section1->NavigationItems) ?: $section1->NavigationItems = [];
			!empty($section2->NavigationItems) ?: $section2->NavigationItems = [];

			if(!empty(array_filter($section1->NavigationItems, function($navItem){return !empty($navItem->IsActive);})))
				return -1;

			if(!empty(array_filter($section2->NavigationItems, function($navItem){return !empty($navItem->IsActive);})))
				return 1;

			return 0;
		});

		
		foreach($arrNavigationSections as $sectionKey => $sectionObject)
		{
			//break;

			$sectionObject->NavigationItems = empty($sectionObject->NavigationItems) ? [] : (array)$sectionObject->NavigationItems;

			if($sectionKey === 'quick-links-settings')
				continue;

			$arrSectionActiveItems = array_filter($sectionObject->NavigationItems, function($navItem){return !empty($navItem->IsActive);});

			if(empty($arrSectionActiveItems) || empty($arrNavigationSections['quick-links-settings']))
				continue;

			foreach($arrNavigationSections['quick-links-settings']->NavigationItems as &$navigationItem) {
				$navigationItem = clone $navigationItem;
				$navigationItem->IsActive = false;
			}
			unset($navigationItem);

			break;
		}


		$arrSideBarArguments   = array();
		$arrSideBarArguments['arrNavigationSections'] = $arrNavigationSections;


		echo TemplatesController::getTemplateOutputContent(TemplatesController::USER_SETTINGS_SIDEBAR_TEMPLATE, array('arrSideBarArguments' => $arrSideBarArguments));


		return;



		$sideBarProfileSettingsItems = array();
		foreach (self::getProfileSettingsSections(true) as $profileSettingsSection)
		{

			switch ($profileSettingsSection)
			{
				case self::SETTINGS_SECTION_PROFILE_AVATAR_PICTURE :

					$profileSettingsItem = new \stdClass();
					$profileSettingsItem->Icon     = 'fa-camera';
					$profileSettingsItem->Title    = self::getSectionDescription($profileSettingsSection);
					$profileSettingsItem->Url      = FrontPageController::getUserSettingsPageUrlBySection($profileSettingsSection, UserController::getProfiledUser()->NiceName);
					$profileSettingsItem->IsActive = ($this->getActiveSection() === $profileSettingsSection);

					if(MchWpUtils::isAdminLoggedIn() || UserRoleController::currentUserCanChangeProfileAvatar()){
						$sideBarProfileSettingsItems[] = $profileSettingsItem;
					}


					break;


				case self::SETTINGS_SECTION_PROFILE_COVER_PICTURE :

					$profileSettingsItem = new \stdClass();
					$profileSettingsItem->Icon     = 'fa-picture-o';
					$profileSettingsItem->Title    = self::getSectionDescription($profileSettingsSection);
					$profileSettingsItem->Url      = FrontPageController::getUserSettingsPageUrlBySection($profileSettingsSection, UserController::getProfiledUser()->NiceName);
					$profileSettingsItem->IsActive = ($this->getActiveSection() === $profileSettingsSection);

					if(MchWpUtils::isAdminLoggedIn() || UserRoleController::currentUserCanChangeProfileCover()){
						$sideBarProfileSettingsItems[] = $profileSettingsItem;
					}

					break;


				case self::SETTINGS_SECTION_PROFILE_FORM_SECTION :

					$arrUserProfileFields = UserController::currentUserCanEditProfile() ? self::getUserProfileFormFields() : array();

					foreach ($arrUserProfileFields as $userProfileField)
					{
						if(! $userProfileField instanceof ProfileSectionField)
							continue;

						$profileSettingsItem = new \stdClass();

						$profileSettingsItem->Title = esc_html($userProfileField->Title);
						$profileSettingsItem->Url   = FrontPageController::getUserSettingsPageUrlBySection($profileSettingsSection, UserController::getProfiledUser()->NiceName, $userProfileField->UniqueId);
						$profileSettingsItem->Icon  = 'fa-user-circle-o';

						$profileSettingsItem->IsActive = ($this->getActiveSectionResourceIdentifier() === $userProfileField->UniqueId);


						$sideBarProfileSettingsItems[] = $profileSettingsItem;
					}

					break;
			}

		}

		$sideBarGroupsSettingsItems = array();
		if(UserRoleController::currentUserCanCreateUserGroups())
		{
			$groupsSettingsItem        = new \stdClass();
			$groupsSettingsItem->Url   = FrontPageController::getUserSettingsPageUrlBySection(self::SETTINGS_SECTION_GROUPS_CREATE_GROUP, UserController::getProfiledUser()->NiceName);;
			$groupsSettingsItem->Title = self::getSectionDescription(self::SETTINGS_SECTION_GROUPS_CREATE_GROUP);
			$groupsSettingsItem->Icon = 'fa-plus';

			$groupsSettingsItem->IsActive = $this->getActiveSection() === self::SETTINGS_SECTION_GROUPS_CREATE_GROUP;

			$sideBarGroupsSettingsItems[] = $groupsSettingsItem;

		}


		if(! MchUtils::isNullOrEmpty(GroupController::getUserCreatedGroups(UserController::getProfiledUser())))
		{
			$groupsSettingsItem        = new \stdClass();
			$groupsSettingsItem->Url   = FrontPageController::getUserSettingsPageUrlBySection(self::SETTINGS_SECTION_GROUPS_MY_GROUPS, UserController::getProfiledUser()->NiceName);;
			$groupsSettingsItem->Title = self::getSectionDescription(self::SETTINGS_SECTION_GROUPS_MY_GROUPS);
			$groupsSettingsItem->Icon = 'fa-th-list';

			$groupsSettingsItem->IsActive = $this->getActiveSection() === self::SETTINGS_SECTION_GROUPS_MY_GROUPS;

			$sideBarGroupsSettingsItems[] = $groupsSettingsItem;

		}

		if(! MchUtils::isNullOrEmpty(GroupController::getUserJoinedGroups(UserController::getProfiledUser())))
		{
			$groupsSettingsItem        = new \stdClass();
			$groupsSettingsItem->Url   = FrontPageController::getUserSettingsPageUrlBySection(self::SETTINGS_SECTION_GROUPS_JOINED_GROUPS, UserController::getProfiledUser()->NiceName);;
			$groupsSettingsItem->Title = self::getSectionDescription(self::SETTINGS_SECTION_GROUPS_JOINED_GROUPS);
			$groupsSettingsItem->Icon = 'fa-list-ul';

			$groupsSettingsItem->IsActive = $this->getActiveSection() === self::SETTINGS_SECTION_GROUPS_JOINED_GROUPS;

			$sideBarGroupsSettingsItems[] = $groupsSettingsItem;

		}

		$sideBarAccountSettingsItems = array();
		$sideBarAccountSettingsItems[self::SETTINGS_SECTION_ACCOUNT_CHANGE_PASSWORD] = 	FrontPageController::getUserSettingsPageUrlBySection(self::SETTINGS_SECTION_ACCOUNT_CHANGE_PASSWORD, UserController::getProfiledUser()->NiceName);
		$sideBarAccountSettingsItems[self::SETTINGS_SECTION_ACCOUNT_DELETE]          = 	FrontPageController::getUserSettingsPageUrlBySection(self::SETTINGS_SECTION_ACCOUNT_DELETE, UserController::getProfiledUser()->NiceName);


		if(MchWpUtils::isAdminUser(UserController::getProfiledUser()->Id)) // No Delete account OR Change Password for Administrators
		{
			unset($sideBarAccountSettingsItems[self::SETTINGS_SECTION_ACCOUNT_CHANGE_PASSWORD]);
			unset($sideBarAccountSettingsItems[self::SETTINGS_SECTION_ACCOUNT_DELETE]);
		}

		if( ! MchWpUtils::isAdminUser(UserController::getProfiledUser()->Id) )
		{
			if(!MchWpUtils::isAdminLoggedIn() && !UserRoleController::currentUserCanChangePassword()){
				unset($sideBarAccountSettingsItems[self::SETTINGS_SECTION_ACCOUNT_CHANGE_PASSWORD]);
			}

			if(!MchWpUtils::isAdminLoggedIn() && !UserRoleController::currentUserCanDeleteAccount()){
				unset($sideBarAccountSettingsItems[self::SETTINGS_SECTION_ACCOUNT_DELETE]);
			}

		}

		foreach($sideBarAccountSettingsItems as $accountSectionKey => $sectionUrl)
		{
			$accountSettingsItem = new \stdClass();
			$accountSettingsItem->Url   = $sectionUrl;

			$accountSettingsItem->IsActive = ($this->getActiveSection() === $accountSectionKey);

			switch($accountSectionKey)
			{
				case self::SETTINGS_SECTION_ACCOUNT_CHANGE_PASSWORD : $accountSettingsItem->Title = self::getSectionDescription($accountSectionKey); $accountSettingsItem->Icon = 'fa-lock'; break;
				case self::SETTINGS_SECTION_ACCOUNT_DELETE          : $accountSettingsItem->Title = self::getSectionDescription($accountSectionKey);  $accountSettingsItem->Icon = 'fa-trash'; break;
			}

			$sideBarAccountSettingsItems[$accountSectionKey] = $accountSettingsItem;
		}

		$sideBarUserRelationsItems = array();
		if(ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FRIENDS))
		{
			$sideBarUserRelationsItems[self::SETTINGS_SECTION_FRIENDS_MY_FRIENDS] = FrontPageController::getUserSettingsPageUrlBySection(self::SETTINGS_SECTION_FRIENDS_MY_FRIENDS, UserController::getProfiledUser()->NiceName);
			$sideBarUserRelationsItems[self::SETTINGS_SECTION_FRIENDS_REQUESTS]   = FrontPageController::getUserSettingsPageUrlBySection(self::SETTINGS_SECTION_FRIENDS_REQUESTS, UserController::getProfiledUser()->NiceName);
		}

		if(ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FOLLOWERS))
		{
			$sideBarUserRelationsItems[self::SETTINGS_SECTION_FOLLOW_FOLLOWERS] = FrontPageController::getUserSettingsPageUrlBySection(self::SETTINGS_SECTION_FOLLOW_FOLLOWERS, UserController::getProfiledUser()->NiceName);
			$sideBarUserRelationsItems[self::SETTINGS_SECTION_FOLLOW_FOLLOWING] = FrontPageController::getUserSettingsPageUrlBySection(self::SETTINGS_SECTION_FOLLOW_FOLLOWING, UserController::getProfiledUser()->NiceName);
		}

		foreach($sideBarUserRelationsItems as $accountSectionKey => $sectionUrl)
		{
			$relationItem = new \stdClass();
			$relationItem->Url   = $sectionUrl;
			$relationItem->Title = self::getSectionDescription($accountSectionKey);
			$relationItem->Icon = 'fa-user-circle-o';

			$relationItem->IsActive = ($this->getActiveSection() === $accountSectionKey);


			$sideBarUserRelationsItems[$accountSectionKey] = $relationItem;
		}

		$arrSideBarArguments = array(
			'sideBarProfileSettingsItems' => $sideBarProfileSettingsItems,
			'sideBarAccountSettingsItems' => $sideBarAccountSettingsItems,
			'sideBarGroupsSettingsItems'  => $sideBarGroupsSettingsItems,
			'sideBarUserRelationsItems'   => $sideBarUserRelationsItems,
		);


		$arrSideBarArguments = (array)apply_filters(UltraCommHooks::FILTER_USER_SETTINGS_SIDEBAR_NAVIGATION_SECTIONS, $arrSideBarArguments, $this);


		echo TemplatesController::getTemplateOutputContent(TemplatesController::USER_SETTINGS_SIDEBAR_TEMPLATE, $arrSideBarArguments);

	}

	public function getContentMarkup()
	{
		$templateName         = null;
		$arrTemplateArguments = array();

		switch ($this->getActiveSection())
		{
			case self::SETTINGS_SECTION_PROFILE_AVATAR_PICTURE :
				$templateName = 'user-settings-profile-picture'; break;

			case self::SETTINGS_SECTION_PROFILE_COVER_PICTURE :
				$templateName = 'user-settings-profile-cover'; break;

			case self::SETTINGS_SECTION_PROFILE_FORM_SECTION :
				$templateName = 'user-settings-profile-section'; break;

			case self::SETTINGS_SECTION_ACCOUNT_CHANGE_PASSWORD :
				$templateName = 'user-settings-account-change-password'; break;

			case self::SETTINGS_SECTION_ACCOUNT_PRIVACY :
				$templateName = 'user-settings-account-privacy'; break;

			case self::SETTINGS_SECTION_ACCOUNT_DELETE :
				$templateName = 'user-settings-account-delete'; break;

			case self::SETTINGS_SECTION_GROUPS_CREATE_GROUP :
			case self::SETTINGS_SECTION_GROUPS_EDIT_GROUP :
				$templateName = 'user-settings-groups-edit-group'; break;

			case self::SETTINGS_SECTION_GROUPS_MY_GROUPS :
				$templateName = 'user-settings-groups-my-groups'; break;

			case self::SETTINGS_SECTION_GROUPS_JOINED_GROUPS :
				$templateName = 'user-settings-groups-joined-groups'; break;

			case self::SETTINGS_SECTION_GROUPS_GROUP_COVER :
				$templateName = 'user-settings-groups-group-cover'; break;

			case self::SETTINGS_SECTION_GROUPS_GROUP_PICTURE :
				$templateName = 'user-settings-groups-group-picture'; break;

			case self::SETTINGS_SECTION_GROUPS_GROUP_MEMBERS :
				$templateName = 'user-settings-groups-group-members'; break;

			case self::SETTINGS_SECTION_GROUPS_GROUP_DELETE :
				$templateName = 'user-settings-groups-group-delete'; break;

			case self::SETTINGS_SECTION_FRIENDS_MY_FRIENDS  :
				$templateName = TemplatesController::USER_PROFILE_FRIENDS_TEMPLATE; $arrTemplateArguments['primaryUserKey'] = UserController::getProfiledUser(); $arrTemplateArguments['currentPage'] = $this->getActiveSectionPageNumber(); break;

			case self::SETTINGS_SECTION_FRIENDS_REQUESTS  :
				$templateName = TemplatesController::USER_PROFILE_FRIEND_REQUESTS_TEMPLATE; $arrTemplateArguments['primaryUserKey'] = UserController::getProfiledUser(); $arrTemplateArguments['currentPage'] = $this->getActiveSectionPageNumber(); break;

			case self::SETTINGS_SECTION_FOLLOW_FOLLOWERS    :
				$templateName = TemplatesController::USER_PROFILE_FOLLOWERS_TEMPLATE; $arrTemplateArguments['primaryUserKey'] = UserController::getProfiledUser(); $arrTemplateArguments['currentPage'] = $this->getActiveSectionPageNumber(); break;

			case self::SETTINGS_SECTION_FOLLOW_FOLLOWING    :
				$templateName = TemplatesController::USER_PROFILE_FOLLOWING_TEMPLATE; $arrTemplateArguments['primaryUserKey'] = UserController::getProfiledUser(); $arrTemplateArguments['currentPage'] = $this->getActiveSectionPageNumber(); break;

		}

		if($this->getActiveSection() === self::SETTINGS_SECTION_PROFILE_FORM_SECTION)
		{
			$profileSection = null;

			$arrProfileSections = array();

			foreach(self::getUserProfileFormFields() as $profileFormField)
			{
				if($profileFormField instanceof ProfileSectionField)
				{
					$profileSection = new \stdClass();
					$profileSection->Title    = esc_html(MchWpUtils::stripHtmlTags($profileFormField->Title));
					$profileSection->IconOutput = "<i class=\"$profileFormField->FontAwesomeIcon\"></i>";
					$profileSection->IsActive = $profileFormField->UniqueId == $this->getActiveSectionResourceIdentifier();
					$profileSection->IsSocialNetworks = false;
					$profileSection->Fields   = array();


					$arrProfileSections[$profileFormField->UniqueId] = $profileSection;
					continue;
				}

				if(null === $profileSection)
					continue;

				if(!UserController::currentUserCanViewProfileFormField($profileFormField, true))
					continue;

				$profileFormField->Value = UserController::getUserProfileFormFieldValue(UserController::getProfiledUser(), $profileFormField);

				if(!empty($profileFormField->OptionsList) && is_scalar($profileFormField->Value))
				{
					if(false !== ($savedOptionValue = array_search($profileFormField->Value, (array)$profileFormField->OptionsList))){
						$profileFormField->Value = $savedOptionValue;
					}

					unset($savedOptionValue);
				}

				!empty($profileFormField->Value) ?: $profileFormField->Value = MchWpUtils::stripSlashes(html_entity_decode(htmlspecialchars_decode($profileFormField->DefaultValue)));

				$profileSection->Fields[] = $profileFormField;

			}

			foreach ($arrProfileSections as $profileSection)
			{
				foreach ($profileSection->Fields as $profileSectionField)
				{
					$profileSection->IsSocialNetworks = $profileSectionField instanceof SocialNetworkUrlField;
				}
			}

			$arrTemplateArguments['arrProfileSections'] = $arrProfileSections;


		}


		if($this->getActiveSection() === self::SETTINGS_SECTION_GROUPS_CREATE_GROUP)
		{
			$arrTemplateArguments['editableGroupEntity'] = new GroupEntity();
		}

		if($this->getActiveSection() === self::SETTINGS_SECTION_GROUPS_EDIT_GROUP)
		{
			$arrTemplateArguments['editableGroupEntity'] = GroupController::getGroupEntityBy($this->getActiveSectionResourceIdentifier());
		}

		if($this->getActiveSection() === self::SETTINGS_SECTION_GROUPS_GROUP_DELETE)
		{
			$arrTemplateArguments['editableGroupEntity'] = GroupController::getGroupEntityBy($this->getActiveSectionResourceIdentifier());
		}

		if($this->getActiveSection() === self::SETTINGS_SECTION_GROUPS_MY_GROUPS)
		{
			$arrGroupsList = array();
			foreach (GroupController::getUserCreatedGroups(UserController::getProfiledUser(), 1, PHP_INT_MAX) as $groupEntity)
			{

				if(!GroupController::userCanEditGroup(UserController::getLoggedInUser(), $groupEntity))
					continue;

				$groupEntity->escapeFields();

				$groupEntity->EditUrl = FrontPageController::getUserSettingsPageUrlBySection(self::SETTINGS_SECTION_GROUPS_EDIT_GROUP, UserController::getProfiledUser()->NiceName, $groupEntity->Id);
				$groupEntity->Url     = UltraCommHelper::getGroupUrl($groupEntity);

				$groupEntity->PictureUrl   = UltraCommHelper::getGroupPictureUrl($groupEntity);
				$groupEntity->MembersCount = GroupController::countGroupUsers($groupEntity->Id);
				$groupEntity->CreatedDate  = mysql2date('M j, Y', $groupEntity->CreatedDate);

				$arrGroupsList[] = $groupEntity;
			}

			$arrTemplateArguments['arrUserGroups'] = $arrGroupsList;
		}

		if($this->getActiveSection() === self::SETTINGS_SECTION_GROUPS_JOINED_GROUPS)
		{
			$arrGroupsList = array();
			foreach (GroupController::getUserJoinedGroups(UserController::getProfiledUser(), 1, PHP_INT_MAX) as $groupEntity)
			{
				$groupEntity->escapeFields();

				$groupEntity->Url     = UltraCommHelper::getGroupUrl($groupEntity);

				$groupEntity->PictureUrl   = UltraCommHelper::getGroupPictureUrl($groupEntity);
				$groupEntity->MembersCount = GroupController::countGroupUsers($groupEntity->Id);
				$groupEntity->CreatedDate  = mysql2date('M j, Y', $groupEntity->CreatedDate);

				$arrGroupsList[] = $groupEntity;
			}

			$arrTemplateArguments['arrUserJoinedGroups'] = $arrGroupsList;
		}


		if($this->getActiveSection() === self::SETTINGS_SECTION_GROUPS_GROUP_MEMBERS)
		{
			$arrTemplateArguments['groupKey']    = $this->getActiveSectionResourceIdentifier();
			$arrTemplateArguments['currentPage'] =  $this->getActiveSectionPageNumber();

		}

		if($this->getActiveSection() === self::SETTINGS_SECTION_WALL_ACTIVITY)
		{

			$currentPage    = $this->getActiveSectionPageNumber();
			$recordsPerPage = 10;

			$arrActivityEntities = ActivityController::getUserProfileActivityList(UserController::getProfiledUser(), $currentPage, $recordsPerPage);

//			if(empty($arrActivityEntities))
//			{
//				$pageNoticeItem = new \stdClass();
//				$pageNoticeItem->Type = 'info';
//
//				$pageNoticeItem->Message = esc_html__('You have no activity yet!', 'ultra-community');
//
//				return TemplatesController::getTemplateOutputContent(TemplatesController::PAGE_CONTENT_NOTICE, array('pageNotice' => $pageNoticeItem));
//			}


			$arrTemplateArguments = array(
					'currentPage'           => $currentPage,
					'arrActivityEntities'   => $arrActivityEntities,
			);

			return TemplatesController::getTemplateOutputContent(TemplatesController::USER_PROFILE_ACTIVITY_TEMPLATE, $arrTemplateArguments);

		}

		echo TemplatesController::getTemplateOutputContent($templateName, $arrTemplateArguments);

	}


	public function getSubMenuTemplateArguments()
	{
		$arrNavBarArguments = array();
		$arrNavBarMenuItems = array();

		if(self::isProfileSection($this->getActiveSection()) )
		{
			$arrNavBarMenuItems = $this->getUserProfileSections(true);
		}

		if(self::isGroupSection($this->getActiveSection()) && $this->sectionRequiresIdentifierInUrl($this->getActiveSection()))
		{
			//$arrNavBarArguments['mainMenuAdditionalHtmlClasses'] = 'uc-menu-stacked';

			foreach(self::getGroupsSettingsSections(true) as $sectionKey)
			{
				if(!$this->sectionRequiresIdentifierInUrl($sectionKey))
					continue;

				$menuItem = new \stdClass();

				$menuItem->IsActive  = $sectionKey === $this->getActiveSection();
				$menuItem->Name      =  self::getSectionDescription($sectionKey);
				$menuItem->Url       =  FrontPageController::getUserSettingsPageUrlBySection($sectionKey, UserController::getProfiledUser()->NiceName, $this->getActiveSectionResourceIdentifier());
				$menuItem->IconClass =  $this->getSectionIconClass($sectionKey);

				$menuItem->IsGroupSettingsSection = true;
				$arrNavBarMenuItems[] = $menuItem;

			}

		}

		$arrNavBarArguments['profileSectionKey'] = implode('-', array_filter(array($this->getActiveSection(), $this->getActiveSectionResourceIdentifier())));
		$arrNavBarArguments['arrNavBarMenuItems']  = $arrNavBarMenuItems;

		return array_filter($arrNavBarArguments);

	}


	private static function getUserProfileFormFields()
	{
		$arrUserProfileFields = UserController::getProfiledUserProfileFormFields();

		if(empty($arrUserProfileFields))
			return array();

		$userProfileField = reset($arrUserProfileFields);
		if(! $userProfileField instanceof ProfileSectionField)
		{
			$profileSectionField           = new ProfileSectionField();
			$profileSectionField->Title    = esc_html__('Personal Information', 'ultra-community');
			$profileSectionField->UniqueId = PHP_INT_MAX;
			array_unshift($arrUserProfileFields, $profileSectionField);
		}

		foreach ($arrUserProfileFields as $userProfileField)
		{
			if($userProfileField instanceof ProfileSectionField)
			{
				!empty($userProfileField->FontAwesomeIcon) ?: $userProfileField->FontAwesomeIcon = 'fa-align-left';

				if(!MchUtils::stringStartsWith($userProfileField->FontAwesomeIcon, 'fa '))
				{
					$userProfileField->FontAwesomeIcon = sanitize_html_class($userProfileField->FontAwesomeIcon);
					$userProfileField->FontAwesomeIcon = "fa {$userProfileField->FontAwesomeIcon}";
				}
			}
		}

		return $arrUserProfileFields;
	}


	private  function getUserProfileSections($asMenuItem = false)
	{
		$arrProfileSections = array();
		foreach ($this->getUserProfileFormFields() as $userProfileField)
		{
			if(!$userProfileField instanceof ProfileSectionField)
				continue;

			$arrProfileSections[] = $userProfileField;
		}

		if(!$asMenuItem)
		{
			return $arrProfileSections;
		}

		//print_r($arrProfileSections);exit;


		foreach ($arrProfileSections as $index => $userProfileField)
		{
			$menuItem = new \stdClass();

			$menuItem->IsActive    = ($this->getActiveSectionResourceIdentifier() === $userProfileField->UniqueId);
			$menuItem->Name        = esc_html($userProfileField->Title);
			$menuItem->Url         = FrontPageController::getUserSettingsPageUrlBySection(self::SETTINGS_SECTION_PROFILE_FORM_SECTION, UserController::getProfiledUser()->NiceName, $userProfileField->UniqueId);

			$menuItem->IconClass   = $userProfileField->FontAwesomeIcon;
			$menuItem->ItemKey     = $userProfileField->UniqueId;
			$menuItem->Counter     = 0;

			$menuItem->IsProfileSettingsSection = true;


			$arrProfileSections[$index] = $menuItem;
		}

		foreach (self::getProfileSettingsSections(true) as $sectionKey)
		{
			if(in_array($sectionKey, array(self::SETTINGS_SECTION_PROFILE_FORM_SECTION)))
				continue;

			$menuItem = new \stdClass();
			$menuItem->IsActive    = ($this->getActiveSection() === $sectionKey);
			$menuItem->Name        = self::getSectionDescription($sectionKey);
			$menuItem->Url         = FrontPageController::getUserSettingsPageUrlBySection($sectionKey, UserController::getProfiledUser()->NiceName);
			$menuItem->ItemKey     = $sectionKey;
			$menuItem->Counter     = 0;

			if($sectionKey === self::SETTINGS_SECTION_PROFILE_AVATAR_PICTURE)
			{
				$menuItem->IconClass   = 'fa fa-camera';
			}

			if($sectionKey === self::SETTINGS_SECTION_PROFILE_COVER_PICTURE)
			{
				$menuItem->IconClass   = 'fa fa-picture-o';
			}

			$menuItem->IsProfileSettingsSection = true;

			$arrProfileSections[] = $menuItem;
		}

		return $arrProfileSections;

	}

}