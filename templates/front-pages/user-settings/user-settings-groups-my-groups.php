<?php
defined('ABSPATH')  || exit;

/**
 * @var $arrUserGroups \UltraCommunity\Entities\GroupEntity[]
 */
$arrUserGroups = empty($arrUserGroups) ? array() : (array)$arrUserGroups;

$userGroupsListsOutput = null;
$manageButtonText = esc_html__('Manage', 'ultra community');
$groupMembersTextPlural   = esc_html__('members', 'ultra community');
$groupMembersTextSingular = esc_html__('member', 'ultra community');

foreach ($arrUserGroups as $groupEntity)
{
    $membersText = $groupEntity->MembersCount > 1 ? $groupMembersTextPlural : $groupMembersTextSingular;

	$userGroupsListsOutput .= <<<UserGroupInfoOutput
	<div class="uc-grid uc-grid--full uc-grid--flex-cells uc-panel uc-user-group-holder">

		<div class="uc-grid-cell uc-grid-cell--center uc-grid uc-grid--fit uc-panel-head">

			<div class="uc-grid-cell uc-grid-cell--autoSize uc-group-avatar-holder">
				<a href="{$groupEntity->Url}" style="background-image:url($groupEntity->PictureUrl)"></a>
			</div>

			<div class="uc-grid-cell uc-user-group-info">
				<a href="{$groupEntity->Url}">$groupEntity->Name</a>
				<span><i class="fa fa-user"></i>$groupEntity->MembersCount $membersText</span>
				<span><i class="fa fa-clock-o"></i>$groupEntity->CreatedDate</span>
			</div>

			<div class="uc-grid-cell uc-grid-cell--center uc-grid-cell--autoSize">
				<a class="uc-button uc-button-left-icon" href="{$groupEntity->EditUrl}">
					<i class="fa fa-cog"></i><span>$manageButtonText</span>
				</a>
			</div>
		</div>

		<p class="uc-grid-cell uc-grid-cell--center uc-panel-content">{$groupEntity->Description}</p>

	</div>
UserGroupInfoOutput;

}

echo '<div class="uc-user-settings-my-groups-section">', $userGroupsListsOutput, '</div>';
