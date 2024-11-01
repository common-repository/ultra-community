<?php
/** @var $activityEntity UltraCommunity\Entities\ActivityEntity */
use UltraCommunity\Controllers\UserController;
use UltraCommunity\UltraCommHelper;


if(!isset($activityEntity))
	return;

isset($activityEntity->UserEntity) ?: $activityEntity->UserEntity = UserController::getUserEntityBy($activityEntity->UserId);

$profileCoverUrl = UltraCommHelper::getUserProfileCoverUrl($activityEntity->UserEntity);
$userAvatarUrl   = UltraCommHelper::getUserAvatarUrl($activityEntity->UserEntity);
$userProfileUrl  = UltraCommHelper::getUserProfileUrl($activityEntity->UserEntity);
$userDisplayName = UltraCommHelper::getUserDisplayName($activityEntity->UserEntity);
$userNickName    = "@{$activityEntity->UserEntity->NiceName}";


echo <<<OutputContent

<div class = "uc-grid-cell">

	<div class="uc-grid uc-bg-holder activity-content-with-cover" style="background-image: url($profileCoverUrl)">
		
		<div class="uc-grid-cell uc-grid  uc-grid--full uc-grid-medium--fit uc-grid--flex-cells uc-grid--justify-center uc-activity-inner-holder">
			
			<a href="$userProfileUrl" class="uc-grid-cell uc-grid-cell--autoSize uc-bg-holder activity-content-avatar" style="background-image: url($userAvatarUrl)"></a>
			
			<div class="uc-grid-cell uc-grid-cell--bottom uc-activity-content-info-holder">
				
				<div class="uc-grid uc-grid--full uc-grid-medium--fit uc-grid--flex-cells uc-grid--justify-center">
					
					<div class="uc-grid-cell uc-activity-content-meta">
						<a href="$userProfileUrl">$userDisplayName</a>
						<p><span>$userNickName</span></p>
					</div>
					
					<div class="uc-grid-cell uc-grid-cell--autoSize uc-activity-content-actions">
<!--						<button style="padding: 10px 30px">follow</button>-->
					</div>
				
				</div>
			
			</div>
		</div>
	</div>


</div>

OutputContent;

