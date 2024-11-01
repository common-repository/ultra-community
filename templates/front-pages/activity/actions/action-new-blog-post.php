<?php
/** @var $activityEntity UltraCommunity\Entities\ActivityEntity */
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\Repository\WpPostRepository;
use UltraCommunity\UltraCommUtils;

if(!isset($activityEntity->TargetId))
    return;
if(null === ($postObject = WpPostRepository::findByPostId($activityEntity->TargetId)))
    return;

setup_postdata( $GLOBALS['post'] = $postObject );
$postObject->post_title    = get_the_title();
$postObject->post_excerpt  = wp_trim_words(get_the_excerpt());
$postObject->post_date     = get_the_date( 'F j, Y', $postObject );
//$postObject->comment_count = get_comments_number_text($noComments, $oneComment, $moreComments);
wp_reset_postdata();

$postImageUrl = UltraCommUtils::getPostThumbnailUrl($activityEntity->TargetId);
$postUrl      = get_permalink($activityEntity->TargetId);

if(!empty($postImageUrl))
{
	$postImageUrl = "<a href=\"$postUrl\"><img src=\"$postImageUrl\"></a>";
}

$categoriesList = get_the_category_list( ', ', ' ', $activityEntity->TargetId);
if(!empty($categoriesList))
{
	$categoriesList = '<li><i class="fa fa-tags"></i>' . $categoriesList . '</li>';
}

echo <<<OutputContent


    <div class="uc-grid uc-grid--full activity-content-link activity-content-new-blog-post">

        <div class="uc-grid-cell uc-grid--justify-center activity-content-1-image">$postImageUrl</div>

        <div class="uc-grid-cell activity-content-link-title">
            <h3><a href="$postUrl">{$postObject->post_title}</a></h3>
            <ul class="uc-user-post-meta">
				$categoriesList
				<li><i class="fa fa-clock-o"></i><span>{$postObject->post_date}</span></li>
				<li><i class="fa fa-comments-o"></i><span>{$postObject->comment_count}</span></li>
			</ul>
        </div>
        <div class="uc-grid-cell activity-content-link-descr">
            <p>{$postObject->post_excerpt}</p>
        </div>
    </div>


OutputContent;



?>

