<?php
use UltraCommunity\UltraCommHelper;

if(empty($widgetItemInfo->ArrOnlineUserEntities))
	return;

$userListOutputContent = null;
foreach ((array)$widgetItemInfo->ArrOnlineUserEntities as $userEntity)
{
	$userDisplayName = UltraCommHelper::getUserDisplayName($userEntity);
	$userAvatarUrl   = UltraCommHelper::getUserAvatarUrl($userEntity);
	$userProfileUrl  = UltraCommHelper::getUserProfileUrl($userEntity);
	
	$userListOutputContent .= "<a class=\"uc-grid-cell uc-grid-cell--autoSize uc-bg-holder uc-circle-border\" uc-tooltip=\"$userDisplayName\" href=\"$userProfileUrl\" style=\"background-image:url($userAvatarUrl)\"></a>";
	
}

echo <<<OutputContent

<div class="uc-grid uc-grid--full uc-grid--center uc-grid--flex-cells uc-panel uc-sidebar-panel uc-group-online-users">
	
	<h3 class="uc-grid-cell uc-grid uc-grid--center uc-grid--fit uc-grid--flex-cells uc-panel-head">
		<span class="uc-grid-cell uc-grid--center uc-grid--justify-center uc-grid-cell--autoSize"><i class="fa fa-wifi"></i></span>
		<span class="uc-grid-cell">{$widgetItemInfo->Title}</span>
	</h3>
	
	<div class="uc-grid-cell uc-panel-content">
		
		<div class="uc-grid uc-grid--fit  uc-grid--flex-cells">$userListOutputContent</div>
		
	</div>
	
	<div class="uc-grid-cell uc-panel-footer"></div>

</div>

OutputContent;

