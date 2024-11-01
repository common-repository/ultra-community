<?php
use UltraCommunity\Controllers\UserController;
use UltraCommunity\Controllers\GroupController;
use UltraCommunity\UltraCommHelper;
use UltraCommunity\Entities\GroupEntity;

/** @var $activityEntity UltraCommunity\Entities\ActivityEntity */
if(!isset($activityEntity))
	return;

isset($activityEntity->UserEntity)  ?: $activityEntity->UserEntity = UserController::getUserEntityBy($activityEntity->UserId);
isset($groupEntity) ?: $groupEntity = \UltraCommunity\Controllers\GroupController::getGroupEntityBy($activityEntity->TargetId);

if(empty($groupEntity))
	return;

$profileCoverUrl = UltraCommHelper::getGroupCoverUrl($groupEntity);
$userAvatarUrl   = UltraCommHelper::getGroupPictureUrl($groupEntity);
$userProfileUrl  = UltraCommHelper::getGroupUrl($groupEntity);
$userDisplayName = esc_html($groupEntity->Name);

$groupTypeDescription = '<i class = "fa ' . GroupEntity::getGroupTypeIconClass($groupEntity->GroupTypeId) . '"></i> ' . GroupEntity::getGroupTypeDescription($groupEntity->GroupTypeId) . esc_html__(' Group', 'ultra-community');


$groupActionsOutput = null;
$groupActions = array_slice(GroupController::getGroupUserPossibleActions($groupEntity, UserController::getLoggedInUser()), 0, 1);

foreach($groupActions as $actionEntity)
{
	$groupActionsOutput .= $actionEntity->getOutputContent();
}

echo <<<OutputContent

<div class = "uc-grid-cell">

	<div class="uc-grid uc-bg-holder activity-content-with-cover" style="background-image: url($profileCoverUrl)">

		<div class="uc-grid-cell uc-grid  uc-grid--full uc-grid-medium--fit uc-grid--flex-cells uc-grid--justify-center uc-activity-inner-holder">

			<a href="$userProfileUrl" class="uc-grid-cell uc-grid-cell--autoSize uc-bg-holder activity-content-avatar" style="background-image: url($userAvatarUrl)"></a>

			<div class="uc-grid-cell uc-grid-cell--bottom uc-activity-content-info-holder">

				<div class="uc-grid uc-grid--full uc-grid-medium--fit uc-grid--flex-cells uc-grid--justify-center">

					<div class="uc-grid-cell uc-activity-content-meta">
						<a href="$userProfileUrl">$userDisplayName</a>
						<p><span>$groupTypeDescription</span></p>
					</div>

					<div class="uc-grid-cell uc-grid-cell--center uc-grid-cell--autoSize uc-activity-content-actions">
					$groupActionsOutput
					</div>

				</div>

			</div>
		</div>
	</div>


</div>

OutputContent;
