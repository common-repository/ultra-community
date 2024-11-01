<?php
use UltraCommunity\Controllers\FrontPageController;
use UltraCommunity\Controllers\UserController;
use UltraCommunity\Repository\ActivityRepository;
use UltraCommunity\UltraCommHooks;

/** @var $activityEntity UltraCommunity\Entities\ActivityEntity */
if(empty($activityEntity) || !UserController::isUserLoggedIn())
	return;

$userAvatarUrl   = esc_url(uc_get_user_avatar_url(UserController::getLoggedInUser()));
$activityEntity->ActivityId = (int)$activityEntity->ActivityId;

$formId = "uc-comments-root-form-{$activityEntity->ActivityId}";

?>

<form id="<?php echo $formId;?>" class="uc-form uc-activity-comments-form uc-activity-comments-root-form uc-grid uc-grid--fit uc-grid--flex-cells uc-hidden" method="post" autocomplete="off">
    <input type="hidden" name="activityId"  value = "<?php echo $activityEntity->ActivityId;?>" />
    
    <div class="uc-grid-cell uc-grid-cell--autoSize uc-grid-cell--top uc-bg-holder uc-user-avatar-holder" style="background-image: url(<?php echo $userAvatarUrl;?>)"></div>

    <div class="uc-grid-cell uc-grid-cell--top uc-grid uc-grid--full">
        
        <div class="uc-grid-cell">
            <textarea name="txtCommentContent" class="uc-input-1" type="textarea" placeholder="<?php esc_attr_e('Write a Comment...', 'ultra-community'); ?>"></textarea>
        </div>

        <div class="uc-grid-cell uc-grid uc-grid--flex-cells uc-activity-comments-form-footer">
            
            <div class="uc-grid-cell uc-grid-cell--autoSize">
                <button class="uc-button uc-button-primary"><span><?php esc_html_e('Post', 'ultra-community');?></span></button>
            </div>

            <div class="uc-grid-cell uc-grid-cell--center uc-grid-cell--autoSize">
                <div class="uc-ajax-loader uc-hidden"><div class="bounce-left"></div><div class="bounce-middle"></div><div class="bounce-right"></div></div>
            </div>

            <div class="uc-grid-cell uc-notice-holder"></div>

        </div>

    </div>

</form>


