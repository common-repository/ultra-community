<?php
namespace UltraCommunity\FrontPages;

use UltraCommunity\Controllers\ActivityController;
use UltraCommunity\Controllers\FrontPageController;
use UltraCommunity\Controllers\GroupController;
use UltraCommunity\Controllers\ModulesController;
use UltraCommunity\Controllers\TemplatesController;
use UltraCommunity\Controllers\UserController;
use UltraCommunity\Controllers\UserRelationsController;
use UltraCommunity\Controllers\WidgetsController;
use UltraCommunity\Entities\ActivityEntity;
use UltraCommunity\Entities\GroupEntity;
use UltraCommunity\Entities\PageActionEntity;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\Repository\WpPostRepository;
use UltraCommunity\MchLib\WordPress\Repository\WpUserRepository;
use UltraCommunity\Modules\Appearance\General\GeneralAppearancePublicModule;
use UltraCommunity\Modules\Appearance\UserProfile\UserProfileAppearanceAdminModule;
use UltraCommunity\Modules\Appearance\UserProfile\UserProfileAppearancePublicModule;
use UltraCommunity\Modules\BBPress\BBPressPublicModule;
use UltraCommunity\Modules\Forms\BaseForm\BaseFormAdminModule;
use UltraCommunity\Modules\Forms\FormFields\BaseField;
use UltraCommunity\Modules\Forms\FormFields\CountryField;
use UltraCommunity\Modules\Forms\FormFields\DividerField;
use UltraCommunity\Modules\Forms\FormFields\EmailField;
use UltraCommunity\Modules\Forms\FormFields\ProfileSectionField;
use UltraCommunity\Modules\Forms\FormFields\SocialNetworkUrlField;
use UltraCommunity\Modules\Forms\FormFields\TextAreaField;
use UltraCommunity\Modules\Forms\FormFields\UserBioField;
use UltraCommunity\Modules\Forms\FormFields\UserEmailField;
use UltraCommunity\Modules\Forms\FormFields\UserNameField;
use UltraCommunity\Modules\Forms\FormFields\UserNickNameField;
use UltraCommunity\Modules\Forms\FormFields\UserWebUrlField;
use UltraCommunity\Modules\UserFriends\UserFriendsPublicModule;
use UltraCommunity\Repository\UserRepository;
use UltraCommunity\UltraCommHelper;
use UltraCommunity\UltraCommHooks;
use UltraCommunity\UltraCommUtils;

class UserProfilePage extends BasePage
{
	private $activeSectionSlug       = null;
	private $activeSectionPageNumber = 1;
	private $activeSectionIdentifier = null;

	public function __construct($pageId = null)
	{
		parent::__construct($pageId);

	}

	public function setActiveSectionSlug($activeSectionSlug)
	{
		$this->activeSectionSlug = $activeSectionSlug;
	}

	public function getActiveSectionSlug()
	{
		return $this->activeSectionSlug;
	}


	public function setActiveSectionIdentifier($activeSectionIdentifier)
	{
		$this->activeSectionIdentifier = $activeSectionIdentifier;
	}

	public function getActiveSectionIdentifier()
	{
		return $this->activeSectionIdentifier;
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

	public function renderMarkup()
	{

		MchWpUtils::addFilterHook(UltraCommHooks::FILTER_PAGE_HOLDER_HTML_CLASSES, function($arrSideBarClasses){
			$arrSideBarClasses[] = 'uch-user-profile';
			return $arrSideBarClasses;
		});


		MchWpUtils::addFilterHook(UltraCommHooks::FILTER_PAGE_SIDEBAR_HTML_CLASSES, function($arrSideBarClasses){
			$arrSideBarClasses[] = 'left' === UserProfileAppearancePublicModule::getUserProfileSideBarPosition() ? 'uch-left-sidebar' : 'uch-right-sidebar';
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
					if(did_action(UltraCommHooks::ACTION_AFTER_PAGE_CONTENT_RENDERED))
						return;
					echo  empty($navBarOutputHtml) ? $pageInstance->getNavBarMarkup() : $navBarOutputHtml;
				});
				
				return did_action(UltraCommHooks::ACTION_AFTER_PAGE_CONTENT_RENDERED) ? null : ' ';
			});
		}



		MchWpUtils::addFilterHook('get_pagenum_link', function ($pageLink){
			return UltraCommHelper::getUserProfileUrl(UserController::getProfiledUser(), $this->activeSectionSlug, true);
		});


		parent::renderMarkup();

	}

	public function getShortCodeContent($arrAttributes, $shortCodeText = null, $shortCodeName = null)
	{

		switch($this->getActiveSectionSlug())
		{
			case UserProfileAppearanceAdminModule::PROFILE_SECTION_ACTIVITY  : return $this->getUserActivityMarkup();
			case UserProfileAppearanceAdminModule::PROFILE_SECTION_POSTS     : return $this->getUserPostsMarkup();
			case UserProfileAppearanceAdminModule::PROFILE_SECTION_COMMENTS  : return $this->getUserCommentsMarkup();
			case UserProfileAppearanceAdminModule::PROFILE_SECTION_ABOUT     : return $this->getUserAboutMarkup();
			case UserProfileAppearanceAdminModule::PROFILE_SECTION_GROUPS    : return $this->getUserGroupsMarkup();

			case UserProfileAppearanceAdminModule::PROFILE_SECTION_REVIEWS   : return TemplatesController::getTemplateOutputContent(TemplatesController::USER_PROFILE_REVIEWS_TEMPLATE,   array('primaryUserKey' => UserController::getProfiledUser(), 'currentPage' => $this->getActiveSectionPageNumber()));

			case UserProfileAppearanceAdminModule::PROFILE_SECTION_FRIENDS   : return TemplatesController::getTemplateOutputContent(TemplatesController::USER_PROFILE_FRIENDS_TEMPLATE,   array('primaryUserKey' => UserController::getProfiledUser(), 'currentPage' => $this->getActiveSectionPageNumber()));
			case UserProfileAppearanceAdminModule::PROFILE_SECTION_FOLLOWERS : return TemplatesController::getTemplateOutputContent(TemplatesController::USER_PROFILE_FOLLOWERS_TEMPLATE, array('primaryUserKey' => UserController::getProfiledUser(), 'currentPage' => $this->getActiveSectionPageNumber()));
			case UserProfileAppearanceAdminModule::PROFILE_SECTION_FOLLOWING : return TemplatesController::getTemplateOutputContent(TemplatesController::USER_PROFILE_FOLLOWING_TEMPLATE, array('primaryUserKey' => UserController::getProfiledUser(), 'currentPage' => $this->getActiveSectionPageNumber()));

		}

		return null;

	}


	private function getUserGroupsMarkup()
	{
		$recordsPerPage = 9;

		$arrListGroupType = array(GroupEntity::GROUP_TYPE_PUBLIC, GroupEntity::GROUP_TYPE_PRIVATE);
		!UserController::isCurrentUserBrowsingOwnProfile() ?: $arrListGroupType[] = GroupEntity::GROUP_TYPE_SECRET;

		$arrUserGroupEntities = GroupController::getUserAllGroups(UserController::getProfiledUser(), $this->getActiveSectionPageNumber(), $recordsPerPage, $arrListGroupType);

		foreach ($arrUserGroupEntities as $groupEntity){
			$groupEntity->Actions = GroupController::getGroupUserPossibleActions($groupEntity, UserController::getLoggedInUser());
		}

		if(empty($arrUserGroupEntities))
		{
			$pageNoticeItem = new \stdClass();
			$pageNoticeItem->Type = 'info';

			$pageNoticeItem->Message = UserController::isCurrentUserBrowsingOwnProfile() ? esc_html__('You', 'ultra-community') : UltraCommHelper::getUserDisplayName(UserController::getProfiledUser());
			$pageNoticeItem->Message .= ' ';
			$pageNoticeItem->Message .= esc_html__("didn't join any group yet!", 'ultra-community');

			return TemplatesController::getTemplateOutputContent(TemplatesController::PAGE_CONTENT_NOTICE, array('pageNotice' => $pageNoticeItem));
		}

		return  TemplatesController::getTemplateOutputContent('user-profile-page-groups', array(
			'arrUserGroups' => $arrUserGroupEntities,
			'currentPage'   => $this->getActiveSectionPageNumber(),
			'totalPages'    => ceil(GroupController::countUserAllGroups(UserController::getProfiledUser(), $arrListGroupType)) / $recordsPerPage)
		);

	}

	private function getUserActivityMarkup()
	{
		$currentPage    = $this->getActiveSectionPageNumber();
		$recordsPerPage = 10;

		$arrActivityEntities = array();
		if($this->getActiveSectionIdentifier())
		{
			$arrActivityEntities[] = ActivityController::getActivityEntityByKey($this->getActiveSectionIdentifier());
		}
		elseif(UserController::isCurrentUserBrowsingOwnProfile())
		{
			$arrActivityEntities = ActivityController::getUserProfileActivityFeed(UserController::getProfiledUser(), $currentPage, $recordsPerPage);
		}
		else
		{
			$arrActivityEntities = ActivityController::getUserProfileActivityList(UserController::getProfiledUser(), $currentPage, $recordsPerPage);
		}

		if(empty($arrActivityEntities) && !UserController::isCurrentUserBrowsingOwnProfile())
		{
			$pageNoticeItem = new \stdClass();
			$pageNoticeItem->Type = 'info';

			$pageNoticeItem->Message = UltraCommHelper::getUserDisplayName(UserController::getProfiledUser()) . ' ';
			$pageNoticeItem->Message .= esc_html__('has no activity yet!', 'ultra-community');

			return TemplatesController::getTemplateOutputContent(TemplatesController::PAGE_CONTENT_NOTICE, array('pageNotice' => $pageNoticeItem));
		}


		$arrTemplateArguments = array(
			'currentPage'           => $currentPage,
			'displaySingleActivity' => !!$this->getActiveSectionIdentifier(),
			'arrActivityEntities'   => $arrActivityEntities,
		);


		MchWpUtils::addFilterHook(UltraCommHooks::FILTER_ACTIVITY_SHOW_NEW_POST_FORM, function ($showPostForm) use($arrTemplateArguments){
			return !empty($arrTemplateArguments['displaySingleActivity']) ? false : $showPostForm;
		});

		return TemplatesController::getTemplateOutputContent(TemplatesController::USER_PROFILE_ACTIVITY_TEMPLATE, $arrTemplateArguments);

	}

	public function isAuthenticationRequired()
	{
		return false;
	}

	public function processRequest()
	{
		if(null === UserController::getProfiledUser()) {
			FrontPageController::redirectTo404Page();
		}


		if(!UserController::isUserProfileAccessible(UserController::getProfiledUser())){
			FrontPageController::redirectTo404Page();
			//FrontPageController::redirectToHomePage();
		}

		if(empty($this->activeSectionSlug)){
			foreach(UserProfileAppearancePublicModule::getActiveUserProfileSections() as $sectionSlug){
				$this->activeSectionSlug = $sectionSlug; break;
			}
		}
		elseif(null === UserProfileAppearancePublicModule::getUserProfileSectionNameBySlug($this->activeSectionSlug)){
			FrontPageController::redirectTo404Page();
		}

//		MchWpUtils::addFilterHook(UltraCommHooks::FILTER_PAGE_SIDEBAR_HTML_CLASSES, function($arrSideBarClasses){
//			$arrSideBarClasses[] = 'left' === UserProfileAppearancePublicModule::getUserProfileSideBarPosition() ? 'uch-left-sidebar' : 'uch-right-sidebar';
//			return $arrSideBarClasses;
//		});
//
//
//		MchWpUtils::addFilterHook(UltraCommHooks::FILTER_PAGE_HEADER_HTML_CLASSES, function ($arrNavBarClasses){
//			$arrNavBarClasses[] = "uc-header-style-" . UserProfileAppearancePublicModule::getHeaderStyleId();
//			return $arrNavBarClasses;
//		});
//
//		if(in_array(UserProfileAppearancePublicModule::getHeaderStyleId(), array(3)))
//		{
//			$navigationBarOutputHtml = '';
//			MchWpUtils::addFilterHook(UltraCommHooks::FILTER_PAGE_NAVBAR_OUTPUT_HTML, function($navBarOutputHtml) use(&$navigationBarOutputHtml){
//				$navigationBarOutputHtml = $navBarOutputHtml;
//				return null;
//			});
//
//			MchWpUtils::addActionHook(UltraCommHooks::ACTION_AFTER_PAGE_NAV_BAR_RENDERED, function($pageInstance) use(&$navigationBarOutputHtml){
//				echo  empty($navigationBarOutputHtml) ? $pageInstance->getNavBarMarkup() : $navigationBarOutputHtml;
//			});
//		}
//
//		$activeSectionSlug = $this->activeSectionSlug;
//		MchWpUtils::addFilterHook('get_pagenum_link', function ($pageLink) use($activeSectionSlug){
//			return UltraCommHelper::getUserProfileUrl(UserController::getProfiledUser(), $activeSectionSlug, true);
//		});

	}

	public function getHeaderMarkup()
	{

		$arrHeaderArguments = array(

				'head-line'          => UltraCommHelper::getUserDisplayName(UserController::getProfiledUser()),
				'cover-url'          => UltraCommHelper::getUserProfileCoverUrl(UserController::getProfiledUser()),
				'picture-url'        => UltraCommHelper::getUserAvatarUrl(UserController::getProfiledUser(), 150),
				'show-online-status' => UserProfileAppearancePublicModule::showUserOnlineStatusInHeader() && UserController::isUserOnline(UserController::getProfiledUser()),
				'circled-sn-icons'   => UserProfileAppearancePublicModule::showCircledSocialNetworksIconsInHeader(),
				'circled-avatar'     => UserProfileAppearancePublicModule::showCircledUserPictureInHeader(),
//				'meta-list' => array(
//
//						array('icon' => 'fa-map-marker', 'content' => 'Canada, Toronto', 'url' => ''),
//						array('icon' => 'fa-chain-broken', 'content' => 'www.ultracommunity.com', 'url' => 'https://www.ultracommunity.com')
//
//				),
//
//				'stats-list' => array(
//						array('content' => 'Posts', 'number' => 12), array('content' => 'Comments', 'number' => 84)
//				),
//
//				'sn-list' => array(
//						'facebook' => array(
//								'url' => 'http://facebook.com',
//								'colored' => true,
//						),
//						'google-plus' => array(
//							'url' => 'http://google.com',
//							'colored' => true,
//
//						),
//				),
		);


//		$arrHeaderArguments['stats-list'] = array(
//				array('content' => __('Posts', 'ultra-community'), 'number' => UserController::getUserStats(UserController::getProfiledUser()->Id, 'posts')),
//				array('content' => __('Comments', 'ultra-community'), 'number' => UserController::getUserStats(UserController::getProfiledUser()->Id, 'comments'))
//		);


		$arrHeaderArguments['stats-list'] = array();
		foreach(UserProfileAppearancePublicModule::getUserProfileHeaderStatsCounters() as $counterSlug)
		{
			$arrHeaderArguments['stats-list'][] = array('content' => UserProfileAppearancePublicModule::getUserStatsCounterNameBySlug($counterSlug), 'number' => UserController::getUserStats(UserController::getProfiledUser()->Id, $counterSlug));
		}

		$arrHeaderArguments['sn-list'] = array();
		if(UserProfileAppearancePublicModule::showUserSocialNetworksInHeader())
		{
			foreach ( UltraCommHelper::getUserSocialNetworksProfileFields( UserController::getProfiledUser() ) as $socialNetworkField ) {
				$icon = $socialNetworkField->getFontAwesomeClass( $socialNetworkField->NetworkId );
				if ( empty( $icon ) || empty( $socialNetworkField->Value ) ) {
					continue;
				}
				$arrHeaderArguments['sn-list'][ $icon ] = array( 'url' => $socialNetworkField->Value );
			}
		}

		$arrHeaderArguments['meta-list'] = array();
		foreach($this->getHeaderTagLineArguments() as $arrTagLineInfo)
		{
			if(empty($arrTagLineInfo['value']))
				continue;

			$arrTagLineItems = array();

			$arrTagLineItems['icon']    = empty($arrTagLineInfo['icon']) ? null : $arrTagLineInfo['icon'];
			$arrTagLineItems['content'] = $arrTagLineInfo['value'];

			if(MchValidator::isURL($arrTagLineItems['content'])){
				$arrTagLineItems['content'] = str_replace( array( 'http://', 'https://' ), '', strtolower($arrTagLineItems['content']));
				$arrTagLineItems['url']     = $arrTagLineInfo['value'];
			}

			$arrHeaderArguments['meta-list'][] = $arrTagLineItems;
		}

		//$headerStyleVersion = $headerStyleVersion = $this->headerStyleVersion;

		return MchUtils::captureOutputBuffer(function() use ($arrHeaderArguments){
			TemplatesController::loadPageHeaderTemplate(UserProfileAppearancePublicModule::getHeaderStyleId(), $arrHeaderArguments);
		});

	}

	public function getNavBarMarkup()
	{
		$arrNavBarArguments = array(
			'mainMenuAdditionalHtmlClasses' => GeneralAppearancePublicModule::showNavigationIconsOnTop() ?   'uc-menu-stacked' : null,
			'sectionsListsOutput' => ''
		);

		$sectionsListsOutput = '';

		$arrNavBarMenuItems = array();
		foreach(UserProfileAppearancePublicModule::getActiveUserProfileSections() as $sectionSlug )
		{

			$navBarMenuItem = new \stdClass();
			$navBarMenuItem->Url        =  UltraCommHelper::getUserProfileUrl(UserController::getProfiledUser(), $sectionSlug, true);
			$navBarMenuItem->Name       = UserProfileAppearancePublicModule::getUserProfileSectionNameBySlug($sectionSlug);
			$navBarMenuItem->IsActive   = $sectionSlug === $this->activeSectionSlug;

			$navBarMenuItem->IconClass  = UserProfileAppearancePublicModule::getUserProfileSectionFontAwesomeIcon($sectionSlug);
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

			continue;

			$sectionUrl  = UltraCommHelper::getUserProfileUrl(UserController::getProfiledUser(), $sectionSlug, true);
			$iconClass = UserProfileAppearancePublicModule::getUserProfileSectionFontAwesomeIcon($sectionSlug);


			$arrNavBarMenuItems[] = $navBarMenuItem;


			$urlClass  = 'uc-hvr-underline-from-center';
			$listClass = '';

			if($sectionSlug === $this->activeSectionSlug)
			{
				$urlClass  .= ' uc-hvr-underline-active';
				$listClass .= ' active-section';
			}

			$sectionName = UserProfileAppearancePublicModule::getUserProfileSectionNameBySlug($sectionSlug);

			if(ModulesController::isModuleRegistered(ModulesController::MODULE_BBPRESS) && $sectionSlug === UserProfileAppearanceAdminModule::PROFILE_SECTION_FORUMS && BBPressPublicModule::isBBPressUserProfileSection($this->activeSectionSlug))
			{
				$urlClass  .= ' uc-hvr-underline-active';
				$listClass .= ' active-section';
			}


			$sectionListOutput  = "<li class=\"uc-grid-cell uc-grid-cell--autoSize $listClass\">";
				$sectionListOutput .= "<a class=\"$urlClass\" href=\"{$sectionUrl}\">";

					if(strpos($iconClass, '<svg') === 0)
					{
						$sectionListOutput .= "$iconClass";
					}
					else
					{
						$sectionListOutput .= "<i class=\"fa $iconClass\" aria-hidden=\"true\"></i>";
					}

					$sectionListOutput .= "<span>{$sectionName}</span>";

				$sectionListOutput .= '</a>';
			$sectionListOutput .= '</li>';

			$sectionsListsOutput .= $sectionListOutput;


			$arrNavBarMenuItems[] = $navBarMenuItem;
		}

		//$arrNavBarArguments['sectionsListsOutput'] = $sectionsListsOutput;
		$arrNavBarArguments['arrNavBarMenuItems']  = $arrNavBarMenuItems;

		$arrNavBarArguments['afterMainMenuOutputContent'] = $this->getAfterMainMenuOutputContent();

		return TemplatesController::getTemplateOutputContent(TemplatesController::USER_PROFILE_NAV_BAR_TEMPLATE, $arrNavBarArguments);

	}


	private function getAfterMainMenuOutputContent()
	{
		$arrTemplateArguments = array();

		if(UserController::isCurrentUserBrowsingOwnProfile())
		{

			$arrNotifications = array();

			$notificationItem = new \stdClass();
			$notificationItem->Url     = FrontPageController::getUserSettingsPageUrlBySection(UserSettingsPage::SETTINGS_SECTION_FRIENDS_REQUESTS, UserController::getProfiledUser()->NiceName, false);
			$notificationItem->Number  = count(UserRelationsController::getFriendshipPendingRequests(UserController::getProfiledUser()));
			$notificationItem->Icon    = 'fa-user-plus';
			$notificationItem->Tooltip = sprintf(esc_html__('You have %d friendship requests!', 'ultra-community'), (int)$notificationItem->Number);
			$arrNotifications['friendship-requests'] = $notificationItem;

			$arrNotifications = array_map(function($notificationItem){

				$notificationItem->Url     = isset($notificationItem->Url)     ? esc_url($notificationItem->Url)      : null;
				$notificationItem->Icon    = isset($notificationItem->Icon)    ? esc_html($notificationItem->Icon)    : null;
				$notificationItem->Tooltip = isset($notificationItem->Tooltip) ? esc_html($notificationItem->Tooltip) : null;

				$notificationItem->Number = isset($notificationItem->Number) ? (int)$notificationItem->Number : 0;

				return $notificationItem;

			}, $arrNotifications);


			$arrNotifications = array_filter($arrNotifications, function($notificationItem){
				return !empty($notificationItem->Number);
			});


			$arrTemplateArguments['arrNotifications'] = $arrNotifications;


			$arrExpandableMenuItems = array();

			$expandableMenuItem = new \stdClass();
			$expandableMenuItem->Title = esc_html__('Profile Settings', 'ultra-community');
			$expandableMenuItem->Url   = FrontPageController::getUserSettingsPageUrlBySection(UserSettingsPage::SETTINGS_SECTION_PROFILE_FORM_SECTION, UserController::getProfiledUser()->NiceName);
			$expandableMenuItem->Icon  = 'fa-user';
			$arrExpandableMenuItems[]  = $expandableMenuItem;


			$expandableMenuItem = new \stdClass();
			$expandableMenuItem->Title = esc_html__('Account Settings', 'ultra-community');
			$expandableMenuItem->Url   = FrontPageController::getUserSettingsPageUrlBySection(UserSettingsPage::SETTINGS_SECTION_ACCOUNT_CHANGE_PASSWORD, UserController::getProfiledUser()->NiceName);
			$expandableMenuItem->Icon  = 'fa-cogs';
			$arrExpandableMenuItems[]  = $expandableMenuItem;


			$expandableMenuItem = new \stdClass();
			$expandableMenuItem->Title = esc_html__('Log out', 'ultra-community');
			$expandableMenuItem->Url   = FrontPageController::getLogOutPageUrl();
			$expandableMenuItem->Icon  = 'fa-power-off';
			$arrExpandableMenuItems[]  = $expandableMenuItem;


			$arrTemplateArguments['arrExpandableMenuItems'] = $arrExpandableMenuItems;

//			$privateMessagesItem = new \stdClass();
//			$privateMessagesItem->Url = FrontPageController::getUserSettingsPageUrlBySection('private-messaages', UserController::getProfiledUser()->NiceName);
//			$privateMessagesItem->Text = 15;
//			$arrTemplateArguments['friendshipRequest'] = $friendshipRequestsItem;


			return TemplatesController::getTemplateOutputContent(TemplatesController::USER_PROFILE_NAV_BAR_USER_SETTINGS_TEMPLATE, $arrTemplateArguments);
		}


		$arrNavBarActions = array();

		if(UserRelationsController::userCanSendFriendshipRequest(UserController::getLoggedInUser(), UserController::getProfiledUser()))
		{
			$arrNavBarActions[]   = new PageActionEntity(UserController::getProfiledUser()->Id, PageActionEntity::TYPE_USER_ADD_FRIEND_FLAT_BUTTON);
		}
		elseif(UserRelationsController::userCanSendUnFriendRequest(UserController::getLoggedInUser(), UserController::getProfiledUser()))
		{
			$arrNavBarActions[]   = new PageActionEntity(UserController::getProfiledUser()->Id, PageActionEntity::TYPE_USER_UN_FRIEND_FLAT_BUTTON);
		}

		if(UserRelationsController::userCanFollow(UserController::getLoggedInUser(), UserController::getProfiledUser()))
		{
			$arrNavBarActions[]   = new PageActionEntity(UserController::getProfiledUser()->Id,  PageActionEntity::TYPE_USER_FOLLOW_FLAT_BUTTON);
		}
		elseif(UserRelationsController::userCanUnFollow(UserController::getLoggedInUser(), UserController::getProfiledUser()))
		{
			$arrNavBarActions[]   = new PageActionEntity(UserController::getProfiledUser()->Id, PageActionEntity::TYPE_USER_UN_FOLLOW_FLAT_BUTTON);
		}

		$arrTemplateArguments['arrNavBarActions'] = $arrNavBarActions;

		return TemplatesController::getTemplateOutputContent(TemplatesController::USER_PROFILE_NAV_BAR_USER_ACTIONS_TEMPLATE, $arrTemplateArguments);

	}

	public function getSideBarMarkup()
	{
		if(UserController::getProfiledUser()->getPrivacyEntity()->userHasProfileVisibilityNotice())
		{
			return null;
		}

		return MchUtils::captureOutputBuffer(function (){

			if(UserController::isCurrentUserBrowsingOwnProfile() || MchWpUtils::isAdminLoggedIn()){
				WidgetsController::renderWidgetContent(WidgetsController::WIDGET_USER_MAIN_NAVIGATION);
			}

			foreach ((array)UserProfileAppearancePublicModule::getInstance()->getOption(UserProfileAppearanceAdminModule::OPTION_SIDE_BAR_WIDGETS) as $widgetId){
				WidgetsController::renderWidgetContent($widgetId);
			}
		});
	}

	public function getContentMarkup()
	{

		$pagePrivacyNotice = UserController::getProfiledUser()->getPrivacyEntity()->getProfilePrivacyNotice();
		if(!empty($pagePrivacyNotice))
		{
			return $pagePrivacyNotice;
		}

		return $this->getShortCodeContent(array());

	}


	private function getUserAboutMarkup()
	{
		$arrUserProfileFields = UserController::getProfiledUserProfileFormFields();

		$userProfileField = reset($arrUserProfileFields);
		if(! $userProfileField instanceof BaseField)
			return null;

		if(! $userProfileField instanceof ProfileSectionField)
		{
			$profileSectionField           = new ProfileSectionField();
			$profileSectionField->Title    = __('My Profile', 'ultra-community');
			$profileSectionField->UniqueId = PHP_INT_MAX;
			array_unshift($arrUserProfileFields, $profileSectionField);
		}


		$arrUserProfileFields = array_values($arrUserProfileFields);


		$arrSections = array();
		$profileSection = null;
		for ($i = 0, $arrLen = count($arrUserProfileFields); $i < $arrLen; ++$i)
		{
			$userProfileField = $arrUserProfileFields[$i];
			if($userProfileField instanceof ProfileSectionField)
			{
				if(null !== $profileSection && !empty($profileSection->Fields)){
					$arrSections[] = $profileSection;
				}

				$profileSection = new \stdClass();
				$profileSection->Title = esc_html(MchWpUtils::stripHtmlTags($userProfileField->Title));
				$profileSection->FontAwesomeIcon = empty($userProfileField->FontAwesomeIcon) ? null : "fa {$userProfileField->FontAwesomeIcon}";
				
				continue;
			}

			if(null === $profileSection)
				continue;

			$userProfileField->Label = esc_html($userProfileField->Label);

			$userProfileField->Value = UserController::getUserProfileFormFieldValue(UserController::getProfiledUser(), $userProfileField);

			!empty($userProfileField->Value) ?: $userProfileField->Value = MchWpUtils::stripSlashes(html_entity_decode(htmlspecialchars_decode($userProfileField->DefaultValue)));

			if(is_scalar($userProfileField->Value))
			{
				$userProfileField->Value = wptexturize($userProfileField->Value);
				$userProfileField->Value = convert_smilies($userProfileField->Value);
				$userProfileField->Value = convert_chars($userProfileField->Value);

				if($userProfileField->IsHtmlAllowed || ($userProfileField instanceof TextAreaField) || ($userProfileField instanceof UserBioField))
				{
					$userProfileField->Value = wpautop($userProfileField->Value);
				}
				else
				{
					$userProfileField->Value = esc_html($userProfileField->Value);
				}

			}

			!is_scalar($userProfileField->Value) ?:  $userProfileField->Value = make_clickable($userProfileField->Value);


			if(is_array($userProfileField->Value))
			{
				$arrFieldValues = array();
				$arrOptions = (array)$userProfileField->getFieldOptionsList();

				foreach ($userProfileField->Value  as $optionKey)
				{
					!isset($arrOptions[$optionKey]) ?: $arrFieldValues[] = esc_html($arrOptions[$optionKey]);
				}

				empty($arrFieldValues) ?: $userProfileField->Value = $arrFieldValues;
			}

			!empty($profileSection->Fields) ?: $profileSection->Fields =  array();

			if(UserController::currentUserCanViewProfileFormField($userProfileField))
			{
				$userProfileField->FontAwesomeIcon = null;
				
				if(!empty($userProfileField->Value) || $userProfileField instanceof DividerField)
				{
					$profileSection->Fields[] = $userProfileField;
				}

			}


			if($i + 1 === $arrLen && !empty($profileSection->Fields)){
				$arrSections[] = $profileSection;
				$profileSection = null;
			}
		}


		return  empty($arrSections) ? null : TemplatesController::getTemplateOutputContent('user-profile-page-about', array('arrSections' => $arrSections));

	}

	private function getUserCommentsMarkup()
	{
		add_filter('comment_excerpt_length', function(){return 40;}, PHP_INT_MAX);
		$commentsPerPage = 7;

		$userProfileUrl  = UltraCommHelper::getUserProfileUrl(UserController::getProfiledUser());
		$userDisplayName = UltraCommHelper::getUserDisplayName(UserController::getProfiledUser());
		$userAvatarUrl   = UltraCommHelper::getUserAvatarUrl(UserController::getProfiledUser(), 50);

		$arrUserComments = array();
		foreach (WpUserRepository::getUserApprovedComments(UserController::getProfiledUser()->Id, $this->getActiveSectionPageNumber(), $commentsPerPage) as $userApprovedComment)
		{
			$userComment = new \stdClass();
			$userComment->Id      = $userApprovedComment->comment_ID;
			$userComment->Url     = esc_url(get_comment_link($userComment->Id));
			$userComment->Date    = get_comment_date('F j, Y', $userComment->Id);
			$userComment->Excerpt = esc_html(get_comment_excerpt($userComment->Id));

			$userComment->UserAvatarUrl   = $userAvatarUrl;
			$userComment->UserProfileUrl  = $userProfileUrl;
			$userComment->UserDisplayName = $userDisplayName;

			$arrUserComments[] = $userComment;
		}


		if(empty($arrUserComments))
		{
			$pageNoticeItem = new \stdClass();
			$pageNoticeItem->Type = 'info';

			$pageNoticeItem->Message = UltraCommHelper::getUserDisplayName(UserController::getProfiledUser()) . ' ';
			$pageNoticeItem->Message .= esc_html__('has no comments yet!', 'ultra-community');

			return TemplatesController::getTemplateOutputContent(TemplatesController::PAGE_CONTENT_NOTICE, array('pageNotice' => $pageNoticeItem));
		}


		return  TemplatesController::getTemplateOutputContent('user-profile-page-comments', array(
			'arrUserComments' => $arrUserComments,
			'currentPage'     => $this->getActiveSectionPageNumber(),
			'totalPages'      => ceil(UserController::getUserStats(UserController::getProfiledUser()->Id, 'comments') / $commentsPerPage)
		));
	}

	private function getUserPostsMarkup()
	{
		$postsPerPage    = 6;

		add_filter('get_the_excerpt', function($postExcerpt){
			return empty($postExcerpt) ? $postExcerpt : wp_trim_words( $postExcerpt, 25, '...' );
		}, PHP_INT_MAX);

		$wpPreparedQuery = WpPostRepository::getUserPostsPreparedQuery(UserController::getProfiledUser()->Id, $this->getActiveSectionPageNumber(), $postsPerPage, array('post_type' => UltraCommHelper::getUserDisplayablePostTypes(UserController::getProfiledUser())));

		$noComments   = esc_html__('no comments', 'ultra-community');
		$oneComment   = esc_html__('1 comment','ultra-community');
		$moreComments = esc_html__('% comments','ultra-community');

		$arrUserPosts = array();
		while ($wpPreparedQuery->have_posts())
		{
			$wpPreparedQuery->the_post();

			$userPost = new \stdClass();

			$userPost->Id       = get_the_ID();
			$userPost->Title    = get_the_title();
			$userPost->Url      = esc_url(get_permalink( $userPost->Id ));
			$userPost->Excerpt  = get_the_excerpt();
			$userPost->Date     = get_the_date('M j, Y');
			$userPost->Comments = get_comments_number_text($noComments, $oneComment, $moreComments);

			$userPost->ThumbUrl = UltraCommUtils::getPostThumbnailUrl($userPost->Id);
			//!empty($userPost->ThumbUrl) ?: $userPost->ThumbUrl = UltraCommUtils::getPostDefaultThumbnailUrl($userPost->Id);

			foreach ((array)get_the_category($userPost->Id) as $index => $postCategory)
			{
				$userPost->Category    = esc_html( $postCategory->name );
				$userPost->CategoryUrl = esc_url( get_category_link($postCategory->term_id ) );
				break;
			}

			if(empty($userPost->ThumbUrl))
			{
				$userPost->Icon = UltraCommHelper::getPostFontAwesomeIcon($userPost->Id);
				unset($userPost->ThumbUrl);
			}

			$arrUserPosts[] = $userPost;
		}

		empty($arrUserPosts) ?: wp_reset_postdata();

		$arrUserPostsArguments = array(
			'currentPage' => $this->getActiveSectionPageNumber(),
			'totalPages'  => $wpPreparedQuery->max_num_pages,
			'arrUserPosts' => $arrUserPosts
		);

		if(empty($arrUserPosts))
		{
			$pageNoticeItem = new \stdClass();
			$pageNoticeItem->Type = 'info';

			$pageNoticeItem->Message = UltraCommHelper::getUserDisplayName(UserController::getProfiledUser()) . ' ';
			$pageNoticeItem->Message .= esc_html__('has no blog posts yet!', 'ultra-community');

			return TemplatesController::getTemplateOutputContent(TemplatesController::PAGE_CONTENT_NOTICE, array('pageNotice' => $pageNoticeItem));
		}



		return  TemplatesController::getTemplateOutputContent('user-profile-page-posts', $arrUserPostsArguments);

	}

	public function getPageCustomCss()
	{

		$dynamicCss = <<<PageDynCss


PageDynCss;



		if(in_array(UserProfileAppearancePublicModule::getHeaderStyleId(), array(3)))
		{
			$dynamicCss .= '@media (min-width:768px){div.uch > .uc-navbar-holder{margin-top:2em}}';
		}




		return apply_filters(UltraCommHooks::FILTER_USER_PROFILE_PAGE_CSS, $dynamicCss . parent::getPageCustomCss(), $this->getActiveSectionSlug());
	}


	private function getHeaderTagLineArguments()
	{
		$arrFields = UserProfileAppearancePublicModule::getInstance()->getOption(UserProfileAppearanceAdminModule::OPTION_HEADER_TAGLINE_FIELDS);
		if(empty($arrFields))
			return array();

		$arrProfileFormFields = UserController::getUserProfileFormFields(UserController::getProfiledUser());

		$arrInfoToRender = array();
		foreach($arrFields as $fieldToDisplay)
		{
			$fieldInstance = null;
			if(isset($arrProfileFormFields[$fieldToDisplay]))
			{
				$fieldInstance = $arrProfileFormFields[$fieldToDisplay];
			}
			elseif( ! MchUtils::isNullOrEmpty($fieldInstance = BaseFormAdminModule::getFieldInstanceByShortClassName($fieldToDisplay)) )
			{
				foreach ($arrProfileFormFields as $fieldId => $profileFormField)
				{
					if( !is_a($profileFormField, get_class($fieldInstance)) )
						continue;

					$fieldInstance = $profileFormField;
					break;
				}
			}

			if(! ($fieldInstance instanceof BaseField) )
				continue;

			$fieldInstance->Value = UserController::getUserProfileFormFieldValue(UserController::getProfiledUser(), $fieldInstance, false);

			if(empty($fieldInstance->Value))
				continue;


			$fieldInstance->FontAwesomeIcon = null;
			switch(true)
			{
				case $fieldInstance instanceof CountryField : $fieldInstance->FontAwesomeIcon      = 'fa-map-marker'; break;
				case $fieldInstance instanceof UserEmailField   : $fieldInstance->FontAwesomeIcon  = 'fa-envelope-o'; break;
				case $fieldInstance instanceof UserWebUrlField   : $fieldInstance->FontAwesomeIcon = 'fa-chain-broken'; break;

				case $fieldInstance instanceof UserNameField :
				case $fieldInstance instanceof UserNickNameField :
					$fieldInstance->Value = '@' . $fieldInstance->Value;break;
			}


			$arrInfoToRender[] = array('id' => $fieldToDisplay, 'label' => $fieldInstance->Label, 'value' => $fieldInstance->Value, 'icon' => $fieldInstance->FontAwesomeIcon);

		}

		return  apply_filters(UltraCommHooks::FILTER_PAGE_HEADER_TAG_LINE_ARGUMENTS, $arrInfoToRender, UserController::getProfiledUser()->Id);
	}



	protected function getPageHiddenContent()
	{
		$pageHiddenContent = parent::getPageHiddenContent();

		if(ModulesController::isModuleRegistered(ModulesController::MODULE_USER_REVIEWS) && $this->getActiveSectionSlug() === UserProfileAppearanceAdminModule::PROFILE_SECTION_REVIEWS){
			$pageHiddenContent .= UltraCommUtils::getWrappedPopupHolderContent('uc-modal-user-reviews-popup', '', TemplatesController::getTemplateOutputContent('user-reviews-form.php', array('primaryUserKey' => UserController::getProfiledUser())), null, 'uc-modal-md');
		}

		return $pageHiddenContent;
	}

	public function getSubMenuTemplateArguments()
	{
		// TODO: Implement getSubMenuTemplateArguments() method.
	}
}