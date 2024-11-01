<?php
/** @var $activityEntity UltraCommunity\Entities\ActivityEntity */
use UltraCommunity\UltraCommHelper;

if(!isset($activityEntity))
	return;

!empty($headerTitle) ?: $headerTitle = null;


$userAvatarUrl  = UltraCommHelper::getUserAvatarUrl($activityEntity->UserEntity);
$userProfileUrl = UltraCommHelper::getUserProfileUrl($activityEntity->UserEntity);

$activityTimeAgo = human_time_diff(mysql2date('U', $activityEntity->CreatedDate, true), current_time('timestamp')) . ' ' . esc_html__('ago', 'ultra-community');

echo <<<ActivityHeaderOutput

    <a href="$userProfileUrl" class="uc-grid-cell uc-grid-cell--autoSize uc-bg-holder" style="background-image:url($userAvatarUrl)"></a>
    
    <div class="uc-grid-cell uc-activity-header-title">
        <p>$headerTitle</p>
        <span>$activityTimeAgo</span>
    </div>


ActivityHeaderOutput;
