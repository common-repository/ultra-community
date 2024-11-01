<?php
use UltraCommunity\Modules\Forms\FormFields\BaseField;
use UltraCommunity\UltraCommHooks;
defined('ABSPATH') || exit;

isset($headerTitle)         ?: $headerTitle         = '';
isset($additionalClasses)   ?: $additionalClasses   = '';
isset($rememberMeLabel)     ?: $rememberMeLabel     = '';
isset($forgotPasswordLabel) ?: $forgotPasswordLabel = '';
isset($forgotPasswordUrl)   ?: $forgotPasswordUrl   = '';
isset($submitButtonText)    ?: $submitButtonText    = '';
isset($registrationLabel)   ?: $registrationLabel   = '';
isset($registrationUrl)     ?: $registrationUrl     = '';
isset($arrFormFields)       ?: $arrFormFields       = array();


$formNotice = null;
$registrationHolderOutput = null;

empty($errorMessage)   ?: $formNotice = '<p class="uc-notice uc-notice-error">' . $errorMessage . '</p>';
empty($successMessage) ?: $formNotice = '<p class="uc-notice uc-notice-success">' . $successMessage . '</p>';

echo "<form autocomplete = \"off\" method=\"post\" class=\"uc-form uc-login-form uc-panel uc-form-holder $additionalClasses\">";

echo '<div class="uc-form-header">' . '<p><i class="fa fa-sign-in"></i><span>' . $headerTitle . '</span></p>' . '</div>';

echo '<div class="uc-form-body">';
/** @var $formField UltraCommunity\Modules\Forms\FormFields\BaseField */
foreach ((array)$arrFormFields as $formField)
{
	$formField->Type !== BaseField::FIELD_TYPE_PASSWORD ?: $fieldValue = null;
	$fieldValue = null;

	$outputHTML = apply_filters(UltraCommHooks::FILTER_FORM_FIELD_OUTPUT_CONTENT, $formField->toHtmlOutput(array(
		'value' => $fieldValue,
	)), $formField, $loginFormPublicModuleInstance);

	echo $outputHTML;

}

if( !empty($registrationUrl) && !empty($registrationLabel))
{
	$registrationHolderOutput = <<<RegistrationOutput

		<div class="uc-form-grouped-controls uc-no-border uc-login-form-registration">
		        <label class="uc-checkbox-holde">
		            <a href="$registrationUrl">$registrationLabel</a>
		        </label>
		</div>
RegistrationOutput;

}

echo '<div class="uc-form-grouped-controls uc-no-border">

            <label class="uc-checkbox-holder uc-remember-me">
                <input name="rememberme" type="checkbox">
                <span class="uc-checkbox"></span>
                <span class="uc-checkbox-text">', $rememberMeLabel, '</span>
            </label>

            <label class="uc-checkbox-holder uc-forgot-pass">
                <a href="',$forgotPasswordUrl,'">', $forgotPasswordLabel, '</a>
            </label>

       </div>';

echo $registrationHolderOutput;

echo '</div>';



echo '<div class="uc-form-footer uc-grid uc-grid--fit uc-grid--flex-cells">';
echo '<div class="uc-grid-cell uc-grid-cell--autoSize "><button class="uc-button uc-button-primary">' . $submitButtonText . '</button></div>';
echo '<div class="uc-grid-cell uc-grid-cell--autoSize uc-grid-cell--center"><div class="uc-ajax-loader uc-hidden"><div class="bounce-left"></div><div class="bounce-middle"></div><div class="bounce-right"></div></div></div>';
echo '<div class="uc-grid-cell uc-notice-holder">'.$formNotice.'</div>';
echo '</div>';

do_action(UltraCommHooks::ACTION_LOGIN_FORM_BOTTOM);

echo '</form>';
