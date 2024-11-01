<?php

use UltraCommunity\Controllers\GroupController;
use UltraCommunity\Controllers\UserController;
use UltraCommunity\Entities\GroupEntity;

defined('ABSPATH')  || exit;

isset($editableGroupEntity) ?: $editableGroupEntity = new GroupEntity();

$editableGroupEntity->escapeFields();

$sectionTitle     = empty($editableGroupEntity->Id) ? esc_html__('Create New Group', 'ultra-community') :  esc_html__('Group Settings', 'ultra-community');
$submitButtonText = empty($editableGroupEntity->Id) ? esc_html__('Create Group', 'ultra-community')     :  esc_html__('Save Changes', 'ultra-community');

?>


<div class="uc-user-settings-groups-edit-group">

	<div class="uc-grid uc-grid--full uc-grid--justify-center uc-grid--flex-cells uc-panel">

		<h3 class="uc-grid-cell uc-grid uc-grid--center uc-grid--fit uc-grid--flex-cells uc-panel-head">
			<span class="uc-grid-cell uc-grid--center uc-grid--justify-center uc-grid-cell--autoSize"><i class="fa fa-cog"></i></span>
			<span class="uc-grid-cell"><?php echo $sectionTitle; ?></span>
		</h3>

		<div class="uc-grid-cell uc-panel-content">


			<form id = "uc-user-settings-group-edit-group-form" autocomplete = "off" class="uc-grid-cell uc-grid-cell--center uc-panel-content uc-form uc-settings-form" method="post">

				<input type="hidden" name="uc-Id" value="<?php echo $editableGroupEntity->Id;?>">

				<div class="uc-form-body">


					<div class="uc-grid uc-grid--center uc-grid--fit">
						<label for="uc-Name"><?php esc_html_e('Group Name', 'ultra-community');?></label>
						<div class="uc-form-grouped-controls uc-grid-cell">
							<input type="text" id="uc-Name" name="uc-Name" class="uc-input-1" value="<?php echo $editableGroupEntity->Name;?>">
						</div>
					</div>


					<div class="uc-grid uc-grid--center uc-grid--fit">
						<label for="uc-GroupTypeId"><?php esc_html_e('Group Type', 'ultra-community');?></label>
						<div class="uc-form-grouped-controls uc-grid-cell">
							<select id = "uc-GroupTypeId" name = "uc-GroupTypeId" class="uc-input-1">
								<?php
								foreach (uc_get_all_group_types() as $groupTypeId => $typeDescription)
								{
									$format = '<option selected = "selected" value = "%d">%s</option>';
									$groupTypeId == $editableGroupEntity->GroupTypeId ?: $format = str_replace('selected = "selected"', '', $format);

									echo sprintf($format, esc_attr($groupTypeId), esc_html($typeDescription));
								}
								?>
							</select>
						</div>
					</div>



					<div class="uc-grid uc-grid--center uc-grid--fit">
						<label for="uc-Description"><?php esc_html_e('Group Description', 'ultra-community');?></label>
						<div class="uc-form-grouped-controls uc-grid-cell">
							<textarea name="uc-Description" id = "uc-Description" class="uc-input-1"><?php echo $editableGroupEntity->Description;?></textarea>
						</div>
					</div>




					<?php if(GroupController::userCanControlGroupPrivacy(UserController::getLoggedInUser())) { ?>


						<div class="uc-grid uc-grid--center uc-grid--fit">
							<div style="" class="uc-section-divider">
								<span style="" class="uc-vertical-align-middle"><?php esc_html_e('Group Privacy', 'ultra-community');?></span>
							</div>
						</div>



						<div class="uc-grid uc-grid--center uc-grid--fit">
							<label for="uc-GroupPrivacyActivityPost" style="width: 300px; max-width: 300px; margin-right: 1em"><?php esc_html_e('Who can post on group activity?', 'ultra-community');?></label>
							<div class="uc-form-grouped-controls uc-grid-cell">
								<select id = "uc-GroupPrivacyActivityPost" name = "uc-GroupPrivacyActivityPost" class="uc-input-1">
									<?php
									foreach (uc_get_group_privacy_activity_posting_types() as $id => $description)
									{
										$format = '<option selected = "selected" value = "%d">%s</option>';
										$id == $editableGroupEntity->GroupPrivacyActivityPost ?: $format = str_replace('selected = "selected"', '', $format);

										echo sprintf($format, esc_attr($id), esc_html($description));
									}
									?>
								</select>
							</div>
						</div>


						<div class="uc-grid uc-grid--center uc-grid--fit">
							<label for="uc-GroupPrivacyActivityComment" style="width: 300px; max-width: 300px; margin-right: 1em"><?php esc_html_e('Who can comment on group activity?', 'ultra-community');?></label>
							<div class="uc-form-grouped-controls uc-grid-cell">
								<select name = "uc-GroupPrivacyActivityComment" class="uc-input-1">
									<?php
									foreach (uc_get_group_privacy_activity_commenting_types() as $id => $description)
									{
										$format = '<option selected = "selected" value = "%d">%s</option>';
										$id == $editableGroupEntity->GroupPrivacyActivityComment ?: $format = str_replace('selected = "selected"', '', $format);

										echo sprintf($format, esc_attr($id), esc_html($description));
									}
									?>
								</select>
							</div>
						</div>



					<?php } ?>


				</div>



				<div class="uc-form-footer">
					<div class="uc-grid uc-grid--fit uc-grid--flex-cells">
						<div class="uc-grid-cell uc-grid-cell--autoSize ">
							<button class="uc-button uc-button-primary"><?php echo $submitButtonText;?></button>
						</div>
						<div class="uc-grid-cell uc-grid-cell--center uc-grid-cell--autoSize ">
							<div class="uc-ajax-loader uc-hidden">
								<div class="bounce-left"></div>
								<div class="bounce-middle"></div>
								<div class="bounce-right"></div>
							</div>
						</div>
						<div class="uc-grid-cell uc-notice-holder"></div>
					</div>

				</div>


			</form>

		</div>



	</div>

</div>

<div style="display: none !important;">
	<div id="uc-group-type-help">

		<table class="uc-table uc-table-bordered uc-table-striped">
			<caption><strong><?php esc_html_e('Groups Privacy Type', 'ultra-community');?></strong></caption>
			<thead>
			<tr>
				<th style="width:25%"></th>
				<th><strong><?php esc_html_e('Public', 'ultra-community');?></strong></th>
				<th><strong><?php esc_html_e('Private', 'ultra-community');?></strong></th>
				<th><strong><?php esc_html_e('Secret', 'ultra-community');?></strong></th>
			</tr>
			</thead>

			<tbody>
			<tr>
				<td><?php esc_html_e('Who can join?', 'ultra-community');?></td>
				<td><?php esc_html_e('Anyone can join or be added or invited by a member', 'ultra-community');?></td>
				<td><?php esc_html_e('Anyone can ask to join or be added or invited by a member', 'ultra-community');?></td>
				<td><?php esc_html_e('Anyone, but they have to be added or invited by a member', 'ultra-community');?></td>
			</tr>
			<tr>
				<td><?php esc_html_e('Who can see the group\'s name?', 'ultra-community');?></td>
				<td><?php esc_html_e('Anyone', 'ultra-community');?></td>
				<td><?php esc_html_e('Anyone', 'ultra-community');?></td>
				<td><?php esc_html_e('Group members and site administrators', 'ultra-community');?></td>
			</tr>
			<tr>
				<td><?php esc_html_e('Who can see who\'s in the group?', 'ultra-community');?></td>
				<td><?php esc_html_e('Anyone', 'ultra-community');?></td>
				<td><?php esc_html_e('Anyone', 'ultra-community');?></td>
				<td><?php esc_html_e('Group members and site administrators', 'ultra-community');?></td>
			</tr>
			<tr>
				<td><?php esc_html_e('Who can see what members post in the group?', 'ultra-community');?></td>
				<td><?php esc_html_e('Anyone', 'ultra-community');?></td>
				<td><?php esc_html_e('Group members and site administrators', 'ultra-community');?></td>
				<td><?php esc_html_e('Group members and site administrators', 'ultra-community');?></td>
			</tr>
			<tr>
				<td><?php esc_html_e('Who can find the group in groups directory?', 'ultra-community');?></td>
				<td><?php esc_html_e('Anyone', 'ultra-community');?></td>
				<td><?php esc_html_e('Anyone', 'ultra-community');?></td>
				<td><?php esc_html_e('Group members and site administrators', 'ultra-community');?></td>
			</tr>

			</tbody>
		</table>


	</div>
</div>





