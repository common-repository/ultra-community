<?php
use UltraCommunity\Controllers\UserController;
use UltraCommunity\UltraCommHelper;

defined('ABSPATH') || exit;

$arrNotifications       = empty($arrNotifications)       ? array() : (array)$arrNotifications;
$arrExpandableMenuItems = empty($arrExpandableMenuItems) ? array() : (array)$arrExpandableMenuItems;
$userAvatarUrl          = UltraCommHelper::getUserAvatarUrl(UserController::getLoggedInUser());
$notificationsOutputList = null;
foreach($arrNotifications as $notificationKey => $notificationItem)
{
	$notificationKey = sanitize_html_class($notificationKey);
	
	isset($notificationItem->Tooltip)           ?: $notificationItem->Tooltip = null;
	isset($notificationItem->ContentBeforeIcon) ?: $notificationItem->ContentBeforeIcon = null;
	isset($notificationItem->ContentAfterIcon)  ?: $notificationItem->ContentAfterIcon  = null;
	
	$notificationsOutputList .=<<<NofificationOutput
	<li class="uc-grid-cell uc-grid-cell--autoSize uc-navbar-notification uc-$notificationKey"  uc-tooltip="{$notificationItem->Tooltip}" uc-tooltip-flow="down">
	    {$notificationItem->ContentBeforeIcon}
		<a href="{$notificationItem->Url}">
			<i class="fa {$notificationItem->Icon}"></i>
			<span>{$notificationItem->Number}</span>
		</a>
		{$notificationItem->ContentAfterIcon}
	</li>
NofificationOutput;
}


$expandableMenuItemsOutputList = null;
foreach($arrExpandableMenuItems as $expandableMenuItem)
{
	$expandableMenuItemsOutputList .=<<<ExpandableMenuItemOutput
		<li class="uc-grid-cell">
			<a class="uc-hvr-underline-from-center" href="$expandableMenuItem->Url">
				<i class="fa $expandableMenuItem->Icon"></i>
				<span>$expandableMenuItem->Title</span>
			</a>
		</li>
ExpandableMenuItemOutput;

}

?>

<ul class="uc-grid uc-grid--fit uc-grid--center uc-grid--flex-cells uc-navbar-notifications">

	<?php echo $notificationsOutputList; ?>

	<li class="uc-grid-cell uc-grid-cell--autoSize uc-navbar-user-picture" style="background-image:url(<?php echo $userAvatarUrl; ?>)"></li>

 
	<li class="uc-grid-cell uc-grid-cell--autoSize uc-navbar-settings-arrow">
		<i class="fa fa-angle-down"></i>
	</li>

	<li class="uc-grid-cell uc-grid-cell--autoSize uc-navbar-settings-menu-holder">

		<ul class="uc-grid uc-grid--full uc-grid--center uc-grid--flex-cell">
			<?php echo $expandableMenuItemsOutputList; ?>
		</ul>

	</li>
</ul>



