<?php
use UltraCommunity\Entities\ActivityEntity;
use UltraCommunity\FrontPages\ActivityPage;

$currentPage         = empty($currentPage)         ? 1  : (int)$currentPage;
$arrActivityEntities = empty($arrActivityEntities) ? array() : (array)$arrActivityEntities;


$singleActivityClassName = empty($displaySingleActivity) ? null : 'uc-single-activity';

if(1 === $currentPage)
{
	echo "<div class=\"uc-group-activity-section $singleActivityClassName\">";
	
	ActivityPage::renderActivityPostForm(ActivityEntity::ACTIVITY_TARGET_TYPE_GROUP);
}

$groupHasActivity = isset($arrActivityEntities[0]);
foreach ($arrActivityEntities as $activityEntity)
{
	ActivityPage::renderActivityEntity($activityEntity);
}

if(1 === $currentPage)
{
	echo '</div>';
	
	if($groupHasActivity && !$singleActivityClassName)
	{
		echo '<div class="uc-page-load-status"><div class="uc-ajax-loader infinite-scroll-request"><div class="bounce-left"></div><div class="bounce-middle"></div><div class="bounce-right"></div></div></div>';
	}
	
}
