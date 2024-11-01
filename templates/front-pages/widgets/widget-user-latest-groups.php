<?php
use UltraCommunity\UltraCommHelper;

if(empty($widgetItemInfo->ArrGroupEntities))
	return;

$groupsListOutputContent = null;


foreach ((array)$widgetItemInfo->ArrGroupEntities as $groupEntity)
{
	$groupDisplayName = esc_html($groupEntity->Name);
	$groupAvatarUrl   = UltraCommHelper::getGroupPictureUrl($groupEntity);
	$groupProfileUrl  = UltraCommHelper::getGroupUrl($groupEntity);
	$groupTypeDescr   = sprintf('%s %s', $groupEntity::getGroupTypeDescription($groupEntity->GroupTypeId), esc_html__('Group', 'ultra-community'));
	$groupTypeIcon    = '<i class = "fa ' .  $groupEntity::getGroupTypeIconClass($groupEntity->GroupTypeId) . '"></i>';
	
	$groupEntity->Actions   = empty($groupEntity->Actions) ? array() : (array)$groupEntity->Actions;
	$userActionsOutputList = null;
	
	foreach ($groupEntity->Actions as $action)
	{
		$userActionsOutputList .= $action->getOutputContent();
	}
	
	$groupsListOutputContent .= <<<OutputContent

		<div class="uc-grid uc-grid--fit  uc-grid--flex-cells">
			
			<div class="uc-grid-cell uc-grid-cell--autoSize uc-group-avatar-holder">
				<a class="uc-bg-holder uc-circle-border" href="$groupProfileUrl" style="background-image:url($groupAvatarUrl)"></a>
			</div>
			
			<div class="uc-grid-cell uc-group-meta">
				<a href="$groupProfileUrl">$groupDisplayName</a>
				<span>$groupTypeIcon $groupTypeDescr</span>
			</div>
			
			<div class="uc-grid-cell uc-grid-cell--center uc-grid-cell--autoSize uc-user-actions">
				$userActionsOutputList
			</div>
			
		</div>


OutputContent;

}

echo <<<OutputContent

<div class="uc-grid uc-grid--full uc-grid--center uc-grid--flex-cells uc-panel uc-sidebar-panel uc-user-latest-groups">
	
	<h3 class="uc-grid-cell uc-grid uc-grid--center uc-grid--fit uc-grid--flex-cells uc-panel-head">
		<span class="uc-grid-cell uc-grid--center uc-grid--justify-center uc-grid-cell--autoSize"><i class="fa fa-users"></i></span>
		<span class="uc-grid-cell">{$widgetItemInfo->Title}</span>
	</h3>
	
	<div class="uc-grid-cell uc-panel-content">
		$groupsListOutputContent
	</div>

	<div class="uc-grid-cell uc-panel-footer"></div>
	
</div>

OutputContent;

