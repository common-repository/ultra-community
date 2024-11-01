<?php
use UltraCommunity\UltraCommHelper;

if(empty($widgetItemInfo->ArrRecentUserEntities))
	return;

$userListOutputContent = null;


foreach ((array)$widgetItemInfo->ArrRecentUserEntities as $userEntity)
{
	$userDisplayName = UltraCommHelper::getUserDisplayName($userEntity);
	$userAvatarUrl   = UltraCommHelper::getUserAvatarUrl($userEntity);
	$userProfileUrl  = UltraCommHelper::getUserProfileUrl($userEntity);
	$userNiceName    = empty($userEntity->NiceName) ? $userEntity->UserName : $userEntity->NiceName;

	$userEntity->Actions   = empty($userEntity->Actions) ? array() : (array)$userEntity->Actions;
	$userActionsOutputList = null;
	
	foreach ($userEntity->Actions as $action)
	{
		$userActionsOutputList .= $action->getOutputContent();
	}
	
	$userListOutputContent .= <<<OutputContent

		<div class="uc-grid uc-grid--fit  uc-grid--flex-cells">
			
			<div class="uc-grid-cell uc-grid-cell--autoSize uc-user-avatar-holder">
				<a class="uc-bg-holder uc-circle-border" href="$userProfileUrl" style="background-image:url($userAvatarUrl)"></a>
			</div>
			
			<div class="uc-grid-cell uc-user-meta">
				<a href="$userProfileUrl">$userDisplayName</a>
				<span>@$userNiceName</span>
			</div>
			
			<div class="uc-grid-cell uc-grid-cell--center uc-grid-cell--autoSize uc-user-actions">
				$userActionsOutputList
			</div>
			
		</div>


OutputContent;

}

echo <<<OutputContent

<div class="uc-grid uc-grid--full uc-grid--center uc-grid--flex-cells uc-panel uc-sidebar-panel uc-group-recent-users">
	
	<h3 class="uc-grid-cell uc-grid uc-grid--center uc-grid--fit uc-grid--flex-cells uc-panel-head">
		<span class="uc-grid-cell uc-grid--center uc-grid--justify-center uc-grid-cell--autoSize"><i class="fa fa-clock-o"></i></span>
		<span class="uc-grid-cell">{$widgetItemInfo->Title}</span>
	</h3>
	
	<div class="uc-grid-cell uc-panel-content">
		$userListOutputContent
	</div>

	<div class="uc-grid-cell uc-panel-footer"></div>
	
</div>

OutputContent;

