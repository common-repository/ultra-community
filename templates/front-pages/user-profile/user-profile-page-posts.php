<?php

defined('ABSPATH') || exit;

$arrUserPosts = empty($arrUserPosts) ? array() : (array)$arrUserPosts;
$currentPage  = empty($currentPage)  ? 1       : (int)$currentPage;
$totalPages   = empty($totalPages)   ? 0       : (int)$totalPages;


echo '<div class="uc-user-posts-section">';

foreach($arrUserPosts  as $userPost)
{
	$postThumbHolderClass = !empty($userPost->ThumbUrl) ? null : 'uc-user-post-no-thumb' ;
	$postIconOutput       = !empty($userPost->Icon)     ? "<i class=\"fa fa-{$userPost->Icon}\"></i>" : null;
	$postThumbHolderStyle = !empty($userPost->ThumbUrl) ? "style=\"background-image:url($userPost->ThumbUrl)\"" : null;

	$postCategoryListOutput = !empty($userPost->Category) && !empty($userPost->CategoryUrl) ? "<li><i class=\"fa fa-tags\"></i><a href=\"{$userPost->CategoryUrl}\">{$userPost->Category}</a></li>" : null;

	$postPanelBox = <<<PostPanelBox

	<div class="uc-grid uc-grid--full uc-grid-medium--fit uc-grid--justify-center uc-grid--flex-cells uc-panel uc-user-post-holder">

		<a class="uc-grid-cell uc-grid-cell--autoSize uc-post-thumb-holder $postThumbHolderClass" $postThumbHolderStyle>
			$postIconOutput
		</a>

		<div class="uc-grid-cell uc-post-content-holder">
			<h3>
				<a href = "{$userPost->Url}">{$userPost->Title}</a>
			</h3>

			<ul class="uc-user-post-meta">
				<li><i class="fa fa-clock-o"></i><span>{$userPost->Date}</span></li>
				$postCategoryListOutput
				<li><i class="fa fa-comments-o"></i><span>{$userPost->Comments}</span></li>
			</ul>

			<p class="uc-user-post-excerpt uc-text-justify">{$userPost->Excerpt}</p>

			<a class="uc-button uc-button-left-icon" href = "{$userPost->Url}">
				<i class="fa fa-angle-double-right"></i><span>read more</span>
			</a>

		</div>


	</div>

PostPanelBox;

	echo $postPanelBox;
}

echo '</div>';

echo ($totalPages > 1 ) ? uc_get_pagination_markup($currentPage, $totalPages) : null;