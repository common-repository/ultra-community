<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Modules;


use UltraCommunity\MchLib\Modules\MchBaseAdminModule;
use UltraCommunity\MchLib\Utils\MchHtmlUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;

abstract class BaseAdminModule extends MchBaseAdminModule
{
	protected function __construct()
	{
		parent::__construct();
	}

	public  function renderModuleSettingsSectionHeader(array $arrSectionInfo)
	{
		//echo '<h3>' . __('WordPress General Settings', '') . '</h3><hr />';
	}

	protected function sanitizeModuleSettings($arrSettingOptions)
	{
		if(empty($arrSettingOptions))
			return $arrSettingOptions;

		$arrDefaultValues = $this->getDefaultOptions();

		foreach($arrSettingOptions as $key => &$value)
		{
			if( ! is_scalar($value) )
				continue;

			if(empty($arrDefaultValues[$key]['InputType'])){
				$value = MchWpUtils::sanitizeText($value);
				continue;
			}

			if($arrDefaultValues[$key]['InputType'] == MchHtmlUtils::FORM_ELEMENT_INPUT_TEXTAREA){

				$arrAllowedHtmlTags =  current_user_can( 'unfiltered_html' ) ?  wp_kses_allowed_html('post') : array();
				$value = wp_kses_stripslashes( $value ) ;
				$value = MchWpUtils::sanitizeTextArea($value, $arrAllowedHtmlTags);

				//$value = MchWpUtils::sanitizeTextArea($value);

				continue;
			}

			$value = MchWpUtils::sanitizeText($value);
		}

		return $arrSettingOptions;

	}


	public function canBeDeleted()
	{
		return true;
	}

	public function saveDefaultOptions($justScalarValues = true)
	{
		foreach($this->getDefaultOptionsValues() as $optionName => $optionValue)
		{
			$canSave = ($justScalarValues ? is_scalar($optionValue) : true);
			
			$canSave = $canSave && (null !==  $optionValue);
			
			!($canSave) ?: $this->saveOption($optionName, $optionValue);

		}

	}

	protected function getFieldAttributesFilterName($settingsField)
	{
		!is_array($settingsField) ?: $settingsField = key($settingsField);
		return $this->getFormOptionFieldName( $settingsField ) . "-attributes";
	}


	public function renderModuleSettingsField(array $arrSettingsField)
	{
		$arrDefaultValues = $this->getDefaultOptionsValues();
		$optionName = key($arrSettingsField);
		if(null === $optionName || !array_key_exists($optionName, $arrDefaultValues))
			return;

		$optionValue = $this->getOption($optionName);

		is_scalar($optionValue) ?: $optionValue = null;

		$arrSettingsField = $arrSettingsField[$optionName];
		$arrFieldAttributes = array(
			'name'  => $this->getFormOptionFieldName($optionName),
			'type'  => !empty($arrSettingsField['InputType']) ? $arrSettingsField['InputType'] : 'text',
			'value' => $optionValue,
			'class' => array(),
			'id'    => $this->getSettingKey() . '-' . $optionName,
		);


		$arrFieldAttributes = apply_filters($this->getFieldAttributesFilterName($optionName), $arrFieldAttributes);


//		if($arrFieldAttributes['type'] === MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX)
//		{
//			!empty($arrFieldAttributes['value']) ? $arrFieldAttributes['checked'] = 'checked' : null;
//			$arrFieldAttributes['value'] = true;
//
//		}

		if($arrFieldAttributes['type'] === MchHtmlUtils::FORM_ELEMENT_SELECT)
		{
			!empty($arrFieldAttributes['class'])     ?: $arrFieldAttributes['class'] = array();
			!is_string($arrFieldAttributes['class']) ?: $arrFieldAttributes['class'] = explode(',', $arrFieldAttributes['class']);

			if(isset($arrFieldAttributes['multiple'])){
				$arrFieldAttributes['name'] = $arrFieldAttributes['name'] . '[]';
			}
		}

		if(isset($arrFieldAttributes['class']) && is_array($arrFieldAttributes['class']))
		{
			$arrFieldAttributes['class'] = implode(' ', $arrFieldAttributes['class']);
		}

		$fieldOutputHtml =  MchHtmlUtils::createFormElement($arrFieldAttributes['type'], $arrFieldAttributes);

		$fieldOutputHtml =  apply_filters($this->getFieldAttributesFilterName($optionName) . '-output-html', $fieldOutputHtml, $arrFieldAttributes);

		if(!empty($arrSettingsField['HelpText'])){
			$fieldOutputHtml .= '<i class="fa fa-info-circle uc-tooltip uc-help-tooltip" title="' . $arrSettingsField['HelpText'] . '"></i>';
		}

		echo $fieldOutputHtml;

		if(!empty($arrSettingsField['Description']))
		{
			echo '<p class = "description">' . $arrSettingsField['Description'] . '</p>';
		}

	}

//	protected function getFormattedFieldDescription($description)
//	{
//		return  '<p class = "description">' . esc_html( $description );  '</p>';
//	}


	public function getOptionDisplayTextByOptionId($settingOptionId)
	{
		$settingOptionId = (int)$settingOptionId;

		foreach($this->getDefaultOptions() as $arrOptionInfo)
		{
			if (isset($arrOptionInfo['Id']) &&  $arrOptionInfo['Id'] === $settingOptionId && isset($arrOptionInfo['DisplayText']))
				return esc_html($arrOptionInfo['DisplayText']);
		}

		return null;
	}


	public function getFormElementName($optionName)
	{
		if(!array_key_exists($optionName, (array)$this->getDefaultOptions()))
			return null;

		return esc_attr($this->getSettingKey() . '[' . $optionName . ']');
	}

	protected function addSettingsSectionDivider($sectionLabel, $fieldSettingsTableRowOutput, $prepend = true)
	{
		$dividerContent = self::getFieldSettingsSectionDivider($sectionLabel);
		return $prepend ? $dividerContent . $fieldSettingsTableRowOutput : $fieldSettingsTableRowOutput . $dividerContent;
	}

	protected function getFieldSettingsSectionDivider($sectionLabel)
	{
		$sectionLabel = esc_html($sectionLabel);

		return "<tr><td style=\"padding:0;\" colspan=\"2\"><div class=\"uc-u-1 uc-control-group\">
			<div style=\"margin: 10px 0 10px;border-top:1px solid #e4e4e4;text-align:center; letter-spacing:1px\" class=\"uc-section-divider\">
			<span style=\"color: #1e88e5;padding:0 15px 0;\" class=\"uc-vertical-align-middle\">$sectionLabel</span>
			</div>
			</div></td></tr>";
	}

}