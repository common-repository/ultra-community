<?php
/** @var $activityEntity UltraCommunity\Entities\ActivityEntity */
if(!isset($activityEntity))
	return;

echo '<div class="uc-grid-cell uc-activity-content-status">', $activityEntity->getPostObjectContent(), '</div>';
