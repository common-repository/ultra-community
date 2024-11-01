<?php
defined('ABSPATH') || exit;

$arrUserPosts = empty($arrUserPosts) ? array() : (array)$arrUserPosts;
$widgetTitle  = empty($widgetTitle)  ? esc_html__('Latest Posts', 'ultra-community') : esc_html($widgetTitle);


if(empty($arrUserPosts))
return;

$latestPostsListOutput = '';
foreach ($arrUserPosts as $userPost)
{

	$thumbHolderClass  = 'uc-grid-cell uc-grid-cell--center uc-grid-cell--autoSize';
	$thumbHolderClass .= (empty($userPost->ThumbUrl) ? ' uc-user-post-no-thumb' : '');

	$latestPostsListOutput .= '<li class="uc-grid uc-grid--fit uc-grid--flex-cells">';

        $latestPostsListOutput .= "<div class=\"$thumbHolderClass\">";
            if(empty($userPost->ThumbUrl) && !empty($userPost->Icon))
            {
                $latestPostsListOutput .= "<a class=\"uc-bg-holder\" href=\"{$userPost->Url}\"><i class=\"fa fa-{$userPost->Icon}\"></i></a>";
            }
            else
            {
                $latestPostsListOutput .= "<a class=\"uc-bg-holder\" href=\"{$userPost->Url}\" style=\"background-image:url($userPost->ThumbUrl)\"></a>";
            }
        $latestPostsListOutput .= '</div>';



        $latestPostsListOutput .= '<div class="uc-grid-cell">';
	        $latestPostsListOutput .= "<h2><a href=\"{$userPost->Url}\">{$userPost->Title}</a></h2>";
        	$latestPostsListOutput .= "<p><span><i class=\"fa fa-clock-o\"></i>{$userPost->Date}</span><span><i class=\"fa fa-comments-o\"></i>{$userPost->Comments}</span></p>";
        $latestPostsListOutput .= '</div>';

	$latestPostsListOutput .= '</li>';
}

?>


<div class="uc-grid uc-grid--full uc-grid--center uc-grid--flex-cells uc-panel uc-sidebar-panel uc-latest-posts">

	<h3 class="uc-grid-cell uc-grid uc-grid--center uc-grid--fit uc-grid--flex-cells uc-panel-head">
		<span class="uc-grid-cell uc-grid--center uc-grid--justify-center uc-grid-cell--autoSize"><i class="fa fa-pencil"></i></span>
		<span class="uc-grid-cell"><?php echo $widgetTitle;?></span>
	</h3>

	<div class="uc-grid-cell uc-panel-content">
		<ul><?php echo $latestPostsListOutput;?></ul>
	</div>

	<div class="uc-grid-cell uc-panel-footer"></div>

</div>