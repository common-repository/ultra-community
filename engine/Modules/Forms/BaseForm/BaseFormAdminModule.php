<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Modules\Forms\BaseForm;
use UltraCommunity\Controllers\ModulesController;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Controllers\UserRoleController;
use UltraCommunity\MchLib\Utils\FontAwesomeIconParser;
use UltraCommunity\MchLib\Utils\MchHtmlUtils;
use UltraCommunity\MchLib\Utils\MchHttpRequest;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\Utils\MchWpUtils;
//use UltraCommunity\Modules\Appearance\Colors\ColorsAppearanceAdminModule;
use UltraCommunity\Modules\BaseAdminModule;
use UltraCommunity\Modules\Forms\FormFields\BaseField;
use UltraCommunity\Modules\Forms\FormFields\CheckBoxField;
use UltraCommunity\Modules\Forms\FormFields\CountryField;
use UltraCommunity\Modules\Forms\FormFields\DividerField;
use UltraCommunity\Modules\Forms\FormFields\DropDownField;
use UltraCommunity\Modules\Forms\FormFields\ProfileSectionField;
use UltraCommunity\Modules\Forms\FormFields\RadioButtonField;
use UltraCommunity\Modules\Forms\FormFields\SocialConnectField;
//use UltraCommunity\Modules\Forms\FormFields\SubscriptionLevelField;
use UltraCommunity\Modules\Forms\FormFields\SubscriptionLevelsField;
use UltraCommunity\Modules\Forms\FormFields\UserDisplayNameField;
use UltraCommunity\Modules\Forms\FormFields\UserEmailField;
use UltraCommunity\Modules\Forms\FormFields\UserFirstNameField;
use UltraCommunity\Modules\Forms\FormFields\UserFullNameField;
use UltraCommunity\Modules\Forms\FormFields\LanguageField;
use UltraCommunity\Modules\Forms\FormFields\UserGenderDropDownField;
use UltraCommunity\Modules\Forms\FormFields\UserGenderRadioField;
use UltraCommunity\Modules\Forms\FormFields\UserLastNameField;
use UltraCommunity\Modules\Forms\FormFields\UserNickNameField;
use UltraCommunity\Modules\Forms\FormFields\UserPasswordField;
use UltraCommunity\Modules\Forms\FormFields\UserRegistrationDateField;
use UltraCommunity\Modules\Forms\FormFields\SocialNetworkUrlField;
use UltraCommunity\Modules\Forms\FormFields\TextAreaField;
use UltraCommunity\Modules\Forms\FormFields\TextField;
use UltraCommunity\Modules\Forms\FormFields\UserBioField;
use UltraCommunity\Modules\Forms\FormFields\UserNameField;
use UltraCommunity\Modules\Forms\FormFields\UserNameOrEmailField;
use UltraCommunity\Modules\Forms\FormFields\UserWebUrlField;
use UltraCommunity\Modules\Forms\UserProfileForm\UserProfileFormAdminModule;
use UltraCommunity\Modules\GeneralSettings\Plugin\PluginSettingsAdminModule;
use UltraCommunity\Modules\SocialConnect\SocialConnectAdminModule;
use UltraCommunity\Modules\UserRole\UserRoleAdminModule;
use UltraCommunity\Modules\UserSubscriptions\Controllers\UserSubscriptionController;
use UltraCommunity\PostsType\UserProfileFormPostType;
use UltraCommunity\UltraCommException;
use UltraCommunity\UltraCommHelper;


abstract class BaseFormAdminModule extends BaseAdminModule
{
	CONST ALIGNMENT_LEFT   = 1;
	CONST ALIGNMENT_CENTER = 2;
	CONST ALIGNMENT_RIGHT  = 3;


	CONST OPTION_FORM_TITLE     = 'T';
	CONST OPTION_FORM_ALIGNMENT = 'A';
	CONST OPTION_FORM_MAX_WIDTH = 'MW';

	CONST OPTION_FORM_REMEMBER_ME_LABEL     = 'SRMe';
	CONST OPTION_FORM_FORGOT_PASSWORD_LABEL = 'SFPass';

	CONST OPTION_FORM_FIELDS           = 'Fields';
	CONST OPTION_PROFILE_HEADER_FIELDS = 'PHF';

	CONST OPTION_FORM_CUSTOM_CSS  = 'CCSS';

	CONST OPTION_FORM_HEADER_TITLE     = 'HT';

	//CONST OPTION_FORM_HEADER_BG_COLOR  = 'HBGC';

	//CONST OPTION_FORM_HEADER_TOP_BORDER_COLOR = 'HeaderTopBorderColor';
	CONST OPTION_FORM_HEADER_BOTTOM_BORDER_COLOR = 'HBBC';

	//CONST OPTION_FORM_HEADER_BOTTOM_BORDER_WIDTH = 'HeaderBottomBorderWidth';

	CONST OPTION_FORM_PRIMARY_BUTTON_TEXT  = 'PBT';
	CONST OPTION_FORM_PRIMARY_BUTTON_COLOR = 'PBC';
	CONST OPTION_FORM_PRIMARY_BUTTON_HOVER_COLOR = 'PBHC';

	CONST OPTION_USER_ROLE_CUSTOM_POST_ID = 'URCPT';
	CONST OPTION_ASSIGNED_USER_ROLES      = 'AUR';

//	CONST OPTION_ASSIGNED_PAGE_ID   = 'API';
//	CONST OPTION_ASSIGNED_PAGE_SLUG = 'APS';



	CONST OPTION_IS_DEFAULT_LOGIN_FORM           = 'IDLF';
	CONST OPTION_IS_DEFAULT_REGISTRATION_FORM    = 'IDRF';
	CONST OPTION_IS_DEFAULT_USER_PROFILE_FORM    = 'IDUPF';
	//CONST OPTION_IS_DEFAULT_RESET_PASSWORD_FORM  = 'IDFPF';
	CONST OPTION_IS_DEFAULT_FORGOT_PASSWORD_FORM = 'IDRPF';

	protected function __construct()
	{
		parent::__construct();
	}

	public function getDefaultOptions()
	{
		static $arrDefaultSettingOptions = null;
		if(null !== $arrDefaultSettingOptions)
			return $arrDefaultSettingOptions;

		$arrDefaultSettingOptions = array(

			self::OPTION_FORM_FIELDS  => array(
				'Value'      => null,
				'LabelText'  => null,
				'InputType'  => null
			),


			self::OPTION_FORM_TITLE  => array(
				'Value'      => __('New Form', 'ultra-community'),
				'LabelText'  => __('Form Name', 'ultra-community'),
				'HelpText'   => __('The title of the form', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
			),

			self::OPTION_USER_ROLE_CUSTOM_POST_ID  => array(
				'Value'      => null,
				'LabelText'  => __('Assigned User Role', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT
			),

			self::OPTION_ASSIGNED_USER_ROLES => array(
				'Value'      => null,
				'LabelText'  => __('Assigned User Roles', 'ultra-community'),
				'HelpText'   => __('This form will be displayed to the selected user roles!', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT

			),

			self::OPTION_FORM_ALIGNMENT  => array(
				'Value'      => self::ALIGNMENT_CENTER,
				'LabelText'  => __('Form Alignment', 'ultra-community'),
				'HelpText'   => __('Select how the form will be aligned on the page. By default the form will be centered', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT
			),

			self::OPTION_FORM_MAX_WIDTH  => array(
				'Value'      => '550px',
				'LabelText'  => __('Form Maximum Width', 'ultra-community'),
				'HelpText'   => __('The maximum width of this form', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
			),

			self::OPTION_FORM_HEADER_TITLE  => array(
				'Value'      => null,
				'LabelText'  => __('Form Header Title', 'ultra-community'),
				'HelpText'   => __('This text will be displayed on front end as form header title', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
			),

//			self::OPTION_FORM_HEADER_BG_COLOR  => array(
//				'Value'      => null,
//				'LabelText'  => __('Form Header Background Color', 'ultra-community'),
//				'HelpText'   => __('The background color of the form header', 'ultra-community'),
//				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
//			),

//			self::OPTION_FORM_HEADER_TOP_BORDER_COLOR  => array(
//				'Value'      => '#007bdd',
//				'LabelText'  => __('Form Header Border Top Color', 'ultra-community'),
//				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
//			),

//			self::OPTION_FORM_HEADER_BOTTOM_BORDER_COLOR  => array(
//				'Value'      => null,
//				'LabelText'  => __('Form Header Border Bottom Color', 'ultra-community'),
//				'HelpText'   => __('The bottom border color of the form header', 'ultra-community'),
//				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
//			),

			self::OPTION_FORM_PRIMARY_BUTTON_TEXT  => array(
				'Value'      => __('Submit', 'ultra-community'),
				'LabelText'  => __('Primary Button Text', 'ultra-community'),
				'HelpText'   => __('The text that should be displayed for Submit button', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
			),

//			self::OPTION_FORM_PRIMARY_BUTTON_COLOR  => array(
//				'Value'      => null,
//				'LabelText'  => __('Primary Button Color', 'ultra-community'),
//				'HelpText'   => __('Sets the background color of the primary button', 'ultra-community'),
//				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
//			),
//
//			self::OPTION_FORM_PRIMARY_BUTTON_HOVER_COLOR  => array(
//				'Value'      => null,
//				'LabelText'  => __('Primary Button Hover Color', 'ultra-community'),
//				'HelpText'   => __('Sets the background color of the primary button on mouse over event', 'ultra-community'),
//				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
//			),

			self::OPTION_FORM_REMEMBER_ME_LABEL => array(
				'Value'      => __('Keep me sign in', 'ultra-community'),
				'LabelText'  => __('Remember Me Option Label', 'ultra-community'),
				'HelpText'   => __('Text used for "Remember me" option', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
			),


			self::OPTION_FORM_FORGOT_PASSWORD_LABEL => array(
				'Value'      => __('Forgot password?', 'ultra-community'),
				'LabelText'  => __('Forgot Password Link Text', 'ultra-community'),
				'HelpText'   => __('Text used for "Forgot password" option', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
			),


			self::OPTION_FORM_CUSTOM_CSS  => array(
				'Value'      => null,
				'LabelText'  => __('Form Custom CSS', 'ultra-community'),
				'HelpText'   => __('Adds extra custom CSS used for this form', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXTAREA
			),


//			self::OPTION_IS_DEFAULT_LOGIN_FORM => array(
//			),
//
//			self::OPTION_IS_DEFAULT_REGISTRATION_FORM => array(
//			),
//
//			self::OPTION_IS_DEFAULT_USER_PROFILE_FORM => array(
//			),

//			self::OPTION_IS_DEFAULT_RESET_PASSWORD_FORM => array(
//			),

//			self::OPTION_IS_DEFAULT_FORGOT_PASSWORD_FORM => array(
//			),

		);

		return $arrDefaultSettingOptions;

	}


	/**
	 * @return \UltraCommunity\Modules\Forms\FormFields\BaseField | null
	 */
	public function getFormFieldByUniqueId($fieldUniqueId)
	{

		foreach((array)$this->getOption(self::OPTION_FORM_FIELDS) as $formField){
			if($formField->UniqueId === $fieldUniqueId)
				return $formField;
		}

		return null;
	}

	public function canBeDeleted()
	{
		
		if( defined( 'WP_UNINSTALL_PLUGIN' ) && current_user_can('delete_plugins') )
			return true;
		
		foreach(array(self::OPTION_IS_DEFAULT_LOGIN_FORM, self::OPTION_IS_DEFAULT_REGISTRATION_FORM, self::OPTION_IS_DEFAULT_USER_PROFILE_FORM, self::OPTION_IS_DEFAULT_FORGOT_PASSWORD_FORM) as $isDefaultForm){
			if(!MchValidator::isNullOrEmpty($this->getOption($isDefaultForm))) {
				return false;
			}
		}

		return true;
	}


	public function deleteAllSettingOptions($forceBlogOption = true)
	{
		if(!$this->canBeDeleted()) {
			throw new UltraCommException( __( 'This is a built in form and cannot be deleted!', 'ultra-community' ) );
		}

		return parent::deleteAllSettingOptions($forceBlogOption);
	}

	public  function validateModuleSettingsFields($arrSettingOptions)
	{
		$arrSettingOptions = $this->sanitizeModuleSettings($arrSettingOptions);

		if($this->getOption(self::OPTION_FORM_FIELDS)){
			$arrSettingOptions[self::OPTION_FORM_FIELDS] = $this->getOption(self::OPTION_FORM_FIELDS);
		}

		if(empty($arrSettingOptions[self::OPTION_USER_ROLE_CUSTOM_POST_ID]))
		{
			unset($arrSettingOptions[self::OPTION_USER_ROLE_CUSTOM_POST_ID]);
		}
		else
		{
			$arrSettingOptions[self::OPTION_USER_ROLE_CUSTOM_POST_ID] = (int)$arrSettingOptions[self::OPTION_USER_ROLE_CUSTOM_POST_ID];
			if(PostTypeController::getPostTypeInstanceByPostId($arrSettingOptions[self::OPTION_USER_ROLE_CUSTOM_POST_ID]) instanceof UserProfileFormPostType) {
				if (null !== ($adminModuleInstance = $this->getSameFormByUserRoleCustomTypeId($arrSettingOptions[self::OPTION_USER_ROLE_CUSTOM_POST_ID]))) {
					$this->registerErrorMessage(__('This User Role is already assigned to ', 'ultra-community') . $adminModuleInstance->getOption(self::OPTION_FORM_TITLE));

					return $this->getAllSavedOptions();
				}
			}
		}

		foreach(
			array(
				self::OPTION_IS_DEFAULT_LOGIN_FORM,
				self::OPTION_IS_DEFAULT_REGISTRATION_FORM,
				self::OPTION_IS_DEFAULT_USER_PROFILE_FORM,
				self::OPTION_IS_DEFAULT_FORGOT_PASSWORD_FORM
			) as $isDefaultForm)
		{

			if(MchValidator::isNullOrEmpty($this->getOption($isDefaultForm)))
				continue;

			$arrSettingOptions[$isDefaultForm] = true;
		}

		$this->registerSuccessMessage(__('Your changes were successfully saved!', 'ultra-community'));

		PluginSettingsAdminModule::getInstance()->saveOption(PluginSettingsAdminModule::SETTINGS_LAST_UPDATED, MchHttpRequest::getServerRequestTime());

		return $arrSettingOptions;

	}

	private function getSameFormByUserRoleCustomTypeId($userRoleId)
	{
		if(MchUtils::isNullOrEmpty($this->getCustomPostType()))
			return null;

		foreach (PostTypeController::getPublishedPosts($this->getCustomPostType()->PostType) as $publishedPostType)
		{
			$adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($publishedPostType);
			if( ! $adminModuleInstance instanceof $this )
				continue;

			if($userRoleId == $adminModuleInstance->getOption(self::OPTION_USER_ROLE_CUSTOM_POST_ID))
				if($adminModuleInstance->getSettingKey() !== $this->getSettingKey())
					return $adminModuleInstance;
		}

		return null;
	}


	public function renderModuleSettingsField(array $arrSettingsField)
	{
		$fieldKey   = key( $arrSettingsField );
		$fieldValue = $this->getOption( $fieldKey );
		$fieldDefaultValue = $this->getDefaultOptionValue($fieldKey);

		//$arrUnAssignedUserRoles = $this->getCustomPostType() ? UltraCommHelper::getUnAssignedUserRolesForNewForm($this->getCustomPostType()) : array();

		MchWpUtils::addFilterHook($this->getFieldAttributesFilterName($fieldKey), function( $arrFieldAttributes ) use ($fieldKey, $fieldValue, $fieldDefaultValue) { //$arrUnAssignedUserRoles

			if($fieldKey === BaseFormAdminModule::OPTION_FORM_TITLE){

				$arrFieldAttributes['placeholder'] = __('New Form', 'ultra-community');
				$arrFieldAttributes['class'][] = 'uc-form-title';

				return $arrFieldAttributes;

			}

			if($fieldKey === BaseFormAdminModule::OPTION_FORM_ALIGNMENT){

				$arrFieldAttributes['value']   = empty($fieldValue) ? $fieldDefaultValue : $fieldValue;
				$arrFieldAttributes['class'][] = 'uc-select2';

				$arrOptions = array(
					BaseFormAdminModule::ALIGNMENT_LEFT   => __('Left', 'ultra-community'),
					BaseFormAdminModule::ALIGNMENT_CENTER => __('Center', 'ultra-community'),
					BaseFormAdminModule::ALIGNMENT_RIGHT  => __('Right',  'ultra-community')
				);

				$arrFieldAttributes['options'] = $arrOptions;

				return $arrFieldAttributes;
			}


			if($fieldKey === BaseFormAdminModule::OPTION_USER_ROLE_CUSTOM_POST_ID){

				$arrFieldAttributes['class'][] = 'uc-select2';

				$arrFieldAttributes['value'] = empty($fieldValue) ? null : $fieldValue;
				$arrFieldAttributes['options'] = array(
					//0 => __('All User Roles', 'ultra-community')
				);

				$defaultUserRoleCustomPostId = 0;

				foreach((array)PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_ROLE) as $customPostUserRole)
				{
					$adminModule = PostTypeController::getAssociatedAdminModuleInstance($customPostUserRole);
					if( ! $adminModule instanceof UserRoleAdminModule)
						continue;

					if(UserRoleController::isDefaultAdminRole($adminModule->getOption(UserRoleAdminModule::OPTION_ROLE_SLUG)))
						continue;

//					if($customPostUserRole->PostId ==  $adminModule->getOption(UserRoleAdminModule::OPTION_IS_DEFAULT_USER_ROLE)){
//						$defaultUserRoleCustomPostId = $customPostUserRole->PostId;
//					}

//					if(!isset($arrUnAssignedUserRoles[$customPostUserRole->PostId]) )
//						continue;

					$arrFieldAttributes['options'][$customPostUserRole->PostId] =  $adminModule->getOption(UserRoleAdminModule::OPTION_ROLE_TITLE);
				}

//				if($defaultUserRoleCustomPostId == $arrFieldAttributes['value'])
//				{
//					$arrFieldAttributes['options'] = array( $defaultUserRoleCustomPostId => $arrFieldAttributes['options'][$defaultUserRoleCustomPostId]);
//					$arrFieldAttributes['readonly'] = 'readonly';
//				}
//				else
//				{
//					unset($arrFieldAttributes['options'][$defaultUserRoleCustomPostId]);
//				}

				return $arrFieldAttributes;

			}


			if(in_array($fieldKey, array( BaseFormAdminModule::OPTION_FORM_PRIMARY_BUTTON_COLOR, BaseFormAdminModule::OPTION_FORM_PRIMARY_BUTTON_HOVER_COLOR)))
			{
				$arrFieldAttributes['class'][] = 'uc-color-picker';
				$arrFieldAttributes['value'] = empty($fieldValue) ? $fieldDefaultValue : $fieldValue;

				return $arrFieldAttributes;
			}

			return $arrFieldAttributes;
		});



		return parent::renderModuleSettingsField($arrSettingsField);

	}


	public function saveFormFieldSettings(BaseField $formFieldInstance)
	{

		if(empty($formFieldInstance->Name)){
			throw new UltraCommException(__('Please provide a Name for this field!', 'ultra-community'));
		}


		if($formFieldInstance instanceof ProfileSectionField)
		{
			if(empty($formFieldInstance->Title)){
				throw new UltraCommException(__('Please provide the Section Title!', 'ultra-community'));
			}
		}

		!empty($formFieldInstance->FrontEndVisibilityUserRoles) ?: $formFieldInstance->FrontEndVisibilityUserRoles = array();

		!empty($formFieldInstance->FrontEndVisibility) ?: $formFieldInstance->FrontEndVisibility = null;

		/**
		 * @var $formFieldInstance BaseField
		 */
		$formFieldInstance = MchUtils::filterObjectEmptyProperties( $formFieldInstance, true );

		if(!empty($formFieldInstance->FontAwesomeIcon)){
			MchUtils::stringStartsWith($formFieldInstance->FontAwesomeIcon, 'fa-') ?: $formFieldInstance->FontAwesomeIcon = "fa-{$formFieldInstance->FontAwesomeIcon}";
		}

		if(!empty($formFieldInstance->MinLength) && !MchValidator::isPositiveInteger($formFieldInstance->MinLength)){
			throw new UltraCommException(__('Please enter a number greater than 0 for Minimum Length!', 'ultra-community'));
		}

		if(!empty($formFieldInstance->MaxLength) && !MchValidator::isPositiveInteger($formFieldInstance->MaxLength)){
			throw new UltraCommException(__('Please enter a a number greater than 0 for Maximum Length!', 'ultra-community'));
		}

		if(!empty($formFieldInstance->MinLength) && !empty($formFieldInstance->MaxLength) && ($formFieldInstance->MinLength > $formFieldInstance->MaxLength)){
			throw new UltraCommException(__('Field Minimum Length must be less than or equal to Maximum Length!', 'ultra-community'));
		}


		empty($formFieldInstance->MinLength) ?: $formFieldInstance->MinLength = absint($formFieldInstance->MinLength);;
		empty($formFieldInstance->MaxLength) ?: $formFieldInstance->MaxLength = absint($formFieldInstance->MaxLength);;



		if(!empty($formFieldInstance->FrontEndVisibility))
		{

			if(!MchValidator::isInteger($formFieldInstance->FrontEndVisibility)){
				throw new UltraCommException(__('Invalid value received for FrontEndVisibility!', 'ultra-community'));
			}

			$formFieldInstance->FrontEndVisibility = (int)$formFieldInstance->FrontEndVisibility;

			if($formFieldInstance->FrontEndVisibility === BaseField::VISIBILITY_JUST_USERS_ROLES)
			{
				$arrFieldProperties['FrontEndVisibilityUserRoles'] = array_map('absint', (array)$formFieldInstance->FrontEndVisibilityUserRoles);

				if(empty($arrFieldProperties['FrontEndVisibilityUserRoles'])){
					throw new UltraCommException(__('Please provide which User Roles can see this field on front end!', 'ultra-community'));
				}

				$arrAllowedUserRoles = array();
				foreach($arrFieldProperties['FrontEndVisibilityUserRoles'] as $userRolePostTypeId){
					if(MchUtils::isNullOrEmpty(PostTypeController::getPostTypeInstanceByPostId($userRolePostTypeId))){
						continue;
					}

					$arrAllowedUserRoles[] = $userRolePostTypeId;
				}

				if(empty($arrAllowedUserRoles)){
					throw new UltraCommException(__('Please provide which User Roles can see this field on front end!', 'ultra-community'));
				}

				$formFieldInstance->FrontEndVisibility = array($formFieldInstance->FrontEndVisibility => $arrAllowedUserRoles);
			}
		}

		unset($formFieldInstance->FrontEndVisibilityUserRoles);

		$arrFormFields = (array)$this->getOption(self::OPTION_FORM_FIELDS);


		if(!empty($formFieldInstance->UniqueId) && !empty($formFieldInstance->RegisterFormCustomPostId))
		{
			if($formFieldInstance->UniqueId === $formFieldInstance->RegisterFormFieldUniqueId)
			{
				$formFieldInstance->UniqueId = null;
			}
		}

		if(empty($formFieldInstance->UniqueId))
		{
			$arrFormFields[] = $formFieldInstance;
			$this->saveOption(self::OPTION_FORM_FIELDS, $arrFormFields);
		}

		foreach ( $arrFormFields as &$formField )
		{
			if ( $formField->UniqueId != $formFieldInstance->UniqueId )
				continue;

			$formField = $formFieldInstance;

			break;
		}

		return $this->saveOption(self::OPTION_FORM_FIELDS, $arrFormFields);


	}



	public function saveOption($optionName, $optionValue, $forceBlogOption = true)
	{
		if(is_array($optionValue))
		{
			foreach($optionValue as &$objectInstance)
			{
				if(!is_object($objectInstance))
					continue;

				if($objectInstance instanceof BaseField && empty($objectInstance->UniqueId)){
					$objectInstance->UniqueId = $this->getFieldUniqueId($objectInstance);
				}

				$objectInstance = MchUtils::filterObjectEmptyProperties($objectInstance);

			}
		}

		return parent::saveOption($optionName, $optionValue);

	}

	private function getFieldUniqueId(BaseField $objectInstance)
	{
		$objectInstance->UniqueId = MchWpUtils::sanitizeKey(MchUtils::getRandomHtmlElementId());
		foreach( (array)$this->getOption(self::OPTION_FORM_FIELDS) as $formField)
		{
			if($objectInstance->UniqueId !== $formField->UniqueId)
				continue;

			$objectInstance->UniqueId = $this->getFieldUniqueId($objectInstance);
		}

		return $objectInstance->UniqueId;
	}

	/**
	 * @param string $fieldClassName
	 *
	 * @return BaseField|null
	 */
	public static function getFieldInstanceByShortClassName($fieldClassName)
	{

		$formFieldNameSpace = apply_filters('ultracomm-form-field-class-namespace', 'UltraCommunity\Modules\Forms\FormFields', $fieldClassName);

		if(is_string($fieldClassName) && class_exists($fieldClassName = rtrim($formFieldNameSpace, '\\') . "\\$fieldClassName")){
			return new $fieldClassName;
		}

		return null;

	}



	public static function renderAllFormFieldsTypeForAdminModal(BaseFormAdminModule $formAdminModuleInstance)
	{
		$arrGroupedFormFields = self::getGroupedFormFields();

		foreach ($arrGroupedFormFields as $groupName => &$arrFormFields)
		{
			for($i = 0, $arrSize = count($arrFormFields); $i < $arrSize; ++$i)
			{
				/**
				 * @var $formField BaseField
				 */
				$formField = $arrFormFields[$i];

				switch(true)
				{
					case $formField instanceof CountryField :
						$formField->FontAwesomeIcon  = ('fa-globe');
						break;
					case $formField instanceof LanguageField :
						$formField->FontAwesomeIcon  = ('fa-language');
						break;

					case $formField instanceof UserBioField :
						$formField->FontAwesomeIcon  = ('fa-file-text');
						break;

					case $formField instanceof TextField :
						$formField->FontAwesomeIcon  = 'fa-text-width';
						break;

					case $formField instanceof CheckBoxField :
						$formField->FontAwesomeIcon  = ('fa-check-square-o');
						break;

					case $formField instanceof RadioButtonField :
						$formField->FontAwesomeIcon  = ('fa-check-circle-o');
						break;

					case $formField instanceof TextAreaField :
						$formField->FontAwesomeIcon  = ('fa-paragraph');
						break;

					case $formField instanceof UserPasswordField :
						$formField->FontAwesomeIcon  = ('fa-lock');
						break;

					case $formField instanceof UserNameField :
						$formField->FontAwesomeIcon  = ('fa-user');
						break;

					case $formField instanceof UserNameOrEmailField :
						$formField->FontAwesomeIcon  = ('fa-user');
						break;

					case $formField instanceof UserFirstNameField :
						$formField->FontAwesomeIcon  = ('fa-text-width');
						break;

					case $formField instanceof UserLastNameField :
						$formField->FontAwesomeIcon  = ('fa-text-width');
						break;

					case $formField instanceof UserFullNameField :
						$formField->FontAwesomeIcon  = ('fa-text-width');
						break;

					case $formField instanceof UserDisplayNameField :
						$formField->FontAwesomeIcon  = ('fa-text-width');
						break;

					case $formField instanceof UserNickNameField :
						$formField->FontAwesomeIcon  = ('fa-text-width');
						break;

					case $formField instanceof DropDownField :
						$formField->FontAwesomeIcon  = ('fa-caret-square-o-down');
						break;

					case $formField instanceof UserEmailField :
						$formField->FontAwesomeIcon  = ('fa-envelope');
						break;

					case $formField instanceof UserWebUrlField :
						$formField->FontAwesomeIcon  = ('fa-link');
						break;

					case $formField instanceof SocialNetworkUrlField :
						$formField->FontAwesomeIcon  = ('fa-comments');
						break;

					case $formField instanceof DividerField :
						$formField->FontAwesomeIcon  = ('fa-arrows-h');
						break;

					case $formField instanceof ProfileSectionField :
						$formField->FontAwesomeIcon  = ('fa-arrows-v');
						break;

					case $formField instanceof UserRegistrationDateField :
						$formField->FontAwesomeIcon  = ('fa-calendar');
						break;

					case $formField instanceof SocialConnectField :
						$formField->FontAwesomeIcon  = ('fa-globe');
						break;


//					case $formField instanceof SubscriptionLevelField :
					case $formField instanceof SubscriptionLevelsField :
						$formField->FontAwesomeIcon  = ('fa-globe');
						break;

					case $formField instanceof UserGenderDropDownField :
					case $formField instanceof UserGenderRadioField :
						$formField->FontAwesomeIcon  = ('fa-venus-mars');
						break;

				}
			}

		}


		$htmlCode  = '';

		$htmlCode .= '<div id = "uc-all-form-fields-type-list" class="uc-u-1">';

		unset($arrFormFields);


		if($formAdminModuleInstance->getCustomPostType() && $formAdminModuleInstance->getCustomPostType()->PostType !== PostTypeController::POST_TYPE_REGISTER_FORM)
		{
			foreach ($arrGroupedFormFields as $groupName => &$arrFormFields)
			{
				foreach ($arrFormFields as $index => $formField)
				{
					if($formField instanceof SubscriptionLevelsField)
					{
						unset($arrFormFields[$index]);
					}
				}
			}

			unset($arrFormFields);
		}




		foreach ($arrGroupedFormFields as $groupName => $arrFormFields)
		{


			$htmlCode .= sprintf('<h4>%s</h4>', esc_html($groupName));

			$htmlCode .= '<ul class="uc-g">';

			foreach ($arrFormFields as $formField)
			{
				$htmlCode .= '<li class="uc-u-1-3">';
				$htmlCode .= sprintf('<button class="uc-button uc-button-primary " data-type = "%s"><i class="fa %s"></i>%s</button>', MchUtils::getClassShortNameFromNameSpace($formField), $formField->FontAwesomeIcon, $formField->getDisplayableFieldType());
				$htmlCode .= '</li>';
			}

			$htmlCode .= '</ul>';
		}


		if($formAdminModuleInstance->getCustomPostType() && $formAdminModuleInstance->getCustomPostType()->PostType === PostTypeController::POST_TYPE_USER_PROFILE_FORM)
		{
			if($formAdminModuleInstance->getOption(BaseFormAdminModule::OPTION_USER_ROLE_CUSTOM_POST_ID)){

				$registerFormAdminModule = null;
				foreach(PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_REGISTER_FORM) as $registerPostType){
					if(null === ($registerFormAdminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($registerPostType)))
						continue;

					if($formAdminModuleInstance->getOption(BaseFormAdminModule::OPTION_USER_ROLE_CUSTOM_POST_ID) != $registerFormAdminModuleInstance->getOption(BaseFormAdminModule::OPTION_USER_ROLE_CUSTOM_POST_ID))
						continue;
					$registerFormAdminModule = $registerFormAdminModuleInstance;
					break;
				}


				if(null !== $registerFormAdminModule && !MchUtils::isNullOrEmpty( $arrFormFields  = (array)$registerFormAdminModule->getOption(BaseFormAdminModule::OPTION_FORM_FIELDS) ))
				{
					$htmlCode .= sprintf('<h4>%s</h4>', __('Registration Form Fields', 'ultra-community'));

					$htmlCode .= '<ul class="uc-g">';
					foreach($arrFormFields as $formField)
					{
						$htmlCode .= '<li class="uc-u-1-3">';
						$htmlCode .= sprintf('<button class="uc-button uc-button-primary " data-registerformfielduniqueid="%s" data-registerformcustompostid="%s" data-type = "%s"><i class="fa %s"></i>%s</button>',$formField->UniqueId, $registerFormAdminModule->getCustomPostTypeId(), MchUtils::getClassShortNameFromNameSpace($formField), $formField->FontAwesomeIcon, $formField->Name);
						$htmlCode .= '</li>';

					}
					$htmlCode .= '</ul>';
				}

			}
		}




		$htmlCode .= '</div>';



		return $htmlCode;


	}


	public function renderAllProfileHeaderFieldsForAdminModal()
	{
		$htmlCode  = '';

		$htmlCode .= '<div id = "uc-all-form-header-profile-fields-list" class="uc-u-1">';


		$arrFormFields     = (array)$this->getOption(self::OPTION_FORM_FIELDS);

		foreach ($arrFormFields as $index => &$formField)
		{
			if($formField instanceof UserNameField)
				$formField = null;

			if($formField instanceof UserPasswordField)
				$formField = null;

			if($formField instanceof UserNameOrEmailField)
				$formField = null;

			if($formField instanceof DividerField)
				$formField = null;
		}

		unset($index, $formField);

		$arrFormFields = array_values(array_filter($arrFormFields));
		$arrFormFieldsKeys = array_keys($arrFormFields);

		$socialButtonAdded = false;
		foreach ($arrFormFields as $index => $formField)
		{
			if(0 === $index)
			{
				$htmlCode .= sprintf( '<h4 class="uc-u-1">%s</h4>', __('Profile Form Defined Fields', 'ultra-community') );
				$htmlCode .= '<ul class="uc-g">';
			}

			if($formField instanceof SocialNetworkUrlField){
				if($socialButtonAdded)
					continue;

				$formField->Name = __('Social Network Links', 'ultra-community');
				$formField->FontAwesomeIcon = 'fa-comments';
				$socialButtonAdded = true;
			}

			$htmlCode .= '<li class="uc-u-1-3">';
			$htmlCode .= sprintf('<button class="uc-button uc-button-primary " data-type = "%s" data-fielduniqueid = "%s" ><i class="fa %s"></i>%s</button>', MchUtils::getClassShortNameFromNameSpace($formField), $formField->UniqueId , $formField->FontAwesomeIcon, $formField->Name);
			$htmlCode .= '</li>';

		}

		if(!empty($arrFormFields)) {
			$htmlCode .= '</ul>';
		}

		$htmlCode .= sprintf( '<h4 class="uc-u-1">%s</h4>', __('User Predefined Fields', 'ultra-community') );
		foreach(self::getGroupedFormFields() as $groupName => $arrFields)
		{
			$htmlCode .= '<ul class="uc-g">';

			foreach ($arrFields as $formField)
			{

				if($formField instanceof UserNameField)
					continue;

				if($formField instanceof UserPasswordField)
					continue;

				if($formField instanceof UserNameOrEmailField)
					continue;

				if($formField instanceof DividerField)
					continue;

				if($formField instanceof DividerField || $formField instanceof SocialConnectField || $formField instanceof SubscriptionLevelsField)
					continue;

				if($formField instanceof TextField || $formField instanceof TextAreaField || $formField instanceof CheckBoxField || $formField instanceof RadioButtonField || $formField instanceof DropDownField)
					continue;

				$fieldAlreadyListed = false;
				foreach ($arrFormFields as $userProfileFormField)
				{
					if(!is_a($formField, get_class($userProfileFormField)))
						continue;

					$fieldAlreadyListed = true;
					break;
				}

				if($fieldAlreadyListed)
					continue;

				$htmlCode .= '<li class="uc-u-1-3">';
				$htmlCode .= sprintf('<button class="uc-button uc-button-primary " data-type = "%s" data-fielduniqueid = "%s" >%s</button>', MchUtils::getClassShortNameFromNameSpace($formField),  $formField->UniqueId,  $formField->getDisplayableFieldType());
				$htmlCode .= '</li>';
			}

			$htmlCode .= '</ul>';
		}

		$htmlCode .= '</div>';

		return $htmlCode;

	}

	public static function getGroupedFormFields()
	{

		$arrGroupedFormFields = array(

			__('Standard Fields', 'ultra-community') => array(
				new TextField(),
				new TextAreaField(),
				new CheckBoxField(),
				new DropDownField(),
				new RadioButtonField(),
				new DividerField(),

			),

			__('User Predefined Fields', 'ultra-community') => array(
				new UserNameField(),
				new UserPasswordField(),
				new UserNameOrEmailField(),
				//new UserDisplayNameField(),
				new UserNickNameField(),
				new UserFullNameField(),
				new UserFirstNameField(),
				new UserLastNameField(),
				new UserRegistrationDateField(),

				new UserEmailField(),
				new CountryField(),
				new LanguageField(),
				new UserBioField(),

				new UserWebUrlField(),
				new UserGenderRadioField(),
				//new UserGenderDropDownField(),
				new SocialNetworkUrlField(),
			),


		);

		$arrSpecialFields = array(
			new ProfileSectionField()
		);

		if(ModulesController::isModuleRegistered(ModulesController::MODULE_SOCIAL_CONNECT)){
			$arrSpecialFields[] = new SocialConnectField();
		}

		if(ModulesController::isModuleRegistered(ModulesController::MODULE_USER_SUBSCRIPTIONS)){
			//$arrSpecialFields[] = new SubscriptionLevelField();
			$arrSpecialFields[] = new SubscriptionLevelsField();
		}

		if(!empty($arrSpecialFields)){
			$arrGroupedFormFields[__('Special Fields', 'ultra-community')] = $arrSpecialFields;
		}

		return $arrGroupedFormFields;
	}

	public  function renderModuleSettingsSectionHeader(array $arrSectionInfo)
	{
		$title = $this->getOption(self::OPTION_FORM_TITLE) . ' - ' . __('Settings', 'ultra-community');
		$title = esc_html($title);

		$customPostId   = $this->getCustomPostTypeId();
		$customPostType = $this->getCustomPostType()->PostType;
		$moduleKey    = $this->getSettingKey();

		$embedListElm = <<<EMBED
		<li>
			<button id = "btn-uc-form-action-embed-form-short-code-$moduleKey" data-modulekey = "$moduleKey" data-custompostid = "$customPostId" data-customposttype = "$customPostType" class = "uc-button uc-button-success"><i class="fa fa-code"></i> Embed</button>
		</li>
EMBED;

		if($customPostType === PostTypeController::POST_TYPE_USER_PROFILE_FORM){
			$embedListElm = '';
		}

		$headerOutput = <<<SH
			<div class = "uc-settings-section-header uc-clear">
				<h3>$title</h3>
				<ul class = "uc-settings-module-actions">
					$embedListElm
					<li>
						<button id = "btn-uc-form-action-add-new-form-$moduleKey" data-modulekey = "$moduleKey" data-custompostid = "$customPostId" data-customposttype = "$customPostType" class = "uc-button uc-button-primary"><i class="fa fa-plus"></i> Add New</button>
					</li>

					<li>
						<button id = "btn-uc-form-action-delete-form-$moduleKey" data-modulekey = "$moduleKey" data-custompostid = "$customPostId" data-customposttype = "$customPostType" class = "uc-button uc-button-danger"><i class="fa fa-trash"></i> Delete</button>
					</li>

				</ul>
			</div>
SH;

		echo $headerOutput;
	}


	public function renderFieldSettingsForAdminModal(BaseField $fieldInstance)
	{
		if (empty($fieldInstance))
			return null;

		$outputContent = '';
		$outputContent .= '<form class="uc-form uc-form-stacked uc-form-field-settings uc-g">';

		$outputContent .= MchHtmlUtils::createFormElement(MchHtmlUtils::FORM_ELEMENT_INPUT_HIDDEN, array('type' => 'hidden', 'data-popuptitle' => $fieldInstance->getDisplayableFieldType() . ' ' . __('Field Settings', 'ultra-community'), 'name' => 'FieldTypeClass', 'value' => MchUtils::getClassShortNameFromNameSpace(get_class($fieldInstance))));
		$outputContent .= MchHtmlUtils::createFormElement(MchHtmlUtils::FORM_ELEMENT_INPUT_HIDDEN, array('type' => 'hidden', 'name' => 'UniqueId', 'value' => esc_html($fieldInstance->UniqueId)));
		$outputContent .= MchHtmlUtils::createFormElement(MchHtmlUtils::FORM_ELEMENT_INPUT_HIDDEN, array('type' => 'hidden', 'name' => 'RegisterFormCustomPostId',  'value' => esc_html($fieldInstance->RegisterFormCustomPostId)));
		$outputContent .= MchHtmlUtils::createFormElement(MchHtmlUtils::FORM_ELEMENT_INPUT_HIDDEN, array('type' => 'hidden', 'name' => 'RegisterFormFieldUniqueId', 'value' => esc_html($fieldInstance->RegisterFormFieldUniqueId)));


		if (!empty($fieldInstance->FontAwesomeIcon) && 0 !== strpos($fieldInstance->FontAwesomeIcon, 'fa-')) {
			$fieldInstance->FontAwesomeIcon = 'fa-' . $fieldInstance->FontAwesomeIcon;
		}




		//$outputContent .= MchHtmlUtils::createFormElement(MchHtmlUtils::FORM_ELEMENT_INPUT_HIDDEN, array('name' => 'FontAwesomeIcon', 'value' => $fieldInstance->FontAwesomeIcon));


		$arrFormFields = array(

				'Name'                   => __( 'Name', 'ultra-community' ),
				'Label'                  => __( 'Label', 'ultra-community' ),
				'PlaceHolder'            => __( 'Placeholder', 'ultra-community' ),
				'DefaultValue'           => __( 'Default Value', 'ultra-community' ),
				'MinLength'              => __( 'Minimum Length', 'ultra-community' ),
				'MaxLength'              => __( 'Maximum Length', 'ultra-community' ),

				'FontAwesomeIcon'        => __( 'Field Icon', 'ultra-community' ),
				'FrontEndVisibility'     => __( 'Front End Visibility', 'ultra-community' ),

				'MappedUserMetaKey'      => __( 'Existing User Meta Key', 'ultra-community' ),
				'ForceNewLine'           => '<div class=" uc-u-1 uc-clear"></div>',

				'ErrorMessage'           => __( 'Error Message', 'ultra-community' ),

				'IsRequired'             => __( 'Is Field Required?', 'ultra-community' ),
				'IsEditable'             => __( 'Is Field Editable?', 'ultra-community' ),
				'IsVisibleOnEditProfile' => __( 'Is Visible on Edit Profile', 'ultra-community' ),

		);



		$isForUserProfileForm = ($this instanceof UserProfileFormAdminModule);

		if( !$isForUserProfileForm )
		{
			unset($arrFormFields['FrontEndVisibility'], $arrFormFields['IsVisibleOnEditProfile']);
		}


		if($isForUserProfileForm)
		{
			unset($arrFormFields['FontAwesomeIcon']);
		}

		switch(true)
		{

			case $fieldInstance instanceof CountryField :
			case $fieldInstance instanceof LanguageField:

				unset($arrFormFields['MappedUserMetaKey'], $arrFormFields['MinLength'], $arrFormFields['MaxLength']);

				$arrFormFields['OptionsList'] = __('Field Selectable Options', 'ultra-community');

				foreach ($arrFormFields as  $propertyField => $label)
				{
					if($propertyField === 'ForceNewLine'){
						$outputContent .= $label;continue;
					}

					if(in_array($propertyField, array('IsVisibleOnEditProfile', 'IsRequired', 'IsEditable')) )
					{
						$wrapperClass = ($this instanceof UserProfileFormAdminModule) ? 'uc-u-1-3 uc-text-center' : 'uc-u-1-2 uc-text-center';
						$outputContent .= self::getFieldSettingsOutputContent( $propertyField, $label, $fieldInstance, array(), $wrapperClass );
						continue;
					}

					if($propertyField === 'OptionsList')
					{
						$outputContent .= self::getFieldSettingsOutputContent( $propertyField, $label, $fieldInstance,  array('readonly' => 'readonly') );
						continue;
					}

					$outputContent .= self::getFieldSettingsOutputContent( $propertyField, $label, $fieldInstance,  array() );
				}

				break;


			case $fieldInstance instanceof UserGenderRadioField :

				unset($arrFormFields['MappedUserMetaKey'], $arrFormFields['MinLength'], $arrFormFields['MaxLength'], $arrFormFields['PlaceHolder'], $arrFormFields['FontAwesomeIcon']);

				$arrFormFields['OptionsList'] = __('Field Selectable Options', 'ultra-community');

				foreach ($arrFormFields as  $propertyField => $label)
				{
					if($propertyField === 'ForceNewLine'){
						$outputContent .= $label;continue;
					}

					if(in_array($propertyField, array('IsVisibleOnEditProfile', 'IsRequired', 'IsEditable')) )
					{
						$wrapperClass = ($this instanceof UserProfileFormAdminModule) ? 'uc-u-1-3 uc-text-center' : 'uc-u-1-2 uc-text-center';
						$outputContent .= self::getFieldSettingsOutputContent( $propertyField, $label, $fieldInstance, array(), $wrapperClass );
						continue;
					}

					if($propertyField === 'OptionsList')
					{
						$outputContent .= self::getFieldSettingsOutputContent( $propertyField, $label, $fieldInstance,  array() );
						continue;
					}

					$outputContent .= self::getFieldSettingsOutputContent( $propertyField, $label, $fieldInstance,  array() );
				}

				break;

			case $fieldInstance instanceof UserFullNameField :
			case $fieldInstance instanceof TextField :

				foreach ($arrFormFields as  $propertyField => $label)
				{
					if($propertyField === 'ForceNewLine'){
						$outputContent .= $label;continue;
					}

					if(in_array($propertyField, array('IsVisibleOnEditProfile', 'IsRequired', 'IsEditable')) )
					{
						$wrapperClass = ($this instanceof UserProfileFormAdminModule) ? 'uc-u-1-3 uc-text-center' : 'uc-u-1-2 uc-text-center';
						$outputContent .= self::getFieldSettingsOutputContent( $propertyField, $label, $fieldInstance, array(), $wrapperClass );
					}
					else
					{
						$outputContent .= self::getFieldSettingsOutputContent( $propertyField, $label, $fieldInstance );
					}
				}


				break;

			case $fieldInstance instanceof UserNameOrEmailField :
			case $fieldInstance instanceof UserNameField :
			case $fieldInstance instanceof UserFirstNameField :
			case $fieldInstance instanceof UserLastNameField :
			case $fieldInstance instanceof UserNickNameField :
			case $fieldInstance instanceof UserEmailField :
			case $fieldInstance instanceof UserWebUrlField :

				unset($arrFormFields['MappedUserMetaKey']);

				foreach ($arrFormFields as  $propertyField => $label)
				{
					if($propertyField === 'ForceNewLine'){
						$outputContent .= $label;continue;
					}

					if(in_array($propertyField, array('IsVisibleOnEditProfile', 'IsRequired', 'IsEditable')) )
					{
						$wrapperClass = ($this instanceof UserProfileFormAdminModule) ? 'uc-u-1-3 uc-text-center' : 'uc-u-1-2 uc-text-center';
						$outputContent .= self::getFieldSettingsOutputContent( $propertyField, $label, $fieldInstance, array(), $wrapperClass );
					}
					else
					{
						$outputContent .= self::getFieldSettingsOutputContent( $propertyField, $label, $fieldInstance );
					}
				}

				break;


			case $fieldInstance instanceof UserRegistrationDateField :

				$arrFormFields = MchUtils::addArrayKeyValueAfterSpecificKey('DefaultValue', $arrFormFields, 'FormatPattern', __('Display Format', 'ultra-community'));

				unset($arrFormFields['MappedUserMetaKey'], $arrFormFields['MinLength'], $arrFormFields['MaxLength'], $arrFormFields['DefaultValue']);

				foreach ($arrFormFields as  $propertyField => $label)
				{
					if($propertyField === 'ForceNewLine'){
						$outputContent .= $label;continue;
					}

					if(in_array($propertyField, array('IsVisibleOnEditProfile', 'IsRequired', 'IsEditable')) )
					{
						$wrapperClass = ($this instanceof UserProfileFormAdminModule) ? 'uc-u-1-3 uc-text-center' : 'uc-u-1-2 uc-text-center';
						$outputContent .= self::getFieldSettingsOutputContent( $propertyField, $label, $fieldInstance, array(), $wrapperClass );
					}
					else
					{
						$outputContent .= self::getFieldSettingsOutputContent( $propertyField, $label, $fieldInstance );
					}
				}


				break;

			case $fieldInstance instanceof UserBioField :
			case $fieldInstance instanceof TextAreaField :

				if($fieldInstance instanceof UserBioField){
					unset($arrFormFields['MappedUserMetaKey']);
				}

				if($this instanceof UserProfileFormAdminModule){
					$arrFormFields['IsHtmlAllowed'] = __( 'Is HTML Allowed?', 'ultra-community' );
				}

				foreach ($arrFormFields as  $propertyField => $label)
				{
					if($propertyField === 'ForceNewLine'){
						$outputContent .= $label;continue;
					}


					if(in_array($propertyField, array('IsVisibleOnEditProfile', 'IsRequired', 'IsEditable', 'IsHtmlAllowed')) )
					{
						$wrapperClass = ($this instanceof UserProfileFormAdminModule) ? 'uc-u-1-4 uc-text-center' : 'uc-u-1-2 uc-text-center';
						$outputContent .= self::getFieldSettingsOutputContent( $propertyField, $label, $fieldInstance, array(), $wrapperClass );
					}
					else
					{
						$outputContent .= self::getFieldSettingsOutputContent( $propertyField, $label, $fieldInstance );
					}
				}





				break;

			case $fieldInstance instanceof UserPasswordField :

				unset($arrFormFields['IsVisibleOnEditProfile'], $arrFormFields['IsRequired'], $arrFormFields['IsEditable']);
				unset($arrFormFields['MappedUserMetaKey'], $arrFormFields['FrontEndVisibility'], $arrFormFields['DefaultValue']);

				foreach ($arrFormFields as  $propertyField => $label)
				{
					if($propertyField === 'ForceNewLine'){
						$outputContent .= $label;continue;
					}

					if(in_array($propertyField, array('IsVisibleOnEditProfile', 'IsRequired', 'IsEditable')) )
					{
						$wrapperClass = ($this instanceof UserProfileFormAdminModule) ? 'uc-u-1-3 uc-text-center' : 'uc-u-1-2 uc-text-center';
						$outputContent .= self::getFieldSettingsOutputContent( $propertyField, $label, $fieldInstance, array(), $wrapperClass );
					}
					else
					{
						$outputContent .= self::getFieldSettingsOutputContent( $propertyField, $label, $fieldInstance );
					}
				}

				break;

//			case $fieldInstance instanceof UserDisplayNameField :
//				$fieldInstance->FontAwesomeIcon  = ('fa-text-width');
//				break;

			case $fieldInstance instanceof CheckBoxField :
			case $fieldInstance instanceof DropDownField :
			case $fieldInstance instanceof RadioButtonField :

				unset($arrFormFields['MappedUserMetaKey'], $arrFormFields['MinLength'], $arrFormFields['MaxLength']);

				$arrFormFields['OptionsList'] = __('Field Selectable Options', 'ultra-community');

				foreach ($arrFormFields as  $propertyField => $label)
				{
					if($propertyField === 'ForceNewLine'){
						$outputContent .= $label;continue;
					}

					if(in_array($propertyField, array('IsVisibleOnEditProfile', 'IsRequired', 'IsEditable')) )
					{
						$wrapperClass = ($this instanceof UserProfileFormAdminModule) ? 'uc-u-1-3 uc-text-center' : 'uc-u-1-2 uc-text-center';
						$outputContent .= self::getFieldSettingsOutputContent( $propertyField, $label, $fieldInstance, array(), $wrapperClass );
					}
					else
					{
						$outputContent .= self::getFieldSettingsOutputContent( $propertyField, $label, $fieldInstance );
					}
				}

				break;


			case $fieldInstance instanceof SocialNetworkUrlField :

				unset($arrFormFields['MappedUserMetaKey'], $arrFormFields['MinLength'], $arrFormFields['MaxLength']);

				$arrFormFields['NetworkId'] = __('Select Social Network', 'ultra-community');

				foreach ($arrFormFields as  $propertyField => $label)
				{
					if($propertyField === 'ForceNewLine'){
						$outputContent .= $label;continue;
					}

					if(in_array($propertyField, array('IsVisibleOnEditProfile', 'IsRequired', 'IsEditable')) )
					{
						$wrapperClass = ($this instanceof UserProfileFormAdminModule) ? 'uc-u-1-3 uc-text-center' : 'uc-u-1-2 uc-text-center';
						$outputContent .= self::getFieldSettingsOutputContent( $propertyField, $label, $fieldInstance, array(), $wrapperClass );
					}
					else
					{
						$outputContent .= self::getFieldSettingsOutputContent( $propertyField, $label, $fieldInstance );
					}
				}


				break;

			case $fieldInstance instanceof DividerField :

				$arrFormFields = array(
					'Name'         => __('Name', 'ultra-community'),
					'Text'         => __('Text', 'ultra-community'),
					'LineHeight'   => __('Line Height', 'ultra-community'),
					'LineColor'    => __('Line Color', 'ultra-community'),
					'MarginTop'    => __('Margin Top', 'ultra-community'),
					'MarginBottom' => __('Margin Bottom', 'ultra-community'),
					'TextColor'    => __('Text Color', 'ultra-community'),
					'ForceNewLine' => '<div class=" uc-u-1 uc-clear"></div>',
					'TextAlign'    => __('Text Align', 'ultra-community'),
					'LineStyle'    => __('Line Style', 'ultra-community'),
					'FrontEndVisibility' => __( 'Front End Visibility', 'ultra-community' ),
				);

				foreach ($arrFormFields as  $propertyField => $fieldLabel)
				{
					if($propertyField === 'ForceNewLine'){
						$outputContent .= $fieldLabel;continue;
					}

					if('FrontEndVisibility' !== $propertyField)
					{
						$toolTipText = BaseField::getPropertyHelpText( $propertyField );
						empty( $toolTipText ) ?: $fieldLabel .= '<i class="fa fa-info-circle  uc-tooltip" title="' . $toolTipText . '"></i>';
					}

					$fieldAttrs = array(
						'id'    => $propertyField,
						'name'  => $propertyField,
						'class' => 'uc-input-1',
						'value' => property_exists($fieldInstance, $propertyField) ? $fieldInstance->{$propertyField} : '',
					);

					if('TextAlign' === $propertyField){
						$fieldAttrs['class']   = array('uc-input-1', 'uc-select2');
						$fieldAttrs['options'] = array('center' => __('Center', 'ultra-community'), 'left' => __('Left', 'ultra-community'), 'right' => __('Right', 'ultra-community'));

					}

					if('LineStyle' === $propertyField){
						$fieldAttrs['class']   = array('uc-input-1', 'uc-select2');
						$fieldAttrs['options'] = array('solid' => __('Solid', 'ultra-community'), 'dotted' => __('Dotted', 'ultra-community'), 'dashed' => __('Dashed', 'ultra-community'), 'none' => __('None', 'ultra-community'));
					}

//					if('TextColor' === $propertyField){
//						!empty($fieldAttrs['value']) ?: $fieldAttrs['value'] = ColorsAppearanceAdminModule::getInstance()->getOption(ColorsAppearanceAdminModule::OPTION_SITE_PRIMARY_COLOR);
//					}

					if('FrontEndVisibility' === $propertyField)
					{
						$outputContent .= $this->getFieldSettingsOutputContent($propertyField, $fieldLabel, $fieldInstance);
						continue;
					}


					$outputContent .= '<div class="uc-u-1-2 uc-control-group">';
					$outputContent .= '<label  for="' . $propertyField . '">' . $fieldLabel . '</label>';
					$outputContent .= MchHtmlUtils::createFormElement(empty($fieldAttrs['options']) ? MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT : MchHtmlUtils::FORM_ELEMENT_SELECT, $fieldAttrs);
					$outputContent .= '</div>';

				}


				break;


			case $fieldInstance instanceof ProfileSectionField :

				$arrFormFields = array(
						'Name'            => __('Name', 'ultra-community'),
						'Title'           => __('Section Title', 'ultra-community'),
						'FontAwesomeIcon' => __('Field Icon', 'ultra-community' ),
				);

				foreach ($arrFormFields as  $propertyField => $fieldLabel)
				{
					$fieldAttrs = array(
							'id'    => $propertyField,
							'name'  => $propertyField,
							'class' => 'uc-input-1',
							'value' => property_exists($fieldInstance, $propertyField) ? $fieldInstance->{$propertyField} : '',
					);


					if('FontAwesomeIcon' === $propertyField )
					{
						!empty($fieldAttrs['value']) ?: $fieldAttrs['value'] = 'fa-align-left';

						$fieldAttrs['class'] = 'uc-input-1 uc-fa-simple-icon-picker';
					}


					$outputContent .= '<div class="uc-u-1-2 uc-control-group">';
					$outputContent .= '<label  for="' . $propertyField . '">' . $fieldLabel . '</label>';
					$outputContent .= MchHtmlUtils::createFormElement( MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT, $fieldAttrs );
					$outputContent .= '</div>';

				}


				break;

			case $fieldInstance instanceof SocialConnectField :

				if(!ModulesController::isModuleRegistered(ModulesController::MODULE_SOCIAL_CONNECT))
					return '';


				foreach ($arrFormFields as  $propertyField => $fieldLabel){
					if($propertyField !== 'Name') unset($arrFormFields[$propertyField]);
				}

				$arrFormFields['SocialConfigPostTypeId'] =  __('Select Social Connect Configuration', 'ultra-community');



				foreach ($arrFormFields as  $propertyField => $fieldLabel)
				{

					$fieldAttrs = array(
							'id'    => $propertyField,
							'name'  => $propertyField,
							'class' => 'uc-input-1',
							'value' => property_exists($fieldInstance, $propertyField) ? $fieldInstance->{$propertyField} : '',
					);

					if('SocialConfigPostTypeId' === $propertyField)
					{
						foreach(PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_SOCIAL_CONNECT) as $socialConnectPostType)
						{
							if(MchUtils::isNullOrEmpty($adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($socialConnectPostType)))
								continue;

							$fieldAttrs['options'][$socialConnectPostType->PostId] = $adminModuleInstance->getOption(SocialConnectAdminModule::OPTION_CONFIGURATION_TITLE);
						}

						$fieldAttrs['class'] = array('uc-input-1', 'uc-select2');
					}


					$outputContent .= '<div class="uc-u-1-2 uc-control-group">';
					$outputContent .= '<label  for="' . $propertyField . '">' . $fieldLabel . '</label>';
					$outputContent .= MchHtmlUtils::createFormElement(empty($fieldAttrs['options']) ? MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT : MchHtmlUtils::FORM_ELEMENT_SELECT, $fieldAttrs);
					$outputContent .= '</div>';

				}


				break;



			case $fieldInstance instanceof SubscriptionLevelsField :

				foreach ($arrFormFields as  $propertyField => $fieldLabel){
					if($propertyField !== 'Name') unset($arrFormFields[$propertyField]);
				}


				$arrFormFields['SubscriptionLevels'] =  __('Select Subscription Level', 'ultra-community');

				foreach ($arrFormFields as  $propertyField => $fieldLabel)
				{

					$fieldAttrs = array(
							'id'    => $propertyField,
							'name'  => $propertyField,
							'class' => 'uc-input-1',
							'value' => property_exists($fieldInstance, $propertyField) ? $fieldInstance->{$propertyField} : '',
					);


					if('SubscriptionLevels' === $propertyField)
					{
						foreach(UserSubscriptionController::getAllActiveSubscriptionLevels() as $activeSubscriptionLevel)
						{
							$fieldAttrs['options'][$activeSubscriptionLevel->SubscriptionLevelId] = $activeSubscriptionLevel->Name;
						}
						$fieldAttrs['class'] = array('uc-input-1', 'uc-select2', 'uc-select2-multiple');

						$fieldAttrs['multiple'] = 'multiple';

					}

					$outputContent .= '<div class="uc-u-1-2 uc-control-group">';
					$outputContent .= '<label  for="' . $propertyField . '">' . $fieldLabel . '</label>';
					$outputContent .= MchHtmlUtils::createFormElement(empty($fieldAttrs['options']) ? MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT : MchHtmlUtils::FORM_ELEMENT_SELECT, $fieldAttrs);
					$outputContent .= '</div>';

				}


				break;
		}





		$outputContent .= '</form>';

		return $outputContent;

	}



	private static function getFieldSettingsOutputContent($fieldKeyName, $fieldLabel, BaseField $fieldInstance, array $arrAdditionalAttrs = array(), $wrapperHtmlClass = 'uc-u-1-2')
	{
		$outputContent = '';
		$toolTipText = BaseField::getPropertyHelpText($fieldKeyName);
		empty($toolTipText) ?: $fieldLabel .= '<i class="fa fa-info-circle  uc-tooltip" title="' . $toolTipText . '"></i>';

		switch ($fieldKeyName)
		{

			case "Name" :
			case "Label":
			case "PlaceHolder" :
			case "DefaultValue" :
			case "MinLength":
			case "MaxLength":

				$fieldAttrs = array(
					'id'    => $fieldKeyName,
					'name'  => $fieldKeyName,
					'class' => 'uc-input-1',
					'value' => property_exists($fieldInstance, $fieldKeyName) ? $fieldInstance->{$fieldKeyName} : '',
				);

				$fieldAttrs = wp_parse_args($arrAdditionalAttrs, $fieldAttrs);

				$outputContent .= '<div class="uc-u-1-2 uc-control-group">';
				$outputContent .= '<label  for="' . $fieldKeyName . '">' . $fieldLabel . '</label>';
				$outputContent .= MchHtmlUtils::createFormElement(MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT, $fieldAttrs);
				$outputContent .= '</div>';

				break;

			case 'ErrorMessage' :

				$fieldAttrs = array(
					'name'  => $fieldKeyName,
					'class' => 'uc-input-1',
					'value' => property_exists($fieldInstance, $fieldKeyName) ? $fieldInstance->{$fieldKeyName} : '',
				);

				$fieldAttrs = wp_parse_args($arrAdditionalAttrs, $fieldAttrs);

				$outputContent .= '<div class="uc-u-1 uc-control-group">';
				$outputContent .= '<label  for="' . $fieldKeyName . '">' . $fieldLabel . '</label>';
				$outputContent .= MchHtmlUtils::createFormElement(MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT, $fieldAttrs);
				$outputContent .= '</div>';

				break;

			case 'MappedUserMetaKey' :

				$fieldAttrs = array(
					'name'  => $fieldKeyName,
					'class' => 'uc-input-1',
					'value' => property_exists($fieldInstance, $fieldKeyName) ? $fieldInstance->{$fieldKeyName} : '',
				);

				$fieldAttrs = wp_parse_args($arrAdditionalAttrs, $fieldAttrs);

				$outputContent .= '<div class="uc-u-1-2 uc-control-group">';
				$outputContent .= '<label  for="' . $fieldKeyName . '">' . $fieldLabel . '</label>';
				$outputContent .= MchHtmlUtils::createFormElement(MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT, $fieldAttrs);
				$outputContent .= '</div>';

				break;

			case 'FontAwesomeIcon' :

				$outputContent .= '<div class="uc-u-1-2 uc-control-group">';
				$outputContent .= '<label>' . $fieldLabel . '</label>';

				$fieldAttrs = array(
						'name'  => $fieldKeyName,
						'class' => 'uc-input-1 uc-fa-simple-icon-picker',
						'value' => property_exists($fieldInstance, $fieldKeyName) ? $fieldInstance->{$fieldKeyName} : '',
				);

				$fieldAttrs = wp_parse_args($arrAdditionalAttrs, $fieldAttrs);

				$outputContent .= MchHtmlUtils::createFormElement(MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT, $fieldAttrs);

				$outputContent .= '</div>';

				break;

			case 'IsRequired'        :
			case 'IsEditable'        :
			case 'IsHtmlAllowed'     :
			case 'IsVisibleOnEditProfile' :

				$fieldAttrs = array(
					'name'  => $fieldKeyName,
					'class' => 'uc-input-1',
					'value' => property_exists($fieldInstance, $fieldKeyName) ? $fieldInstance->{$fieldKeyName} : '',
				);

				$fieldAttrs = wp_parse_args($arrAdditionalAttrs, $fieldAttrs);

				$outputContent .= "<div class=\"$wrapperHtmlClass uc-control-group\">";
				$outputContent .= '<label class="uc-checkbox-holder">';
				$outputContent .= MchHtmlUtils::createFormElement(MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX, $fieldAttrs);

				$outputContent .= '<span class="uc-checkbox"></span>';
				$outputContent .= '<span class="uc-checkbox-text">' . $fieldLabel . '</span>';

				$outputContent .= '</label>';

				$outputContent .= '</div>';

				break;


			case 'FrontEndVisibility' :


				if (empty($fieldInstance->FrontEndVisibility)) {
					$fieldInstance->FrontEndVisibility = 0;
				}



				$fieldVisibilityUserRoles = array();
				$fieldVisibilityValue = 0;

				if (!is_array($fieldInstance->FrontEndVisibility)) {
					$fieldVisibilityValue = (int)$fieldInstance->FrontEndVisibility;
				} else {
					$fieldVisibilityValue = key($fieldInstance->FrontEndVisibility);
					$fieldVisibilityUserRoles = $fieldInstance->FrontEndVisibility[$fieldVisibilityValue];
				}

				!empty($fieldVisibilityValue) ?: $fieldVisibilityValue = BaseField::VISIBILITY_EVERYBODY;


				$fieldAttrs = array(
					'name'    => 'FrontEndVisibility',
					'class'   => array('uc-input-1-2', 'uc-select2'),
					'value'   => $fieldVisibilityValue,
					'options' => array(
						BaseField::VISIBILITY_EVERYBODY        => __('Everybody', 'ultra-community'),
						BaseField::VISIBILITY_NOBODY           => __('Nobody', 'ultra-community'),
						BaseField::VISIBILITY_LOGGED_IN_USERS  => __('All logged in members', 'ultra-community'),

						BaseField::VISIBILITY_JUST_USERS_ROLES => __('Just following user roles', 'ultra-community'),
					)
				);

				$fieldAttrs = wp_parse_args($arrAdditionalAttrs, $fieldAttrs);

				$outputContent .= '<div class="uc-u-1-2 uc-control-group">';
				$outputContent .= '<label  for="' . $fieldKeyName . '">' . $fieldLabel . '</label>';
				$outputContent .= MchHtmlUtils::createFormElement(MchHtmlUtils::FORM_ELEMENT_SELECT, $fieldAttrs);


				$fieldAttrs = array(
					'name'     => 'FrontEndVisibilityUserRoles',
					'class'    => array('uc-input-1-2', 'uc-select2-multiple'),
					'multiple' => 'multiple',
					'value'    => $fieldVisibilityUserRoles
				);

				foreach ((array)PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_ROLE) as $customPostUserRole) {
					$adminModule = PostTypeController::getAssociatedAdminModuleInstance($customPostUserRole);
					if (!$adminModule instanceof UserRoleAdminModule)
						continue;

					$fieldAttrs['options'][$customPostUserRole->PostId] = $adminModule->getOption(UserRoleAdminModule::OPTION_ROLE_TITLE);
				}


				$outputContent .= empty($fieldVisibilityUserRoles) ? '<div style="display: none;">' : '<div>';
				$outputContent .= MchHtmlUtils::createFormElement(MchHtmlUtils::FORM_ELEMENT_SELECT, $fieldAttrs);
				$outputContent .= '</div>';

				$outputContent .= '</div>';

				break;


			case 'OptionsList' :

				$fieldText = implode(PHP_EOL, (array)$fieldInstance->OptionsList);


				$fieldAttrs = array(
						'name'  => $fieldKeyName,
						'class' => 'uc-input-1-1',
						'value' => $fieldText,
				);

				$fieldAttrs = wp_parse_args($arrAdditionalAttrs, $fieldAttrs);

				$outputContent .= '<div class="uc-u-1-2 uc-control-group">';
				$outputContent .= '<label  for="' . $fieldKeyName . '">' . $fieldLabel . '</label>';
					$outputContent .= MchHtmlUtils::createFormElement(MchHtmlUtils::FORM_ELEMENT_INPUT_TEXTAREA, $fieldAttrs);

				$outputContent .= '</div>';


				break;

			case 'NetworkId' : // Social Network

				$fieldAttrs = array(
					'name'  => $fieldKeyName,
					'class' => 'uc-input-1-1',
					'value' => property_exists($fieldInstance, $fieldKeyName) ? $fieldInstance->{$fieldKeyName} : '',
				);

				$fieldAttrs = wp_parse_args($arrAdditionalAttrs, $fieldAttrs);

				$outputContent .= '<div class="uc-u-1-2 uc-control-group">';
				$outputContent .= '<label  for="' . $fieldKeyName . '">' . $fieldLabel . '</label>';

				if($fieldInstance instanceof SocialNetworkUrlField)
				{

					$fieldAttrs['class']   = (array)$fieldAttrs['class'];
					$fieldAttrs['class'][] = 'uc-select2';
					$fieldAttrs['options'] = array();
					foreach ($fieldInstance->getAllNetworks() as $socialNetwork)
					{
						$fieldAttrs['options'][$socialNetwork->Id] = $socialNetwork->Name;
					}

					$outputContent .= MchHtmlUtils::createFormElement(MchHtmlUtils::FORM_ELEMENT_SELECT, $fieldAttrs);
				}

				$outputContent .= '</div>';

				break;

			case 'FormatPattern':

				$fieldAttrs = array(
					'name'  => $fieldKeyName,
					'class' => 'uc-input-1-1',
					'value' => property_exists($fieldInstance, $fieldKeyName) ? $fieldInstance->{$fieldKeyName} : '',
				);

				$fieldAttrs = wp_parse_args($arrAdditionalAttrs, $fieldAttrs);

				$outputContent .= '<div class="uc-u-1-2 uc-control-group">';
				$outputContent .= '<label  for="' . $fieldKeyName . '">' . $fieldLabel . '</label>';

				$arrDateFormatPatterns = array(
					'F j, Y', 'j F, Y', 'M j, Y', 'j M, Y', 'F Y', 'M Y'
				);

				$fieldAttrs['class']   = (array)$fieldAttrs['class'];
				$fieldAttrs['class'][] = 'uc-select2';
				$fieldAttrs['options'] = array();
				foreach ($arrDateFormatPatterns as $formatPattern)
				{
					$fieldAttrs['options'][$formatPattern] = date($formatPattern);
					if(false !== strpos($formatPattern, ',')) {
						$formatPattern = str_replace(',', '', $formatPattern);
						$fieldAttrs['options'][$formatPattern] = date($formatPattern);
					}

				}

				$outputContent .= MchHtmlUtils::createFormElement(MchHtmlUtils::FORM_ELEMENT_SELECT, $fieldAttrs);

				$outputContent .= '</div>';

				break;


		}


		return $outputContent;


	}




}

