<?php

$currentPage     = empty($currentPage)  ? 1 : (int)$currentPage;
$totalPages      = empty($totalPages)   ? 0 : (int)$totalPages;
$arrGroupMembers = empty($arrGroupMembers) ? array() : (array)$arrGroupMembers;


echo '<div class="uc-group-members-section">';

foreach($arrGroupMembers as $groupMember)
{
	!empty($groupMember->Actions) ?: $groupMember->Actions = array();
	$actionsListsOutput = null;

	foreach($groupMember->Actions as $userAction){
		$actionsListsOutput .= '<li>' . $userAction->getOutputContent() . '</li>';
	}

	echo <<<GroupMemberOutput

<div class="uc-grid uc-grid--full uc-grid--flex-cells uc-panel uc-group-member-holder">
	<div class="uc-grid-cell uc-grid-cell--center uc-grid uc-grid--fit uc-panel-head">

		<div class="uc-grid-cell uc-grid-cell--autoSize uc-user-avatar-holder">
			<a href="{$groupMember->ProfileUrl}" style="background-image:url({$groupMember->AvatarUrl})"></a>
		</div>

		<div class="uc-grid-cell uc-group-member-info">
			<a href="{$groupMember->ProfileUrl}">{$groupMember->DisplayName}</a>
			<span><i class="fa fa-clock-o"></i>{$groupMember->JoinedDate}</span>
		</div>

		<ul class="uc-grid-cell uc-grid-cell--center uc-grid-cell--autoSize uc-grid uc-grid--fit uc-group-member-actions">
			$actionsListsOutput
		</ul>

	</div>

		<p class="uc-grid-cell uc-grid-cell--center uc-panel-content">{$groupMember->Description}</p>

</div>
GroupMemberOutput;

}

echo '</div>';

echo ($totalPages > 1 ) ? uc_get_pagination_markup($currentPage, $totalPages) : null;