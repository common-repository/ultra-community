<?php
/** @var $activityComment \WP_Comment */
use UltraCommunity\Controllers\UserController;
use UltraCommunity\UltraCommHelper;
use UltraCommunity\UltraCommHooks;

if(empty($activityComment->user_id))
	return;

isset($activityEntity) ?: $activityEntity = null;

$userAvatarUrl   = esc_url(uc_get_user_avatar_url($activityComment->user_id));
$userProfileUrl  = esc_url(uc_get_user_profile_url($activityComment->user_id));
$userDisplayName = esc_html(UltraCommHelper::getUserDisplayName(($activityComment->user_id)));

$commentPosted   = human_time_diff(mysql2date('U', $activityComment->comment_date, true), current_time('timestamp')) . ' ' . esc_html__('ago', 'ultra-community');

$commentContent  = apply_filters(UltraCommHooks::FILTER_ACTIVITY_COMMENT_CONTENT, get_comment_text($activityComment), $activityComment, $activityEntity);

$commentId       = (int)$activityComment->comment_ID;
$commentParentId = (int)$activityComment->comment_parent;
$commentReplyText = esc_html__('Reply', 'ultra-community');


$commentActionsOutput  = '';
$commentActionsOutput .= '<div class="uc-grid uc-grid--center uc-grid--full uc-activity-comment-actions">';
$commentActionsOutput .= '	<div class="uc-grid-cell uc-grid uc-grid--center">';
$commentActionsOutput .= '		__REPLY_ACTION__';
$commentActionsOutput .= '	</div>';
$commentActionsOutput .= '	<div class="uc-grid-cell uc-comments-form-holder uc-hidden"></div>';
$commentActionsOutput .= '</div>';

if(!empty($depth) && $depth < 5)
{
	$commentActionsOutput = str_replace('__REPLY_ACTION__', '<div class="uc-grid-cell uc-grid-cell--center"><a data-reply-activity-comment-id = "'.$commentId.'"><i class="fa fa-comment"></i>' . $commentReplyText . '</a></div>', $commentActionsOutput);
}

$commentActionsOutput = str_replace(array('__REPLY_ACTION__'), '', $commentActionsOutput);

$holderClass = empty($activityComment->isHidden) ? '' : 'uc-hidden';

$commentOutput = <<<CommentOutput

<li class="{$holderClass}">

		<div class = "uc-grid uc-grid--center uc-grid--fit uc-grid--flex-cells">

			<a href="$userProfileUrl" class="uc-grid-cell uc-grid-cell--autoSize uc-grid-cell--top uc-bg-holder uc-circle-border uc-user-avatar-holder" style="background-image: url($userAvatarUrl)">
			</a>

			<div class="uc-grid-cell uc-grid-cell--top uc-grid">

				<div class="uc-grid uc-grid--fit uc-grid--flex-cells uc-grid--center uc-activity-comment-meta" >

					<div class="uc-grid-cell uc-grid-cell--autoSize">
						<p>
							<a href="$userProfileUrl">$userDisplayName</a>
						</p>
					</div>
					<div class="uc-grid-cell"></div>
					<div class="uc-grid-cell uc-grid-cell--autoSize">
						<p class="uc-activity-comment-time-ago"><i class="fa fa-clock-o"></i>$commentPosted</p>
					</div>

				</div>

				<div class="uc-grid uc-grid--center uc-grid--full">
					<div class="uc-grid-cell">
						<p style="line-height: 1.5em;">$commentContent</p>
					</div>
				</div>

				$commentActionsOutput


			</div>

		</div>



CommentOutput;

echo $commentOutput;

return;
?>

<div class="uc-grid uc-grid--center uc-grid--full uc-activity-comment-actions">
	<div class="uc-grid-cell uc-grid uc-grid--center">
		<div class="uc-grid-cell uc-grid-cell--center"><a href="#" data-reply-activity-comment-id = "$commentId"><i class="fa fa-reply" aria-hidden="true"></i>$commentReplyText</a></div>
	</div>
	<div class="uc-grid-cell uc-comments-form-holder uc-hidden"></div>
</div>
