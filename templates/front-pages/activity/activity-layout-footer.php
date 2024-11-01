<?php
/** @var $activityEntity UltraCommunity\Entities\ActivityEntity */
use UltraCommunity\Controllers\UserController;
use UltraCommunity\UltraCommHelper;
use UltraCommunity\UltraCommHooks;

if(!isset($activityEntity))
	return;




$activityEntity->FooterActions = empty($activityEntity->FooterActions) ? array() : (array)$activityEntity->FooterActions;

$activityActionsOutput = null;


foreach ($activityEntity->FooterActions as $actionKey => $arrActionInfo) {
 
	$actionKey = ucfirst(sanitize_key($actionKey));
	
	$activityEntity->ActivityId = (int)$activityEntity->ActivityId;
	$arrActionInfo['class']     = empty($arrActionInfo['class']) ? '' : sanitize_html_class($arrActionInfo['class']);
	
	$arrActionInfo['url'] = empty($arrActionInfo['url']) ? '' : esc_url($arrActionInfo['url']);
	
	$triggerLoginAttribute = empty($arrActionInfo['triggerLogIn']) ? '' : "data-uc-trigger-login = \"activityFooterAction$actionKey\"";
	
	$activityActionsOutput .= <<<FooterAction

    <li>
        <a class="{$arrActionInfo['class']}" href="{$arrActionInfo['url']}" {$triggerLoginAttribute} data-uc-target-id = "{$activityEntity->ActivityId}">
            <i class="{$arrActionInfo['icon']}"></i><span>{$arrActionInfo['text']}</span>
        </a>
    </li>

FooterAction;
}




echo '<ul class="uc-grid-cell uc-activity-footer-actions">', $activityActionsOutput, '</ul>';

do_action(UltraCommHooks::ACTION_AFTER_ACTIVITY_FOOTER_ACTIONS);


$totalLikes = empty($activityEntity->MetaData->UserLikes) ? 0 : count($activityEntity->MetaData->UserLikes);
$activityLikesOutput = null;
if($totalLikes > 0)
{
    $likesCounter = 0;
    foreach ($activityEntity->MetaData->UserLikes as $userId => $liked)
    {
    	
    	
        $userEntity = UserController::getUserEntityBy($userId);
        
	    $userProfileUrl  = UltraCommHelper::getUserProfileUrl($userEntity);
	    $userAvatarUrl   = UltraCommHelper::getUserAvatarUrl($userEntity);
	    $userDisplayName = UltraCommHelper::getUserDisplayName($userEntity);
	    
	    $activityLikesOutput .= <<<UserInfo
                <a href = "$userProfileUrl" class="uc-bg-holder uc-circle-border" uc-tooltip="$userDisplayName" uc-tooltip-flow="up"  style="background-image:url($userAvatarUrl)"></a>
UserInfo;
        
        if(++$likesCounter > 2)
            break;
    }
 
    if(($plusCounter = $totalLikes - $likesCounter) > 0)
    {
        $viewAllText = esc_html__('View all', 'ultra-community');
	    $activityLikesOutput .= <<<UserInfo
                <a href = "#" class="uc-circle-border uc-activity-footer-all-people" uc-tooltip="$viewAllText" uc-tooltip-flow="up">
                    <span>+{$plusCounter}</span>
                </a>
UserInfo;
    
    }
    
    $activityLikesOutput .= '<p>' . esc_html__('liked this', 'ultra-community') . '</p>';
}

echo '<div class="uc-grid-cell uc-grid-cell--autoSize uc-grid--center uc-activity-footer-likes-list">', $activityLikesOutput, '</div>';

