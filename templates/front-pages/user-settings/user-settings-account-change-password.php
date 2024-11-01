<?php

defined('ABSPATH')  || exit;

?>




<div class="uc-user-settings-account-change-password">

	<div class="uc-grid uc-grid--full uc-grid--flex-cells uc-panel">

		<h3 class="uc-grid-cell uc-grid uc-grid--center uc-grid--fit uc-grid--flex-cells uc-panel-head">
			<span class="uc-grid-cell uc-grid--center uc-grid--justify-center uc-grid-cell--autoSize"><i class="fa fa-lock"></i></span>
			<span class="uc-grid-cell"><?php esc_html_e('Change Password', 'ultra-community');?></span>
		</h3>


		<form id = "uc-user-settings-account-change-password-form" autocomplete="off" class="uc-grid-cell uc-grid-cell--center uc-panel-content uc-form uc-settings-form" method="post">

			<div class="uc-form-body">

				<div class="uc-grid uc-grid--center uc-grid--fit">
					<label for="uc-user-current-password"><?php esc_html_e('Current Password', 'ultra-community' ); ?></label>

					<div class="uc-form-grouped-controls uc-grid-cell">
						<input type="password" name="uc-user-current-password" class="uc-input-1" placeholder="<?php esc_html_e('type your current password', 'ultra-community' ); ?>">
					</div>
				</div>

				<div class="uc-grid uc-grid--center uc-grid--fit">
					<label for="uc-user-new-password"><?php esc_html_e('New Password', 'ultra-community');?></label>

					<div class="uc-form-grouped-controls uc-grid-cell">
						<input type="password" name="uc-user-new-password" class="uc-input-1" placeholder="<?php esc_html_e('type your new password', 'ultra-community');?>">
					</div>
				</div>

				<div class="uc-grid uc-grid--center uc-grid--fit">
					<label for="uc-user-confirm-password"><?php esc_html_e('Confirm Password', 'ultra-community');?></label>

					<div class="uc-form-grouped-controls uc-grid-cell">
						<input type="password" name="uc-user-confirm-password" class="uc-input-1" placeholder="<?php esc_html_e('confirm your new password', 'ultra-community');?>">
					</div>
				</div>

			</div>


			<div class="uc-form-footer">
				<div class="uc-grid uc-grid--fit uc-grid--flex-cells">
					<div class="uc-grid-cell uc-grid-cell--autoSize ">
						<button class="uc-button uc-button-primary"><?php esc_html_e('Change Password', 'ultra-community');?></button>
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
