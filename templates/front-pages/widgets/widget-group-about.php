<?php

use UltraCommunity\Entities\GroupEntity;
use UltraCommunity\UltraCommHelper;

if(empty($widgetItemInfo->GroupEntity))
	return;

$groupAvatarUrl = UltraCommHelper::getGroupPictureUrl($widgetItemInfo->GroupEntity);
$groupTypeOutput = '';
$groupTypeOutput .= '<i class = "fa ' . GroupEntity::getGroupTypeIconClass($widgetItemInfo->GroupEntity->GroupTypeId) . '"></i>';
$groupTypeOutput .= GroupEntity::getGroupTypeDescription($widgetItemInfo->GroupEntity->GroupTypeId) . ' ' . esc_html__('Group', 'ultra-community');


echo <<<OutputContent


<div class="uc-grid uc-grid--full uc-grid--center uc-grid--flex-cells uc-panel uc-sidebar-panel uc-group-about">
	
	<h3 class="uc-grid-cell uc-grid uc-grid--center uc-grid--fit uc-grid--flex-cells uc-panel-head">
		<span class="uc-grid-cell uc-grid--center uc-grid--justify-center uc-grid-cell--autoSize"><i class="fa fa-user-circle"></i></span>
		<span class="uc-grid-cell">$widgetItemInfo->Title</span>
	</h3>
	
	<div class="uc-grid-cell uc-panel-content">
		<div class="uc-bg-holder uc-circle-border" style="background-image:url($groupAvatarUrl)"></div>
		
		<div class="uc-grid uc-grid--full uc-grid--center uc-group-about-meta">
			<h3 class="uc-grid-cell">{$widgetItemInfo->GroupEntity->Name}</h3>
            <span class="uc-grid-cell">$groupTypeOutput</span>
		</div>
		
		<div class="uc-section-divider"></div>
		
		<p>{$widgetItemInfo->GroupEntity->Description}</p>
  
	</div>
	
	<div class="uc-grid-cell uc-panel-footer"></div>

</div>


OutputContent;


