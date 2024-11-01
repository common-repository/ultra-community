<?php
use UltraCommunity\Modules\Forms\FormFields\BaseField;
use UltraCommunity\UltraCommHooks;
defined('ABSPATH') || exit;

isset($headerTitle)         ?: $headerTitle = '';
isset($additionalClasses)   ?: $additionalClasses = '';
isset($arrFormFields)       ?: $arrFormFields = array();
isset($submitButtonText)    ?: $submitButtonText = '';

if(empty($showResetPasswordForm)) // Forgot Password Form
{
	echo "<form autocomplete = \"off\" method=\"post\" class=\"uc-form uc-panel uc-form-holder uc-forgot-pass-form $additionalClasses\">";

	echo '<div class="uc-form-header">' . '<p><span>' . $headerTitle . '</span></p>' . '</div>';

	echo '<div class="uc-form-body">';
	/** @var $formField UltraCommunity\Modules\Forms\FormFields\BaseField */
	foreach ( (array) $arrFormFields as $formField ) {
		$formField->Type !== BaseField::FIELD_TYPE_PASSWORD ?: $fieldValue = null;

		echo $formField->toHtmlOutput();
	}
	echo '</div>';

	echo '<div class="uc-form-footer uc-grid uc-grid--fit uc-grid--flex-cells">';
	echo '<div class="uc-grid-cell uc-grid-cell--autoSize "><button class="uc-button uc-button-primary">' . $submitButtonText . '</button></div>';
	echo '<div class="uc-grid-cell uc-grid-cell--autoSize uc-grid-cell--center"><div class="uc-ajax-loader uc-hidden"><div class="bounce-left"></div><div class="bounce-middle"></div><div class="bounce-right"></div></div></div>';
	echo '<div class="uc-grid-cell uc-notice-holder"></div>';
	echo '</div>';

	do_action( UltraCommHooks::ACTION_FORGOT_PASSWORD_FORM_BOTTOM );

	echo '</form>';
}
else // Reset Password Form
{
	echo "<form autocomplete = \"off\" method=\"post\" class=\"uc-form uc-panel uc-form-holder uc-forgot-pass-form $additionalClasses\">";

	echo '<div class="uc-form-header">' . '<p><span>' . $headerTitle . '</span></p>' . '</div>';

	echo '<div class="uc-form-body">';


		echo '<label for="txtPassword">'. esc_html__('Your new password', 'ultra-community') .'</label>';
		echo '<div class="uc-form-grouped-controls"><input name="txtPassword" placeholder="" value="" class="" type="password"></div>';

		echo '<label for="txtPassword">'. esc_html__('Confirm password', 'ultra-community') .'</label>';
		echo '<div class="uc-form-grouped-controls"><input name="txtConfirmPassword" placeholder="" value="" class="" type="password"></div>';


	echo '</div>';

	echo '<div class="uc-form-footer uc-grid uc-grid--fit uc-grid--flex-cells">';
	echo '<div class="uc-grid-cell uc-grid-cell--autoSize "><button class="uc-button uc-button-primary">' . $submitButtonText . '</button></div>';
	echo '<div class="uc-grid-cell uc-grid-cell--autoSize uc-grid-cell--center"><div class="uc-ajax-loader uc-hidden"><div class="bounce-left"></div><div class="bounce-middle"></div><div class="bounce-right"></div></div></div>';
	echo '<div class="uc-grid-cell uc-notice-holder"></div>';
	echo '</div>';

	do_action( UltraCommHooks::ACTION_FORGOT_PASSWORD_FORM_BOTTOM );

	echo '</form>';

}
?>


