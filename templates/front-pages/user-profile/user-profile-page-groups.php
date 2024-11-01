<?php
use UltraCommunity\UltraCommHelper;

if(empty($arrUserGroups))
return;

$currentPage    = empty($currentPage) ? 1 : (int)$currentPage;
$totalPages     = empty($totalPages)  ? 1 : (int)$totalPages;

echo '<div class="uc-user-groups-section">';


foreach ($arrUserGroups as $groupEntity)
{
	
	$groupUrl = UltraCommHelper::getGroupUrl($groupEntity);
	$groupAvatarUrl = UltraCommHelper::getGroupPictureUrl($groupEntity);
	$groupEntity->Name = esc_html($groupEntity->Name);
	$groupEntity->Description = esc_html($groupEntity->Description);
	
	
	$groupAction       = empty($groupEntity->Actions) ? null : reset($groupEntity->Actions);
	$groupActionOutput = empty($groupAction) ? null : $groupAction->getOutputContent();
	
	$groupType     = $groupEntity::getGroupTypeDescription($groupEntity->GroupTypeId) . ' ' . esc_html__('Group', 'ultra-community');
	$groupTypeIcon = $groupEntity::getGroupTypeIconClass($groupEntity->GroupTypeId);
	
	echo <<<UserGroup

		<div class="uc-grid uc-grid--full uc-grid--flex-cells uc-panel uc-user-group-holder">

			<div class="uc-grid-cell uc-grid-cell--center uc-grid uc-grid--fit uc-panel-head">

				<div class="uc-grid-cell uc-grid-cell--autoSize uc-group-avatar-holder">
					<a href="{$groupUrl}" style="background-image:url({$groupAvatarUrl})"></a>
				</div>

				<div class="uc-grid-cell uc-user-group-info">
					<a href="{$groupUrl}">{$groupEntity->Name}</a>
					<span><i class="fa $groupTypeIcon"></i>$groupType</span>
				</div>
				
				<div class="uc-grid-cell uc-grid-cell--center uc-grid-cell--autoSize">
					{$groupActionOutput}
				</div>
				
			</div>

			<p class="uc-grid-cell uc-grid-cell--center uc-panel-content">{$groupEntity->Description}</p>

		</div>

UserGroup;

}

echo '</div>';

echo ($totalPages > 1 ) ? uc_get_pagination_markup($currentPage, $totalPages) : null;

