<?php

/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\MchLib\Utils;

final class MchHtmlUtils
{
	CONST FORM_ELEMENT_INPUT_HIDDEN   = 'hidden';
	CONST FORM_ELEMENT_INPUT_TEXT     = 'text';
	CONST FORM_ELEMENT_INPUT_TEXTAREA = 'textarea';
	CONST FORM_ELEMENT_INPUT_CHECKBOX = 'checkbox';
	CONST FORM_ELEMENT_INPUT_PASSWORD = 'password';
	CONST FORM_ELEMENT_SELECT         = 'select';
	CONST FORM_ELEMENT_INPUT_RADIO    = 'radio';

	private static function sanitizeClassAttribute($arrAttributes)
	{

		//$arrAttributes = array_change_key_case($arrAttributes, \CASE_LOWER);

		if(!isset($arrAttributes['class']))
			return $arrAttributes;

		is_array( $arrAttributes['class']) ?: $arrAttributes['class'] = (array)explode(' ', $arrAttributes['class']);

		$arrAttributes['class'] = (array)array_map('sanitize_html_class', $arrAttributes['class']);
		$arrAttributes['class'] = implode(' ', $arrAttributes['class']);




		return $arrAttributes;
	}

	public static function createFormElement($elementType, array $arrAttributes)
	{
		$arrAttributes = self::sanitizeClassAttribute($arrAttributes);

		if(empty($arrAttributes['class'])) {
			unset($arrAttributes['class']);
		}

		switch ($elementType)
		{
			case MchHtmlUtils::FORM_ELEMENT_SELECT :

				return MchHtmlUtils::createSelectElement($arrAttributes);

			case MchHtmlUtils::FORM_ELEMENT_INPUT_TEXTAREA :

				return MchHtmlUtils::createTextAreaElement($arrAttributes);

			case MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX:

				empty($arrAttributes['value']) ?: $arrAttributes['checked'] = 'checked';
				$arrAttributes['value'] = true;

				$arrAttributes['type'] = $elementType;

				return MchHtmlUtils::createInputElement($arrAttributes);

			case MchHtmlUtils::FORM_ELEMENT_INPUT_RADIO:

				$arrAttributes['type'] = $elementType;

				return MchHtmlUtils::createRadioElement($arrAttributes);

			case MchHtmlUtils::FORM_ELEMENT_INPUT_HIDDEN :
				$arrAttributes['type'] = MchHtmlUtils::FORM_ELEMENT_INPUT_HIDDEN;
				return MchHtmlUtils::createInputElement($arrAttributes);


			case MchHtmlUtils::FORM_ELEMENT_INPUT_PASSWORD :

				$arrAttributes['type'] = MchHtmlUtils::FORM_ELEMENT_INPUT_PASSWORD;
				return MchHtmlUtils::createInputElement($arrAttributes);

			default :

				return MchHtmlUtils::createInputElement($arrAttributes);
		}

		return null;
	}


	private static function createRadioElement(array $arrAttributes)
	{

		unset($arrAttributes['options'], $arrAttributes['id'], $arrAttributes['placeholder']);

		if(!empty($arrAttributes['selected'])){
			$arrAttributes['checked'] = 'checked';
		}

		if(empty($arrAttributes['checked'])){
			unset($arrAttributes['checked']);
		}
		else
		{
			$arrAttributes['checked'] = 'checked';
		}

		$code  = '<input';
		foreach ($arrAttributes as $key => $value)
		{
			$value = esc_attr($value);
			$code .= " {$key}=\"{$value}\"";
		}

		$code .= '/>';

		return $code;

	}

	private static function createSelectElement(array $arrAttributes)
	{
		$optionsCode = '';

		if(isset($arrAttributes['options']) && is_array($arrAttributes['options']))
		{
			foreach($arrAttributes['options'] as $key => $value)
			{
				$selected = '';

				if(isset( $arrAttributes['value'] ) && is_array($arrAttributes['value'])) // is multiselect
				{
					MchUtils::stringEndsWith($arrAttributes['name'], '[]') ?: $arrAttributes['name'] .= '[]';
					$selected = in_array($key, $arrAttributes['value']) ? 'selected = "selected"' : '';
				}
				else
				{
					$selected = isset( $arrAttributes['value'] ) && $arrAttributes['value'] == $key ? 'selected = "selected"' : '';
				}

				$additionalAttributes = null;

				if(isset($arrAttributes['options-attributes'][$key]))
				{
					foreach((array)$arrAttributes['options-attributes'][$key] as $attributeName => $attributevalue)
					{
						$additionalAttributes .= "$attributeName=\"$attributevalue\" ";
					}

					$additionalAttributes = trim($additionalAttributes);
					$additionalAttributes = " $additionalAttributes ";
				}

				$optionsCode .= '<option value="' . esc_attr( $key ) . '" ' . $selected . $additionalAttributes . '>' . esc_html( $value ) . '</option>';
			}
		}

		unset($arrAttributes['value'], $arrAttributes['options'], $arrAttributes['type']);

		empty($arrAttributes['id']) && !empty($arrAttributes['name']) ? $arrAttributes['id'] = MchUtils::replaceNonAlphaCharacters($arrAttributes['name'], '-') : null;

		unset($arrAttributes['options-attributes']);


		$code  = '<select';
		foreach ($arrAttributes as $key => $value)
		{
//			if(null !== $value && !is_scalar($value))
//				continue;

			$value = esc_attr($value);
			$code .= " {$key}=\"{$value}\"";
		}

		$code .= '>' . $optionsCode . '</select>';

		return $code;

	}


	private static function createTextAreaElement(array $arrAttributes)
	{
		$code  = '<textarea ';

		empty($arrAttributes['id']) && !empty($arrAttributes['name']) ? $arrAttributes['id'] = MchUtils::replaceNonAlphaCharacters($arrAttributes['name'], '-') : null;

		$text = isset($arrAttributes['value']) ? $arrAttributes['value'] : '';
		unset($arrAttributes['value']);
		foreach ($arrAttributes as $key => $value)
		{
			$value = esc_attr($value);
			$code .= " {$key}=\"{$value}\"";
		}

		$code .= '>' . $text . '</textarea>';

		return $code;

	}


	private static function createInputElement(array $arrAttributes)
	{
		$code  = '<input';

		$code .= isset($arrAttributes['type'])   ? " type=\"{$arrAttributes['type']}\"" : " type=\"text\"";

		unset($arrAttributes['type']);

		empty($arrAttributes['id']) && !empty($arrAttributes['name']) ? $arrAttributes['id'] = MchUtils::replaceNonAlphaCharacters($arrAttributes['name'], '-') : null;

		foreach ($arrAttributes as $key => $value)
		{
			$value = esc_attr($value);

			if('placeholder' === $key && !strlen($value)) // use strlen here
			{
				//$value = ' ';
				continue;
			}

			$code .= " {$key}=\"{$value}\"";
		}

		$code .= ' />';

		return $code;
	}

	private static function createLabelElement($innerText, $forInputId)
	{
		return '<label>' . esc_html($innerText) . '<label>';
	}

	public static function createAnchorElement($innerText, array $arrAttributes)
	{
		$arrAttributes = self::sanitizeClassAttribute($arrAttributes);


		$code  = '<a';

		foreach ($arrAttributes as $key => $value)
		{
			$value = ('href' === $key) ? esc_url($value) :  esc_attr($value);
			$code .= " {$key}=\"{$value}\"";
		}

		$code .= '>' . $innerText . '</a>';

		return $code;
	}


	private function __construct()
	{}
}
