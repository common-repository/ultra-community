<?php
use UltraCommunity\Entities\PageActionEntity;
use UltraCommunity\UltraCommHelper;
use UltraCommunity\Controllers\UserController;
use UltraCommunity\Controllers\GroupController;
use UltraCommunity\Entities\GroupUserEntity;

defined('ABSPATH')  || exit;

if(empty($groupKey))
	return;

$currentPage = empty($currentPage) ? 0 : (int)$currentPage;

$groupEntity = GroupController::getGroupEntityBy($groupKey);

/**
 * @var $arrGroupMembers UltraCommunity\Entities\GroupUserEntity[]
 */
$arrGroupMembers = array_merge(GroupController::getGroupUsers($groupEntity, 1, 50, GroupUserEntity::GROUP_USER_STATUS_PENDING), GroupController::getGroupUsers($groupEntity, $currentPage, 15));
$arrGroupMembers = array_unique( $arrGroupMembers, SORT_REGULAR );

$membersListOutput = null;

foreach($arrGroupMembers as $groupUserEntity)
{
	if(null === ($userEntity = UserController::getUserEntityBy($groupUserEntity->UserId)))
		continue;

	$userEntity->Description = UltraCommHelper::getUserShortDescription($userEntity);
	$userEntity->AvatarUrl   = UltraCommHelper::getUserAvatarUrl($userEntity);
	$userEntity->ProfileUrl  = UltraCommHelper::getUserProfileUrl($userEntity);
	$userEntity->DisplayName = UltraCommHelper::getUserDisplayName($userEntity);
	$userEntity->JoinedDate  = mysql2date('M j, Y', $groupUserEntity->JoinedDate);

	$userEntity->Actions = array();

	if($groupUserEntity->UserStatusId === GroupUserEntity::GROUP_USER_STATUS_PENDING)
	{
		$userEntity->Actions[] =  new PageActionEntity($groupUserEntity->UserId, PageActionEntity::TYPE_GROUP_ACCEPT_USER_JOIN_REQUEST_FLAT_BUTTON);
		$userEntity->Actions[] =  new PageActionEntity($groupUserEntity->UserId, PageActionEntity::TYPE_GROUP_DECLINE_USER_JOIN_REQUEST_FLAT_BUTTON);

	}
	elseif(!GroupController::userCanEditGroup($groupUserEntity->UserId, $groupEntity))
	{
		if($groupUserEntity->UserStatusId === GroupUserEntity::GROUP_USER_STATUS_BLOCKED)
		{
			$pageAction = new PageActionEntity($groupUserEntity->UserId, PageActionEntity::TYPE_GROUP_UNBLOCK_USER_ICON_BUTTON);
			$pageAction->ToolTipText = esc_html__('Unblock User', 'ultra-community');
			$userEntity->Actions[] = $pageAction;
		}
		else
		{
			$pageAction = new PageActionEntity($groupUserEntity->UserId, PageActionEntity::TYPE_GROUP_BLOCK_USER_ICON_BUTTON);
			$pageAction->ToolTipText = esc_html__('Block User', 'ultra-community');
			$userEntity->Actions[] = $pageAction;
		}

		$pageAction = new PageActionEntity($groupUserEntity->UserId, PageActionEntity::TYPE_GROUP_DELETE_USER_ICON_BUTTON);
		$pageAction->ToolTipText = esc_html__('Delete User', 'ultra-community');
		$userEntity->Actions[] = $pageAction;

	}

	$actionsOutput = null;
	foreach ($userEntity->Actions as $pageAction)
    {
	    //$actionsOutput .= (empty($pageAction->ToolTipText)) ? '<li>' : '<li uc-tooltip-flow="up" uc-tooltip="' .$pageAction->ToolTipText . '">';
	    $actionsOutput .= '<li>';
	    $actionsOutput .= $pageAction->getOutputContent() . '</li>';
    }

	$actionsOutput = '<ul class="uc-grid-cell uc-grid-cell--center uc-grid-cell--autoSize uc-grid uc-grid--fit uc-group-member-actions">' . $actionsOutput . '</ul>';

	$membersListOutput .= <<<MemberOutput

<div class="uc-grid uc-grid--full uc-grid--flex-cells uc-panel uc-group-member-holder">
	<div class="uc-grid-cell uc-grid-cell--center uc-grid uc-grid--fit uc-panel-head">

		<div class="uc-grid-cell uc-grid-cell--autoSize uc-user-avatar-holder">
			<a href="{$userEntity->ProfileUrl}" style="background-image:url($userEntity->AvatarUrl)"></a>
		</div>

		<div class="uc-grid-cell uc-group-member-info">
			<a href="{$userEntity->ProfileUrl}">{$userEntity->DisplayName}</a>
			<span><i class="fa fa-clock-o"></i>{$userEntity->JoinedDate}</span>
		</div>

        $actionsOutput
	</div>

	<p class="uc-grid-cell uc-grid-cell--center uc-panel-content">$userEntity->Description</p>

</div>

MemberOutput;

}

echo '<div class="uc-user-settings-groups-members-section">', $membersListOutput, '</div>';
