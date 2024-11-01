<?php
use UltraCommunity\UltraCommHelper;
use UltraCommunity\Controllers\UserController;

defined('ABSPATH')  || exit;

$userCoverPhotoUrl    = UltraCommHelper::getUserProfileCoverUrl(UserController::getProfiledUser());
?>


<div class="uc-user-settings-profile-cover-section">

	<div class="uc-grid uc-grid--full uc-grid--justify-center uc-grid--flex-cells uc-panel">

		<h3 class="uc-grid-cell uc-grid uc-grid--center uc-grid--fit uc-grid--flex-cells uc-panel-head">
			<span class="uc-grid-cell uc-grid--center uc-grid--justify-center uc-grid-cell--autoSize"><i class="fa fa-picture-o"></i></span>
			<span class="uc-grid-cell"><?php esc_html_e('Change Profile Cover', 'ultra-community');?></span>
		</h3>

		<div class="uc-grid-cell uc-grid-cell--center uc-panel-content">

			<div class="uc-grid uc-grid--full uc-grid--justify-center uc-grid--flex-cells">
				<div class="uc-grid-cell uc-grid-cell--autoSize uc-user-cover-holder" style="background-image:url(<?php echo $userCoverPhotoUrl;?>);"></div>
				<div class="uc-grid-cell uc-grid-cell--center uc-grid--justify-center">

					<label for="btnChangeCover" class="uc-button uc-button-primary">
						<input id="btnChangeCover" name="btnChangeCover" value="" accept="image/*" type="file" />
						<i class="fa fa-upload" style="margin-right: 5px"></i><?php esc_html_e('Upload New Picture', 'ultra-community');?>
					</label>

				</div>

				<div class="uc-grid-cell uc-grid-cell--center">
					<div class="uc-picture-holder-wrap uc-hidden" style="width: 100%;  margin: 0 auto;">

						<div id="uc-cover-picture-holder" class=""></div>
						<div>
							<button id="btnSaveCoverPicture" class="uc-button uc-button-primary"><?php esc_html_e('Save Picture', 'ultra-community');?></button>
						</div>

					</div>

				</div>

			</div>

		</div>

		<div class="uc-grid-cell uc-grid-cell--center uc-panel-footer"></div>

	</div>


</div>





