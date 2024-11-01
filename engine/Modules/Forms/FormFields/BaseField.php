<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Modules\Forms\FormFields;

use UltraCommunity\MchLib\Plugin\MchBasePlugin;
use UltraCommunity\MchLib\Utils\MchHtmlUtils;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\UltraCommException;

abstract class BaseField
{
	CONST FIELD_TYPE_TEXT     = 'text';
	CONST FIELD_TYPE_PASSWORD = 'password';
	CONST FIELD_TYPE_TEXTAREA = 'textarea';
	CONST FIELD_TYPE_SELECT   = 'select';
	CONST FIELD_TYPE_CHECKBOX = 'checkbox';
	CONST FIELD_TYPE_HIDDEN   = 'hidden';
	CONST FIELD_TYPE_RADIO    = 'radio';

	CONST VISIBILITY_EVERYBODY         = 1;
	CONST VISIBILITY_NOBODY            = 2;
	CONST VISIBILITY_LOGGED_IN_USERS   = 3;
	CONST VISIBILITY_JUST_USERS_ROLES  = 4;



	public $UniqueId     = null;
	public $Type         = null;
	public $Name         = null;
	public $Class        = null;
	public $PlaceHolder  = null;
	public $Value        = null;
	public $Label        = null;
	//public $HelpText     = null;
	public $ErrorMessage = null;
	public $DefaultValue = null;

	public $MinLength = null;
	public $MaxLength = null;

	public $FrontEndVisibility = null;
	public $OptionsList         = null;
	public $MappedUserMetaKey = null;

	public $DisplayFieldType  = null;

	public $IsRequired        = null;
	public $IsEditable        = null;
	public $IsVisibleOnEditProfile = null;

	public $IsHtmlAllowed = null;
//	public $EnableWpEditor = null;

	public $FontAwesomeIcon  = null;

	public $RegisterFormCustomPostId  = null;
	public $RegisterFormFieldUniqueId = null;

	public function __construct($fieldType = null)
	{
		$this->Type = $fieldType;
	}



	public function sanitizeFieldValue()
	{
		if(empty($this->Value)){
			return $this->Value;
		}

		switch(true)
		{
			case $this->Type === self::FIELD_TYPE_TEXTAREA :

				global $allowedtags;

				$arrAllowedHtmlTags =  current_user_can( 'unfiltered_html' ) ?  wp_kses_allowed_html('post') : $allowedtags;

				unset($arrAllowedHtmlTags['code']);

				//$this->Value =  esc_textarea(MchWpUtils::sanitizeTextArea($this->Value, $this->IsHtmlAllowed ? $arrAllowedHtmlTags : array()));

				$this->Value =  (MchWpUtils::sanitizeTextArea($this->Value, $this->IsHtmlAllowed ? $arrAllowedHtmlTags : array()));

				break;

			case $this instanceof UserNameField :
				$this->Value = MchWpUtils::sanitizeUserName($this->Value, true);
				break;

			case $this instanceof UserNameOrEmailField :
				$this->Value =  MchValidator::isEmail($this->Value) ?  MchWpUtils::sanitizeEmail($this->Value) : MchWpUtils::sanitizeUserName($this->Value);
				break;

			case $this instanceof EmailField :
			case $this instanceof UserEmailField:
				$this->Value =  MchWpUtils::sanitizeEmail($this->Value);
				break;

			case $this instanceof WebUrlField:
			case $this instanceof UserWebUrlField:
			case $this instanceof SocialNetworkUrlField:
				$this->Value =  esc_url_raw(MchWpUtils::sanitizeText($this->Value));
				break;

			case self::FIELD_TYPE_PASSWORD :
				break;

			default :
				$this->Value =  MchWpUtils::sanitizeText($this->Value);
				break;
		}


	}


	public static function getPropertyHelpText($propertyName)
	{
		switch ($propertyName)
		{
			case 'Name':
				return __('The name of the field is required and it is set just for your reference in order to easily identify it', 'ultra-community');

			case 'Label' :
				return __('The text used to create a caption for this field', 'ultra-community');

			case 'PlaceHolder' :
				return __('A descriptive text displayed inside the input field until the field is filled. It disappears when user starts typing in the field.', 'ultra-community');

			case 'IsRequired' :
				return __('This specifies that the field must be filled out before submitting the form.', 'ultra-community');

			case 'IsEditable' :
				return __('This specifies whether or not the user can edit this field', 'ultra-community');

			case 'DefaultValue' :
				return __('The default value of this field', 'ultra-community');

			case 'ErrorMessage' :
				return __('The text to be displayed in case validation is not passed!', 'ultra-community');

			case 'MinLength' :
				return __('The minimum number of characters the user must enter for this field', 'ultra-community');

			case 'MaxLength' :
				return __('The maximum number of characters allowed for this field', 'ultra-community');

			case 'FrontEndVisibility' :
				return __('Specifies who is allowed to see this field on front end', 'ultra-community');

			case 'MappedUserMetaKey' :
				return __('Use this option to map this field to any already defined user meta key excluding nickname, description, first_name, last_name !', 'ultra-community');

			case 'IsVisibleOnEditProfile' :
				return __('This specifies whether or not the field is visible when user edits the profile. An admin will always see the field!', 'ultra-community');

			case 'IsHtmlAllowed' :
				return __('This specifies whether or not the user is allowed to use HTML tags.', 'ultra-community');

			case 'OptionsList':
				return __('This specifies all available options for user. Please enter one option per line!', 'ultra-community');

			case 'FormatPattern':
				return __('This specifies how the date will be displayed on front end!', 'ultra-community');

		}

		return null;
	}


	public function isValid($performSanitization = true)
	{

		!($performSanitization) ?: $this->sanitizeFieldValue();

		if(!$this->IsRequired && MchValidator::isNullOrEmpty($this->Value, true))
			return true;

		switch(true)
		{
			case $this instanceof EmailField :
			case $this instanceof UserEmailField:
				if( ! MchValidator::isEmail($this->Value) )
					throw new UltraCommException(empty($this->ErrorMessage) ? __('Please provide a valid E-mail Address', 'ultra-community') : $this->ErrorMessage);
			break;

			case $this instanceof WebUrlField:
			case $this instanceof UserWebUrlField:
			case $this instanceof SocialNetworkUrlField:
				if( ! MchValidator::isURL($this->Value) )
					throw new UltraCommException(empty($this->ErrorMessage) ? __('Please provide a valid URL!', 'ultra-community') : $this->ErrorMessage);
			break;

			case $this instanceof UserNameField :
				if(!validate_username($this->Value))
					throw new UltraCommException(empty($this->ErrorMessage) ? __('Please provide a valid Username!', 'ultra-community') : $this->ErrorMessage);
			break;

			default:
			break;
		}

		if($this->IsRequired && MchValidator::isNullOrEmpty($this->Value, true)){
			throw new UltraCommException(empty($this->ErrorMessage) ? sprintf(__("The field %s is required!", 'ultra-community'), esc_html($this->Label)) : $this->ErrorMessage);
		}


		if((int)$this->MinLength > 0 && mb_strlen( strip_tags($this->Value), '8bit' ) < (int)$this->MinLength)
		{
			throw new UltraCommException(empty($this->ErrorMessage) ? sprintf(__("The field %s required at least %d characters!", 'ultra-community'), esc_html($this->Label), (int)$this->MinLength) : $this->ErrorMessage);
		}

		if((int)$this->MaxLength > 0)
		{
			if(mb_strlen( strip_tags($this->Value), '8bit' ) > (int)$this->MaxLength){
				throw new UltraCommException(empty($this->ErrorMessage) ? sprintf(__("The field %s allows maximum of %d characters!", 'ultra-community'), esc_html($this->Label), (int)$this->MaxLength) : $this->ErrorMessage);
			}
		}

		return true;
	}


	public function getFieldOptionsList()
	{
		return $this->OptionsList;
	}

	public function toHtmlOutput(array $arrAdditionalAttributes = array(), $useFloatingLabel = false)
	{

		$arrFieldAttributes = array(
				'id'          => $this->UniqueId,
				'name'        => $this->UniqueId,
				'placeholder' => $this->PlaceHolder,
				'value'       => MchValidator::isNullOrEmpty($this->Value, true) ? $this->DefaultValue : $this->Value,
				'class'       => array('uc-input-1'),
		);


		empty($arrAdditionalAttributes) ?: $arrFieldAttributes = wp_parse_args($arrAdditionalAttributes, $arrFieldAttributes);


		switch($this->Type)
		{
			case self::FIELD_TYPE_SELECT :
			case self::FIELD_TYPE_RADIO :
			case self::FIELD_TYPE_CHECKBOX :

				$arrFieldAttributes['options'] = (array)$this->getFieldOptionsList();
				$arrFieldAttributes['options-attributes'] = array();
			break;

			case self::FIELD_TYPE_PASSWORD :

				$arrFieldAttributes['value'] = null;

			break;
		}


		$arrFieldAttributes['type'] = $this->Type;

		$formFieldOutput = sprintf('<label class="" for="%s">%s</label>', esc_attr($arrFieldAttributes['id']), esc_html($this->Label));

		if($this->Type == self::FIELD_TYPE_RADIO)
		{

			$formFieldOutput .= '<div class="uc-form-grouped-controls uc-no-border uc-grid uc-grid--center">';

			foreach((array)$this->getFieldOptionsList() as $key => $value)
			{
				$selectedAttribute = in_array($key, (array)$this->Value) ? ' checked = "checked" ' : null;

				$formFieldOutput .= '<label class="uc-radio-holder uc-grid-cell">';
				$formFieldOutput .= sprintf('<input type="radio" name="%s" value = "%s" %s>', $this->UniqueId, esc_attr($key), $selectedAttribute);
				$formFieldOutput .= '<span class="uc-radio"></span>';
				$formFieldOutput .= sprintf('<span class="uc-radio-text">%s</span>', esc_html($value)); ;
				$formFieldOutput .= '</label>';
			}

			$formFieldOutput .= '</div>';

			return $formFieldOutput;
		}

		if($this->Type == self::FIELD_TYPE_CHECKBOX)
		{

			$formFieldOutput .= '<div class="uc-form-grouped-controls uc-no-border uc-grid uc-grid--center">';

			foreach((array)$this->getFieldOptionsList() as $key => $value)
			{
				$selectedAttribute = in_array($key, (array)$this->Value) ? ' checked = "checked" ' : null;

				$formFieldOutput .= '<label class="uc-checkbox-holder uc-grid-cell">';
				$formFieldOutput .= sprintf('<input type="checkbox" name="%s[%s]" %s>', $this->UniqueId, $key, $selectedAttribute);
				$formFieldOutput .= '<span class="uc-checkbox"></span>';
				$formFieldOutput .= sprintf('<span class="uc-checkbox-text">%s</span>', esc_html($value)); ;
				$formFieldOutput .= '</label>';
			}

			$formFieldOutput .= '</div>';

			return $formFieldOutput;
		}

		$this->FontAwesomeIcon = esc_attr($this->FontAwesomeIcon);

		if($useFloatingLabel)
		{
			$formFieldOutput = null;
		}

		$formFieldOutput .= '<div class="uc-form-grouped-controls">';

		if(!empty($this->FontAwesomeIcon))
		{
			$formFieldOutput .= '<div class="uc-form-item">';
			$formFieldOutput .= '<i class="fa ' . $this->FontAwesomeIcon .'"></i>';
			$formFieldOutput .= '</div>';
		}


		if($this->Type == self::FIELD_TYPE_SELECT)
		{
			$arrFieldAttributes['class'][] = 'uc-nice-select';
			$arrFieldAttributes['class'][] = 'uc-nice-select-wide';


			if($this instanceof CountryField)
			{
				foreach($arrFieldAttributes['options'] as $optionValue => $optionDisplay)
				{
					$countryCode = strtolower($optionValue);
					$arrFieldAttributes['options-attributes'][$optionValue] = array(
						'class' => 'uc-flag-icon-background',
						'style' => 'background-image:url(' . MchBasePlugin::getPluginBaseUrl() . "/assets/images/country-flags/rectangle/{$countryCode}.svg)"
					);
				}
			}
			
			//print_r($arrFieldAttributes);exit;


			$arrFieldAttributes['options'] = array("" => $arrFieldAttributes['placeholder']) + $arrFieldAttributes['options'];

		}

		$formFieldOutput .=  MchHtmlUtils::createFormElement($arrFieldAttributes['type'], $arrFieldAttributes);

		if ($useFloatingLabel)
		{
			$formFieldOutput .= sprintf('<label class="uc-floating-label" for="%s">%s</label>', esc_attr($arrFieldAttributes['id']), esc_html($this->Label));
		}


		$formFieldOutput .= '</div>';

		return $formFieldOutput;
	}

	public function getDisplayableFieldType()
	{
		switch(true)
		{
			case $this instanceof CountryField :
				return (__('Country', 'ultra-community'));
			case $this instanceof TextField :
				return __('Single Line Text', 'ultra-community');
			case $this instanceof CheckBoxField :
				return (__('Checkboxes', 'ultra-community'));
			case $this instanceof RadioButtonField :
				return (__('Radio Buttons', 'ultra-community'));
			case $this instanceof TextAreaField :
				return (__('Paragraph Text', 'ultra-community'));
			case $this instanceof DropDownField :
				return (__('Dropdown', 'ultra-community'));
			case $this instanceof EmailField :
				return (__('Email', 'ultra-community'));
			case $this instanceof SocialNetworkUrlField :
				return (__('Social Networks', 'ultra-community'));

			case $this instanceof DividerField :
				return (__('Divider', 'ultra-community'));

			case $this instanceof ProfileSectionField :
				return (__('Profile Section', 'ultra-community'));

			case $this instanceof LanguageField :
				return (__('Language', 'ultra-community'));


			case $this instanceof UserNameOrEmailField :
				return (__('Username or Email', 'ultra-community'));

			case $this instanceof UserBioField :
				return (__('Biography', 'ultra-community'));
			case $this instanceof UserNameField :
				return (__('Username', 'ultra-community'));
			case $this instanceof UserFirstNameField :
				return (__('First Name', 'ultra-community'));
			case $this instanceof UserLastNameField :
				return (__('Last Name', 'ultra-community'));
			case $this instanceof UserFullNameField :
				return (__('Full Name', 'ultra-community'));

			case $this instanceof UserDisplayNameField :
				return (__('Display Name', 'ultra-community'));
			case $this instanceof UserNickNameField :
				return (__('Display Name', 'ultra-community'));

			case $this instanceof UserEmailField :
				return (__('Email Address', 'ultra-community'));
			case $this instanceof UserWebUrlField :
				return (__('WebSite Url', 'ultra-community'));
			case $this instanceof UserRegistrationDateField :
				return (__('Registration Date', 'ultra-community'));
			case $this instanceof UserPasswordField :
				return (__('Password', 'ultra-community'));
			case $this instanceof UserGenderDropDownField :
				return (__('Gender(Dropdown)', 'ultra-community'));
			case $this instanceof UserGenderRadioField :
				return (__('Gender', 'ultra-community'));


			case $this instanceof SocialConnectField :
				return (__('Social Connect', 'ultra-community'));


//			case $this instanceof SubscriptionLevelField :
//				return (__('Subscription Level', 'ultra-community'));

			case $this instanceof SubscriptionLevelsField :
				return (__('Subscription Levels', 'ultra-community'));

		}

		return null;
	}


	public function getUserEntityMappedFieldName()
	{
		switch (true)
		{
			case $this instanceof UserNickNameField :
				return '_meta_NickName';
			case $this instanceof UserBioField :
				return '_meta_Description';
			case $this instanceof UserLastNameField :
				return '_meta_LastName';
			case $this instanceof UserFirstNameField :
				return '_meta_FirstName';
			case $this instanceof UserNameField :
				return 'UserName';
			case $this instanceof UserDisplayNameField :
				return 'DisplayName';
			case $this instanceof UserEmailField :
				return 'Email';
			case $this instanceof UserWebUrlField :
				return 'WebSiteUrl';
			case $this instanceof UserRegistrationDateField :
				return 'RegisteredDate';
			case $this instanceof UserPasswordField :
				return 'Password';

		}

		return null;
	}

}
