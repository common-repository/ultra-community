<?php
/** @var $activityEntity UltraCommunity\Entities\ActivityEntity */
use UltraCommunity\Controllers\ActivityController;
use UltraCommunity\UltraCommHooks;

if(empty($activityEntity->ActivityId))
	return;

$arrActivityComments = ActivityController::getActivityComments($activityEntity);
$totalComments = count($arrActivityComments);

if(0 === $totalComments){
	return;
}


if($totalComments > 5)
{
	$showAllCommentsText = sprintf(esc_html__('Show All Comments (%d)', 'ultra-community'), $totalComments);
	echo '<li class = "uc-activity-show-all-comments">',
		'<a class=""><i class="fa fa-eye" aria-hidden="true"></i>', $showAllCommentsText, '</a>',
	'</li>';
	unset($showAllCommentsText);
}

wp_list_comments(array('style' => 'ul', 'reverse_top_level' => true, 'max_depth' => 5, 'callback' => function($comment, $args, $depth) use($activityEntity){
	do_action(UltraCommHooks::ACTION_ACTIVITY_RENDER_COMMENT, $comment, $activityEntity,  $args, $depth);
},), $arrActivityComments);

unset($activityEntity, $arrActivityComments, $totalComments);
