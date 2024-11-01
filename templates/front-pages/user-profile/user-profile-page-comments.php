<?php

defined('ABSPATH') || exit;

$currentPage     = empty($currentPage)     ? 1 : (int)$currentPage;
$totalPages      = empty($totalPages)      ? 0 : (int)$totalPages;
$arrUserComments = empty($arrUserComments) ? array() : (array)$arrUserComments;

echo '<div class="uc-user-comments-section">';

foreach ($arrUserComments as $userComment)
{

	$userCommentBox = <<<UserCommentBox
		<div class="uc-grid uc-grid--full uc-grid--flex-cells uc-panel uc-user-comment-holder">

			<div class="uc-grid-cell uc-grid-cell--center uc-grid uc-grid--fit uc-panel-head">

				<div class="uc-grid-cell uc-grid-cell--autoSize uc-user-avatar-holder">
					<a href="{$userComment->UserProfileUrl}" style="background-image:url({$userComment->UserAvatarUrl})"></a>
				</div>

				<div class="uc-grid-cell uc-user-comment-info">
					<a href="{$userComment->UserProfileUrl}">{$userComment->UserDisplayName}</a>
					<span><i class="fa fa-clock-o"></i>{$userComment->Date}</span>
				</div>
				<div class="uc-grid-cell uc-grid-cell--center uc-grid-cell--autoSize">
					<a class="uc-button uc-button-left-icon" href = "{$userComment->Url}">
						<i class="fa fa-eye"></i><span>view comment</span>
					</a>
				</div>
			</div>

			<p class="uc-grid-cell uc-grid-cell--center uc-panel-content">{$userComment->Excerpt}</p>

		</div>

UserCommentBox;

	echo $userCommentBox;

}

echo '</div>';

echo ($totalPages > 1 ) ? uc_get_pagination_markup($currentPage, $totalPages) : null;
