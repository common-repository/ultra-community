<?php

defined('ABSPATH')  || exit;

$arrWarningTxt = array(
	esc_html__('Are you sure you want to delete your account?', 'ultra-community'),
	esc_html__('This action is irreversible and cannot be undone! All of your account data will be removed!', 'ultra-community'),
	esc_html__('To delete your account enter your password below!', 'ultra-community'),
);


?>


<div class="uc-user-settings-account-delete">

	<div class="uc-grid uc-grid--full uc-grid--flex-cells uc-panel">

		<h3 class="uc-grid-cell uc-grid uc-grid--center uc-grid--fit uc-grid--flex-cells uc-panel-head">
			<span class="uc-grid-cell uc-grid--center uc-grid--justify-center uc-grid-cell--autoSize"><i class="fa fa-trash"></i></span>
			<span class="uc-grid-cell"><?php esc_html_e('Delete Account', 'ultra-community' ); ?></span>
		</h3>


		<form id = "uc-user-settings-account-delete-form" autocomplete="off" class="uc-grid-cell uc-grid-cell--center uc-panel-content uc-form uc-settings-form" method="post">

			<div class="uc-form-body">

				<div class="uc-grid uc-grid--center uc-grid--full">

					<p class="uc-grid-cell uc-notice uc-notice-warning uc-delete-account-warning"><?php echo implode('<br/>', $arrWarningTxt) ?></p>

				</div>

				<div class="uc-grid uc-grid--center uc-grid--fit">
					<label for="uc-user-current-password"><?php esc_html_e('Current Password', 'ultra-community');?></label>
					<div class="uc-form-grouped-controls uc-grid-cell">
						<input type="password" name="uc-user-current-password" placeholder="type your current password">
					</div>
				</div>

			</div>


			<div class="uc-form-footer">
				<div class="uc-grid uc-grid--fit uc-grid--flex-cells">
					<div class="uc-grid-cell uc-grid-cell--autoSize ">
						<button class="uc-button uc-button-danger"><?php esc_html_e('DELETE ACCOUNT', 'ultra-community');?></button>
					</div>
					<div class="uc-grid-cell uc-grid-cell--autoSize uc-grid-cell--center">
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
