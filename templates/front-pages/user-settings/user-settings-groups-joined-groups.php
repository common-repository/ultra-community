<?php

defined('ABSPATH')  || exit;

/**
 * @var $arrUserJoinedGroups \UltraCommunity\Entities\GroupEntity[]
 */
$arrUserJoinedGroups = empty($arrUserJoinedGroups) ? array() : (array)$arrUserJoinedGroups;

$userGroupsListsOutput = null;
$leaveButtonText = esc_html__('Leave', 'ultra community');
$groupMembersTextPlural   = esc_html__('members', 'ultra community');
$groupMembersTextSingular = esc_html__('member', 'ultra community');

foreach ($arrUserJoinedGroups as $groupEntity)
{
	$membersText = $groupEntity->MembersCount > 1 ? $groupMembersTextPlural : $groupMembersTextSingular;

	$userGroupsListsOutput .= <<<UserGroupInfoOutput
	<div class="uc-grid uc-grid--full uc-grid--flex-cells uc-panel uc-user-group-holder">

		<div class="uc-grid-cell uc-grid-cell--center uc-grid uc-grid--fit uc-panel-head">

			<div class="uc-grid-cell uc-grid-cell--autoSize uc-group-avatar-holder">
				<a href="{$groupEntity->Url}" style="background-image:url($groupEntity->PictureUrl)"></a>
			</div>

			<div class="uc-grid-cell uc-grid-cell--autoSize uc-user-group-info">
				<a href="{$groupEntity->Url}">$groupEntity->Name</a>
				<span><i class="fa fa-user"></i>$groupEntity->MembersCount $membersText</span>
				<span><i class="fa fa-clock-o"></i>$groupEntity->CreatedDate</span>
			</div>

			<div class="uc-grid-cell uc-grid-cell--center uc-notice-holder"></div>

			<div class="uc-grid-cell uc-grid-cell--center uc-grid-cell--autoSize">
				<button class="uc-button uc-button-primary uc-button-left-icon" data-uc-action = "userLeaveGroup" data-group-id = "$groupEntity->Id">
					<i class="fa fa-sign-out"></i><span>$leaveButtonText</span>
				</button>
			</div>

		</div>

		<p class="uc-grid-cell uc-grid-cell--center uc-panel-content">{$groupEntity->Description}</p>

	</div>
UserGroupInfoOutput;

}

echo '<div class="uc-user-settings-my-groups-section">', $userGroupsListsOutput, '</div>';
