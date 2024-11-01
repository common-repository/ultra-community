<?php
use UltraCommunity\Entities\UserPrivacyEntity;
use UltraCommunity\Controllers\UserController;

defined('ABSPATH')  || exit;

$profileVisibilityText = esc_html__('Profile Visibility', 'ultra-community');
$sectionTitle          = esc_html__('Account Privacy', 'ultra-community');


$userPrivacyEntity = new UserPrivacyEntity(UserController::getProfiledUser());

$arrSingleMetaOptions = array(
	UserPrivacyEntity::META_KEY_HIDE_ONLINE_STATUS  => esc_html__('Hide my online status', 'ultra-community'),
	UserPrivacyEntity::META_KEY_HIDE_IN_SEARCHES    => esc_html__('Hide my profile in searches', 'ultra-community'),
	UserPrivacyEntity::META_KEY_HIDE_IN_DIRECTORIES => esc_html__('Hide my profile in directories', 'ultra-community'),
);

$profileVisibilityOptionsOutput = null;
foreach(UserPrivacyEntity::getProfileVisibilityOptions() as $optionValue => $description)
{
	$selected = $optionValue === $userPrivacyEntity->getProfileVisibilityValue() ? 'selected = "selected"' : null;

	$profileVisibilityOptionsOutput .= "<option value = \"$optionValue\" $selected >$description</option>";
}


$singleMetaOptionsOutput = null;
foreach($arrSingleMetaOptions as $metaKey => $labelText)
{
	$checkedAttribute = null;

	switch($metaKey)
	{
		case UserPrivacyEntity::META_KEY_HIDE_IN_SEARCHES    : $userPrivacyEntity->showProfileInSearches()    ?: $checkedAttribute = 1; break;
		case UserPrivacyEntity::META_KEY_HIDE_ONLINE_STATUS  : $userPrivacyEntity->showOnlineStatus()         ?: $checkedAttribute = 1; break;
		case UserPrivacyEntity::META_KEY_HIDE_IN_DIRECTORIES : $userPrivacyEntity->showProfileInDirectories() ?: $checkedAttribute = 1; break;
	}

	empty($checkedAttribute) ?: $checkedAttribute = 'checked = "checked"';

	$singleMetaOptionsOutput .= <<<Output
		<div class="uc-form-grouped-controls uc-grid-cell uc-no-border">
			<label class="uc-checkbox-holder">
				<input type="checkbox" name="userMetaPrivacy[$metaKey]" $checkedAttribute >
				<span class="uc-checkbox"></span>
				<span class="uc-checkbox-text">$labelText</span>
			</label>
		</div>
Output;


}

echo <<<Output

<div class="uc-user-settings-account-change-password">

	<div class="uc-grid uc-grid--full uc-grid--flex-cells uc-panel">

		<h3 class="uc-grid-cell uc-grid uc-grid--center uc-grid--fit uc-grid--flex-cells uc-panel-head">
			<span class="uc-grid-cell uc-grid--center uc-grid--justify-center uc-grid-cell--autoSize"><i class="fa fa-user-secret"></i></span>
			<span class="uc-grid-cell">$sectionTitle</span>
		</h3>


		<form id="uc-user-settings-account-privacy-form" autocomplete="off" class="uc-grid-cell uc-grid-cell--center uc-panel-content uc-form uc-settings-form" method="post">

			<div class="uc-form-body">

				<div class="uc-grid uc-grid--center uc-grid--fit">
					<label for="uc-user-current-password">$profileVisibilityText</label>

					<div class="uc-form-grouped-controls uc-grid-cell">
						<select name="selectProfileVisibility" class="uc-input-1 uc-nice-select uc-nice-select-wide">
						$profileVisibilityOptionsOutput
						</select>
					</div>
				</div>

				$singleMetaOptionsOutput
			</div>


			<div class="uc-form-footer">
				<div class="uc-grid uc-grid--fit uc-grid--flex-cells">
					<div class="uc-grid-cell uc-grid-cell--autoSize ">
						<button class="uc-button uc-button-primary">Save Changes</button>
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



Output;


return;

?>



<div class="uc-user-settings-account-change-password">

	<div class="uc-grid uc-grid--full uc-grid--flex-cells uc-panel">

		<h3 class="uc-grid-cell uc-grid uc-grid--center uc-grid--fit uc-grid--flex-cells uc-panel-head">
			<span class="uc-grid-cell uc-grid--center uc-grid--justify-center uc-grid-cell--autoSize"><i class="fa fa-user-secret"></i></span>
			<span class="uc-grid-cell">Account Privacy</span>
		</h3>


		<form id="uc-user-settings-account-privacy-form" autocomplete="off" class="uc-grid-cell uc-grid-cell--center uc-panel-content uc-form uc-settings-form" method="post">

			<div class="uc-form-body">

				<div class="uc-grid uc-grid--center uc-grid--fit">
					<label for="uc-user-current-password">Profile Visibility</label>

					<div class="uc-form-grouped-controls uc-grid-cell">
						<select name="selectProfileVisibility" class="uc-input-1 uc-nice-select uc-nice-select-wide" style="">
							<option value="1">Val1</option>
							<option value="2">Val2</option>
							<option value="3">Val3</option>
						</select>
					</div>
				</div>

				<div class="uc-form-grouped-controls uc-grid-cell uc-no-border">

					<label class="uc-checkbox-holder">
						<input name="userPrivacy[test1]" type="checkbox" checked = "checked"><span class="uc-checkbox"></span><span class="uc-checkbox-text">Hide my online status</span>
					</label>

				</div>


				<div class="uc-form-grouped-controls uc-grid-cell uc-no-border">

					<label class="uc-checkbox-holder">
						<input name="userPrivacy[test2]" type="checkbox" checked = "checked"><span class="uc-checkbox"></span>
						<span class="uc-checkbox-text">Hide my profile in searches</span>
					</label>

				</div>

				<div class="uc-form-grouped-controls uc-grid-cell uc-no-border">

					<label class="uc-checkbox-holder">
						<input name="userPrivacy[test3]" type="checkbox" checked = "checked">
						<span class="uc-checkbox"></span>
						<span class="uc-checkbox-text">Hide my profile in directories</span>
					</label>

				</div>

			</div>


			<div class="uc-form-footer">
				<div class="uc-grid uc-grid--fit uc-grid--flex-cells">
					<div class="uc-grid-cell uc-grid-cell--autoSize ">
						<button class="uc-button uc-button-primary">Save Changes</button>
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
