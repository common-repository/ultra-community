<?php

use UltraCommunity\Modules\Forms\FormFields\BaseField;
use UltraCommunity\UltraCommHooks;
defined('ABSPATH') || exit;

isset($headerTitle)         ?: $headerTitle       = '';
isset($additionalClasses)   ?: $additionalClasses = '';
isset($submitButtonText)    ?: $submitButtonText  = '';
isset($arrFormFields)       ?: $arrFormFields     = array();


echo "<form autocomplete = \"off\" method=\"post\" class=\"uc-form uc-register-form uc-panel uc-form-holder $additionalClasses\">";

echo '<div class="uc-form-header">' . '<p><i class="fa fa-sign-in"></i><span>' . $headerTitle . '</span></p>' . '</div>';

echo '<div class="uc-form-body">';
/** @var $formField UltraCommunity\Modules\Forms\FormFields\BaseField */
foreach ((array)$arrFormFields as $formField)
{
    $formField->sanitizeFieldValue();
	$fieldValue = $formField->Value;
	$formField->Type !== BaseField::FIELD_TYPE_PASSWORD ?: $fieldValue = null;

	echo apply_filters(UltraCommHooks::FILTER_FORM_FIELD_OUTPUT_CONTENT, $formField->toHtmlOutput(array(
			'value' => $fieldValue
	)), $formField, $formPublicModuleInstance);

}


echo '</div>';

echo '<div class="uc-form-footer uc-grid uc-grid--fit uc-grid--flex-cells">';
echo '<div class="uc-grid-cell uc-grid-cell--autoSize "><button class="uc-button uc-button-primary">' . $submitButtonText . '</button></div>';
echo '<div class="uc-grid-cell uc-grid-cell--autoSize uc-grid-cell--center"><div class="uc-ajax-loader uc-hidden"><div class="bounce-left"></div><div class="bounce-middle"></div><div class="bounce-right"></div></div></div>';
echo '<div class="uc-grid-cell uc-notice-holder"></div>';
echo '</div>';

do_action(UltraCommHooks::ACTION_REGISTRATION_FORM_BOTTOM);

echo '</form>';
