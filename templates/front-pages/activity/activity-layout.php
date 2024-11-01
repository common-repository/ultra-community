<?php

use UltraCommunity\UltraCommHooks;
/** @var $activityEntity UltraCommunity\Entities\ActivityEntity */
if(!isset($activityEntity))
	return;

echo '<div class = "uc-grid uc-grid--full uc-grid--flex-cells uc-panel uc-activity-holder">';

	echo '<div class = "uc-grid-cell uc-grid-cell--center uc-panel-head ">';
		echo '<div class = "uc-grid uc-grid--fit uc-grid--flex-cells uc-activity-header">';
			do_action(UltraCommHooks::ACTION_ACTIVITY_RENDER_HEADER, $activityEntity);
		echo '</div>';
	echo '</div>';

	echo '<div class = "uc-grid-cell uc-panel-content">';
		echo '<div class = "uc-grid uc-grid--full uc-grid--flex-cells uc-activity-content">';
			do_action(UltraCommHooks::ACTION_ACTIVITY_RENDER_CONTENT, $activityEntity);
		echo '</div>';
	echo '</div>';
	
	echo '<div class = "uc-grid-cell uc-panel-footer">';
		echo '<div class = "uc-grid uc-grid--full uc-grid-medium--fit uc-grid--flex-cells uc-activity-footer">';
			do_action(UltraCommHooks::ACTION_ACTIVITY_RENDER_FOOTER, $activityEntity);
		echo '</div>';
	echo '</div>';

	echo '<ul class="uc-grid-cell uc-activity-comments-holder">';
		do_action(UltraCommHooks::ACTION_ACTIVITY_RENDER_COMMENTS_LIST, $activityEntity);
	echo '</ul>';

	echo '<div class="uc-grid-cell uc-activity-comments-form-holder">';
		do_action(UltraCommHooks::ACTION_ACTIVITY_RENDER_COMMENTS_FORM, $activityEntity);
	echo '</div>';
	
	
echo '</div>';