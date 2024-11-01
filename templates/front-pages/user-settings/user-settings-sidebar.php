<?php
use UltraCommunity\UltraCommHelper;
use UltraCommunity\Controllers\UserController;
use UltraCommunity\Controllers\FrontPageController;
use UltraCommunity\UltraCommHooks;

defined('ABSPATH')  || exit;

$userPhotoUrl    = UltraCommHelper::getUserAvatarUrl(UserController::getProfiledUser());
$userDisplayName = UltraCommHelper::getUserDisplayName(UserController::getProfiledUser());
$userProfileUrl  = UltraCommHelper::getUserProfileUrl(UserController::getProfiledUser());
$userLogOutUrl   = FrontPageController::getLogOutPageUrl();

$homeUrl  = esc_url( home_url( '/' ) );

$sideBarProfileSettingsItems = isset($sideBarProfileSettingsItems) ? (array)$sideBarProfileSettingsItems : array();
$sideBarAccountSettingsItems = isset($sideBarAccountSettingsItems) ? (array)$sideBarAccountSettingsItems : array();
$sideBarGroupsSettingsItems  = isset($sideBarGroupsSettingsItems)  ? (array)$sideBarGroupsSettingsItems  : array();
$sideBarUserRelationsItems   = isset($sideBarUserRelationsItems)   ? (array)$sideBarUserRelationsItems   : array();

$viewProfileText     = esc_html__('View Profile', 'ultra-community');
$logOutText          = esc_html__('Log out', 'ultra-community');
$homeText            = esc_html__('Home', 'ultra-community');

$txtGreetings = sprintf(esc_html_x('Hello, %s!', 'translators - %s will replace user display name', 'ultra-community'), $userDisplayName);

$profileSvgIcon = \UltraCommunity\Entities\PageActionEntity::getSvgIcon('user-solid');

$arrNavigationSections = empty($arrSideBarArguments['arrNavigationSections']) ? array() : (array)$arrSideBarArguments['arrNavigationSections'];


$sideBarOutput = null;

$navigationSectionsOutput = null;
$quickLinksListOutput     = null;

foreach ($arrNavigationSections as $sectionKey => $sectionObject)
{

	isset($sectionObject->Title)           ?: $sectionObject->Title = null;
	isset($sectionObject->SectionKey)      ?: $sectionObject->SectionKey;
	isset($sectionObject->IsExpanded)      ?: $sectionObject->IsExpanded = null;
	isset($sectionObject->IconOutput )     ?: $sectionObject->IconOutput = null;
	//isset($sectionObject->NavigationItems) ?: $sectionObject->NavigationItems = array();

	if(empty($sectionObject->NavigationItems))
		continue;


	$sectionItemsListOutput = null;

	foreach ((array)$sectionObject->NavigationItems as $navigationItem)
    {
	    isset($navigationItem->IsActive)    ?: $navigationItem->IsActive = false;
	    isset($navigationItem->Name)        ?: $navigationItem->Name = null;
	    isset($navigationItem->Url)         ?: $navigationItem->Url = null;

	    isset($navigationItem->IconClass)   ?: $navigationItem->IconClass = 'fa fa-align-left';
	    isset($navigationItem->ItemKey)     ?: $navigationItem->ItemKey = null;
	    isset($navigationItem->Counter)     ?: $navigationItem->Counter = 0;

	    $navigationItem->Counter = empty($navigationItem->Counter) ? null : "<b>$navigationItem->Counter</b>";

        $activeClass = null;

	    if($navigationItem->IsActive)
        {
	        $activeClass = 'uc-item-active';
	        $sectionObject->IsExpanded = true;
        }

	    $linkOutput = <<<Output
            <li class="uc-grid-cell"><a class="{$activeClass}" href="{$navigationItem->Url}"><i class="{$navigationItem->IconClass}"></i><small>$navigationItem->Name</small>{$navigationItem->Counter}</a></li>
Output;

	    if($sectionKey === 'quick-links-settings')
        {
	        $quickLinksListOutput .= $linkOutput;
        }
        else
        {
	        $sectionItemsListOutput .= $linkOutput;
        }

    }

	if($sectionKey === 'quick-links-settings')
    {
        continue;
    }

    $sectionExpandedClass = $sectionObject->IsExpanded ? 'uc-section-expanded' : null;

	$navigationSectionsOutput .= <<<NavSectionOutput

<dl class="uc-grid uc-grid--full uc-grid--center uc-grid--flex-cells uc-panel uc-sidebar-navigation-section $sectionExpandedClass">

	<dt class="uc-grid-cell uc-panel-header">

		<div class="uc-grid uc-grid--fit uc-grid--center uc-grid--flex-cells">

			<span class="uc-grid-cell uc-grid-cell--autoSize">
				$sectionObject->IconOutput
			</span>

			<b class="uc-grid-cell">$sectionObject->Title</b>

			<span class="uc-grid-cell uc-grid-cell--autoSize uc-arrow-holder">
				<svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024"><path stroke-width="49.152" stroke-miterlimit="4" stroke-linecap="round" stroke-linejoin="round" d="M320.563 975.981l519.623-463.981-519.623-463.981-136.741 122.101 382.879 341.879-382.879 341.878 136.741 122.103z"></path></svg>
			</span>

		</div>

	</dt>

	<dd class="uc-grid-cell uc-panel-content ">
        <ul class="uc-grid uc-grid--full uc-grid--center uc-grid--flex-cells">$sectionItemsListOutput</ul>
	</dd>

</dl>

NavSectionOutput;

}


echo <<<SideBarOutput

<div class="uc-grid uc-grid--full uc-grid--center uc-grid--flex-cells uc-panel uc-sidebar-user-section">

	<div class="uc-grid-cell uc-panel-header">

		<div class="uc-grid uc-grid--fit uc-grid--flex-cells">
			<div class="uc-grid-cell uc-grid-cell--autoSize uc-bg-holder" style="background-image:url($userPhotoUrl)"></div>

			<div class="uc-grid-cell">

				<div class="uc-grid uc-grid--full uc-grid--flex-cells">
					<b class="uc-grid-cell uc-grid-cell--center">$txtGreetings</b>
					<div class="uc-grid-cell uc-grid-cell--center">
						<a class = "uc-home-url"    href="$homeUrl"        uc-tooltip="$homeText"        uc-tooltip-flow="down"><i class="fa fa-home"></i></a>
						<a class = "uc-profile-url" href="$userProfileUrl" uc-tooltip="$viewProfileText" uc-tooltip-flow="down">$profileSvgIcon</a>
						<a class = "uc-logout-url"  href="$userLogOutUrl"  uc-tooltip="$logOutText"      uc-tooltip-flow="down"><i class="fa fa-power-off"></i></a>
					</div>
				</div>

			</div>

		</div>
	</div>

	<div class="uc-grid-cell uc-panel-content">
        <ul class="uc-grid uc-grid--full uc-grid--flex-cells">$quickLinksListOutput</ul>
    </div>

</div>

$navigationSectionsOutput

SideBarOutput;



return;




?>





<?php

return;

$userPhotoUrl    = UltraCommHelper::getUserAvatarUrl(UserController::getProfiledUser());
$userDisplayName = UltraCommHelper::getUserDisplayName(UserController::getProfiledUser());
$userProfileUrl  = UltraCommHelper::getUserProfileUrl(UserController::getProfiledUser());
$userLogOutUrl   = FrontPageController::getLogOutPageUrl();

$sideBarProfileSettingsItems = isset($sideBarProfileSettingsItems) ? (array)$sideBarProfileSettingsItems : array();
$sideBarAccountSettingsItems = isset($sideBarAccountSettingsItems) ? (array)$sideBarAccountSettingsItems : array();
$sideBarGroupsSettingsItems  = isset($sideBarGroupsSettingsItems)  ? (array)$sideBarGroupsSettingsItems  : array();
$sideBarUserRelationsItems   = isset($sideBarUserRelationsItems)   ? (array)$sideBarUserRelationsItems   : array();

$viewProfileText     = esc_html__('View Profile', 'ultra-community');
$logOutText          = esc_html__('Log out', 'ultra-community');

$groupsSettingsText      = esc_html__('Groups Settings', 'ultra-community');
$profileSettingsText     = esc_html__('Profile Settings', 'ultra-community');
$accountSettingsText     = esc_html__('Account Settings', 'ultra-community');
$relationsSettingsText   = esc_html__('Relations', 'ultra-community');

$userCardOutput = $profileSettingsOutput = $groupsSettingsOutput = $accountSettingsOutput = $userRelationsOutput = null;

$userCardOutput = <<<UserCardOutput

<div class="uc-grid uc-grid--full uc-grid--center  uc-panel uc-sidebar-panel uc-settings-sidebar-user-card">

	<h3 class="uc-grid-cell uc-grid uc-grid--center uc-panel-head"></h3>

	<div class="uc-grid-cell uc-grid   uc-grid--full uc-panel-content">
		<div class="uc-settings-sidebar-avatar-holder" style="background-image:url($userPhotoUrl);"></div>
		<h4>$userDisplayName</h4>
		<div>
			<a class="uc-button uc-button-left-icon" href="$userProfileUrl">
				<i class="fa fa-power-off"></i><span>$viewProfileText</span>
			</a>
		</div>
	</div>

	<div class="uc-grid-cell uc-panel-footer">

		<a class="uc-button uc-button-left-icon" href="$userLogOutUrl">
			<i class="fa fa-power-off"></i><span>$logOutText</span>
		</a>

	</div>
</div>


UserCardOutput;

if(!empty($sideBarProfileSettingsItems))
{
	$profileSettingsList = null;
	foreach ($sideBarProfileSettingsItems as $sideBarProfileSettingsItem)
	{
		$profileSettingsList .= '<li>';
		$profileSettingsList .= empty($sideBarProfileSettingsItem->IsActive) ? '<a class="uc-hvr-underline-from-center" href="' . $sideBarProfileSettingsItem->Url . '">' : '<a class="uc-hvr-underline-from-center uc-hvr-underline-active" href="' . $sideBarProfileSettingsItem->Url . '">';
		$profileSettingsList .= '<i class="fa '. $sideBarProfileSettingsItem->Icon .'"></i>';
		$profileSettingsList .= '<span>' . $sideBarProfileSettingsItem->Title . '</span>';
		$profileSettingsList .= '</a>';
		$profileSettingsList .= '</li>';
	}

	$profileSettingsOutput = <<<ProfileSettingsOutput

<div class="uc-grid uc-grid--full uc-grid--center uc-grid--flex-cells uc-panel uc-sidebar-panel uc-settings-sidebar-profile">

	<h3 class="uc-grid-cell uc-grid uc-grid--center uc-grid--fit uc-grid--flex-cells uc-panel-head">
		<span class="uc-grid-cell uc-grid--center uc-grid--justify-center uc-grid-cell--autoSize"><i class="fa fa-cogs"></i></span>
		<span class="uc-grid-cell">$profileSettingsText</span>
	</h3>

	<div class="uc-grid-cell uc-panel-content">
		<ul>$profileSettingsList</ul>
	</div>

	<div class="uc-grid-cell uc-panel-footer"></div>

</div>

ProfileSettingsOutput;

}


if(!empty($sideBarGroupsSettingsItems))
{
	$groupsSettingsList = null;
	foreach ($sideBarGroupsSettingsItems as $sideBarGroupsSettingsItem)
	{
		$groupsSettingsList .= '<li>';
		$groupsSettingsList .= empty($sideBarGroupsSettingsItem->IsActive) ? '<a class="uc-hvr-underline-from-center" href="' . $sideBarGroupsSettingsItem->Url . '">' : '<a class="uc-hvr-underline-from-center uc-hvr-underline-active" href="' . $sideBarGroupsSettingsItem->Url . '">';
		$groupsSettingsList .= '<i class="fa '. $sideBarGroupsSettingsItem->Icon .'"></i>';
		$groupsSettingsList .= '<span>' . $sideBarGroupsSettingsItem->Title . '</span>';
		$groupsSettingsList .= '</a>';
		$groupsSettingsList .= '</li>';
	}

	$groupsSettingsOutput = <<<GroupsSettingsOutput

<div class="uc-grid uc-grid--full uc-grid--center uc-grid--flex-cells uc-panel uc-sidebar-panel uc-settings-sidebar-profile">

	<h3 class="uc-grid-cell uc-grid uc-grid--center uc-grid--fit uc-grid--flex-cells uc-panel-head">
		<span class="uc-grid-cell uc-grid--center uc-grid--justify-center uc-grid-cell--autoSize"><i class="fa fa-cogs"></i></span>
		<span class="uc-grid-cell">$groupsSettingsText</span>
	</h3>

	<div class="uc-grid-cell uc-panel-content">
		<ul>$groupsSettingsList</ul>
	</div>

	<div class="uc-grid-cell uc-panel-footer"></div>

</div>

GroupsSettingsOutput;

}

if(!empty($sideBarUserRelationsItems))
{
	$userRelationsList = null;
	foreach ($sideBarUserRelationsItems as $userRelationItem)
	{
		$userRelationsList .= '<li>';
		$userRelationsList .= empty($userRelationItem->IsActive) ? '<a class="uc-hvr-underline-from-center" href="' . $userRelationItem->Url . '">' : '<a class="uc-hvr-underline-from-center uc-hvr-underline-active" href="' . $userRelationItem->Url . '">';
		$userRelationsList .= '<i class="fa '. $userRelationItem->Icon .'"></i>';
		$userRelationsList .= '<span>' . $userRelationItem->Title . '</span>';
		$userRelationsList .= '</a>';
		$userRelationsList .= '</li>';
	}

	$userRelationsOutput = <<<UserRelationsOutput

<div class="uc-grid uc-grid--full uc-grid--center uc-grid--flex-cells uc-panel uc-sidebar-panel uc-settings-sidebar-profile">

	<h3 class="uc-grid-cell uc-grid uc-grid--center uc-grid--fit uc-grid--flex-cells uc-panel-head">
		<span class="uc-grid-cell uc-grid--center uc-grid--justify-center uc-grid-cell--autoSize"><i class="fa fa-cogs"></i></span>
		<span class="uc-grid-cell">$relationsSettingsText</span>
	</h3>

	<div class="uc-grid-cell uc-panel-content">
		<ul>$userRelationsList</ul>
	</div>

	<div class="uc-grid-cell uc-panel-footer"></div>

</div>

UserRelationsOutput;

}


if(!empty($sideBarAccountSettingsItems))
{
	$accountSettingsList = null;
	foreach ($sideBarAccountSettingsItems as $sideBarAccountSettingsItem)
	{
		$accountSettingsList .= '<li>';
		$accountSettingsList .=  empty($sideBarAccountSettingsItem->IsActive) ? '<a class="uc-hvr-underline-from-center" href="' . $sideBarAccountSettingsItem->Url . '">' : '<a class="uc-hvr-underline-from-center uc-hvr-underline-active" href="' . $sideBarAccountSettingsItem->Url . '">';
		$accountSettingsList .= '<i class="fa '. $sideBarAccountSettingsItem->Icon .'"></i>';
		$accountSettingsList .= '<span>' . $sideBarAccountSettingsItem->Title . '</span>';
		$accountSettingsList .= '</a>';
		$accountSettingsList .= '</li>';
	}

	$accountSettingsOutput = <<<AccountSettingsOutput

<div class="uc-grid uc-grid--full uc-grid--center uc-grid--flex-cells uc-panel uc-sidebar-panel uc-settings-sidebar-profile">

	<h3 class="uc-grid-cell uc-grid uc-grid--center uc-grid--fit uc-grid--flex-cells uc-panel-head">
		<span class="uc-grid-cell uc-grid--center uc-grid--justify-center uc-grid-cell--autoSize"><i class="fa fa-cogs"></i></span>
		<span class="uc-grid-cell">$accountSettingsText</span>
	</h3>

	<div class="uc-grid-cell uc-panel-content">
		<ul>$accountSettingsList</ul>
	</div>

	<div class="uc-grid-cell uc-panel-footer"></div>

</div>

AccountSettingsOutput;

}

echo $userCardOutput;

do_action(UltraCommHooks::ACTION_USER_PROFILE_SETTINGS_SIDEBAR_BEFORE_PROFILE_SECTIONS, UserController::getProfiledUser());
	echo $profileSettingsOutput;
do_action(UltraCommHooks::ACTION_USER_PROFILE_SETTINGS_SIDEBAR_AFTER_PROFILE_SECTIONS, UserController::getProfiledUser());

echo $groupsSettingsOutput, $userRelationsOutput, $accountSettingsOutput;

