<?php
/**
 * Copyright (c) 2017 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Modules\MembersDirectory;
use UltraCommunity\Admin\Pages\BaseAdminPage;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\MchLib\Utils\MchHtmlUtils;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\Repository\WpPostRepository;
use UltraCommunity\Modules\BaseAdminModule;
use UltraCommunity\Modules\Forms\BaseForm\BaseFormAdminModule;
use UltraCommunity\Modules\Forms\FormFields\BaseField;
use UltraCommunity\Modules\Forms\FormFields\DividerField;
use UltraCommunity\Modules\Forms\FormFields\ProfileSectionField;
use UltraCommunity\Modules\Forms\FormFields\SocialNetworkUrlField;
use UltraCommunity\Modules\Forms\FormFields\UserDisplayNameField;
use UltraCommunity\Modules\Forms\FormFields\UserNameField;
use UltraCommunity\Modules\Forms\FormFields\UserNameOrEmailField;
use UltraCommunity\Modules\Forms\FormFields\UserNickNameField;
use UltraCommunity\Modules\Forms\FormFields\UserPasswordField;
use UltraCommunity\Modules\UserRole\UserRoleAdminModule;
use UltraCommunity\UltraCommHooks;

class MembersDirectoryAdminModule extends BaseAdminModule
{

	CONST USER_STATS_COUNTER_POSTS     = 'posts';
	CONST USER_STATS_COUNTER_COMMENTS  = 'comments';

	CONST OPTION_DIRECTORY_TITLE      = 'Name';

	CONST OPTION_ASSIGNED_PAGE_ID   = 'AssignedPageId';


	CONST OPTION_LIST_USER_ROLES = 'LUR';
	CONST OPTION_IS_DEFAULT_MEMBERS_DIRECTORY = 'ISDMD';

	CONST OPTION_PAGINATION_TYPE = 'PT';
	CONST OPTION_PROFILE_CARD_BACKGROUND_COLOR = 'PCBC';

	CONST OPTION_USER_CARD_TAGLINE_FIELDS = 'UCTF';
	CONST OPTION_USER_CARD_BELOW_TAGLINE_FIELDS = 'UCBTF';

	CONST OPTION_USER_ACTIVITY_STATS_COUNTERS = 'UASC';

	CONST OPTION_DIRECTORY_CUSTOM_CSS = 'DCCSS';

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

			self::OPTION_DIRECTORY_TITLE => array(
				'Value'      => __('New Members Directory', 'ultra-community'),
				'LabelText'  => __('Members Directory Name', 'ultra-community'),
				'HelpText'   => __('The title of this members directory', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
			),

			self::OPTION_ASSIGNED_PAGE_ID => array(
					'Value'      => null,
					'LabelText'  => __('Assigned Page', 'ultra-community'),
					'HelpText'   => __('The assigned page for this members directory. This value cannot be changed !', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT
			),

//			self::OPTION_ASSIGNED_PAGE_SLUG  => array(
//				'Value' => NULL,
//				'LabelText'  => __('Assigned Page Slug', 'ultra-community'),
//				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
//			),

			self::OPTION_LIST_USER_ROLES => array(
				'Value'      => null,
				'LabelText'  => __('List The Following User Roles', 'ultra-community'),
				'HelpText'   => __('The list of user roles that should be listed in this members directory', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT
			),

//			self::OPTION_ALLOWED_USER_ROLES => array( //move this to content protect
//				'Value'      => null,
//				'LabelText'  => __('User Roles Allowed to Access', 'ultra-community'),
//				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
//				'HelpText'   => __('The list of user roles that can view this directory. Leave this blank if you want this Member Directory to be publicly visible', 'ultra-community')
//			),

			self::OPTION_USER_CARD_TAGLINE_FIELDS => array(
				'Value'      => null,
				'LabelText'  => __('Fields To Display In User Tagline', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
				'HelpText'   => __('Choose fields to display in user tagline - the line underneath User Profile Name. Those fields will be displayed one per line in the same order you add them!', 'ultra-community'),
			),

			self::OPTION_USER_CARD_BELOW_TAGLINE_FIELDS => array(
				'Value'      => null,
				'LabelText'  => __('Fields To Display Below User Tagline', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
				'HelpText'   => __('Choose the fields to displayed below user tagline. Those fields will be displayed on only ONE line in the same order you add them!', 'ultra-community'),
			),

			self::OPTION_USER_ACTIVITY_STATS_COUNTERS => array(
					'Value'      => array(self::USER_STATS_COUNTER_POSTS, self::USER_STATS_COUNTER_COMMENTS),
					'LabelText'  => __('User Activity Stats Counters', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
					'HelpText'   => __('Choose the user activity counters that will be displayed at the bottom of user profile card', 'ultra-community'),
			),

			self::OPTION_DIRECTORY_CUSTOM_CSS => array(
				'Value'      => null,
				'LabelText'  => __('Directory Custom CSS', 'ultra-community'),
				'HelpText'   => __('Add your directory custom CSS', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXTAREA,

			),


		);

		return $arrDefaultSettingOptions;

	}

	public function validateModuleSettingsFields($arrSettingOptions)
	{
		$arrSettingOptions = $this->sanitizeModuleSettings($arrSettingOptions);

		if(empty($arrSettingOptions[self::OPTION_DIRECTORY_TITLE]))
		{
			$this->registerErrorMessage(__('Please provide a Name for this directory!', 'ultra-community'));
			return $this->getAllSavedOptions();
		}


		if(isset($arrSettingOptions[self::OPTION_USER_CARD_TAGLINE_FIELDS]) &&  !empty($_POST[ 'ordered-' . $this->getSettingKey() ][self::OPTION_USER_CARD_TAGLINE_FIELDS][0]))
		{
			$arrSettingOptions[self::OPTION_USER_CARD_TAGLINE_FIELDS] = explode(',', $_POST[ 'ordered-' . $this->getSettingKey() ][self::OPTION_USER_CARD_TAGLINE_FIELDS][0]);

			$arrSettingOptions[self::OPTION_USER_CARD_TAGLINE_FIELDS] = array_filter($arrSettingOptions[self::OPTION_USER_CARD_TAGLINE_FIELDS]);

			$arrSettingOptions[self::OPTION_USER_CARD_TAGLINE_FIELDS] = array_map('sanitize_text_field', $arrSettingOptions[self::OPTION_USER_CARD_TAGLINE_FIELDS]);

			$arrSettingOptions[self::OPTION_USER_CARD_TAGLINE_FIELDS] = array_values($arrSettingOptions[self::OPTION_USER_CARD_TAGLINE_FIELDS]);
		}


		if(isset($arrSettingOptions[self::OPTION_USER_CARD_BELOW_TAGLINE_FIELDS]) && !empty($_POST[ 'ordered-' . $this->getSettingKey() ][self::OPTION_USER_CARD_BELOW_TAGLINE_FIELDS][0]))
		{
			$arrSettingOptions[self::OPTION_USER_CARD_BELOW_TAGLINE_FIELDS] = explode(',', $_POST[ 'ordered-' . $this->getSettingKey() ][self::OPTION_USER_CARD_BELOW_TAGLINE_FIELDS][0]);

			$arrSettingOptions[self::OPTION_USER_CARD_BELOW_TAGLINE_FIELDS] = array_filter($arrSettingOptions[self::OPTION_USER_CARD_BELOW_TAGLINE_FIELDS]);

			$arrSettingOptions[self::OPTION_USER_CARD_BELOW_TAGLINE_FIELDS] = array_map('sanitize_text_field', $arrSettingOptions[self::OPTION_USER_CARD_BELOW_TAGLINE_FIELDS]);

			$arrSettingOptions[self::OPTION_USER_CARD_BELOW_TAGLINE_FIELDS] = array_values($arrSettingOptions[self::OPTION_USER_CARD_BELOW_TAGLINE_FIELDS]);
		}


		if(isset($arrSettingOptions[self::OPTION_USER_ACTIVITY_STATS_COUNTERS]) && !empty($_POST[ 'ordered-' . $this->getSettingKey() ][self::OPTION_USER_ACTIVITY_STATS_COUNTERS][0]))
		{
			$arrSettingOptions[self::OPTION_USER_ACTIVITY_STATS_COUNTERS] = explode(',', $_POST[ 'ordered-' . $this->getSettingKey() ][self::OPTION_USER_ACTIVITY_STATS_COUNTERS][0]);

			$arrSettingOptions[self::OPTION_USER_ACTIVITY_STATS_COUNTERS] = array_filter($arrSettingOptions[self::OPTION_USER_ACTIVITY_STATS_COUNTERS]);

			$arrSettingOptions[self::OPTION_USER_ACTIVITY_STATS_COUNTERS] = array_map('sanitize_text_field', $arrSettingOptions[self::OPTION_USER_ACTIVITY_STATS_COUNTERS]);

			$arrSettingOptions[self::OPTION_USER_ACTIVITY_STATS_COUNTERS] = array_values($arrSettingOptions[self::OPTION_USER_ACTIVITY_STATS_COUNTERS]);

		}


		$customPostType = (null !== $this->getCustomPostType()) ? $this->getCustomPostType() : PostTypeController::getPostTypeInstance(PostTypeController::POST_TYPE_MEMBERS_DIRECTORY);

		$customPostType->PostTitle = $arrSettingOptions[self::OPTION_DIRECTORY_TITLE];

		PostTypeController::publishPostType($customPostType);

		$this->setCustomPostType($customPostType);


		if($this->getOption(self::OPTION_IS_DEFAULT_MEMBERS_DIRECTORY)){
			$arrSettingOptions[self::OPTION_IS_DEFAULT_MEMBERS_DIRECTORY] = true;
		}

		$arrSettingOptions[self::OPTION_ASSIGNED_PAGE_ID] = (int)$this->getOption(self::OPTION_ASSIGNED_PAGE_ID);

		$this->registerSuccessMessage(__('Your changes were successfully saved!', 'ultra-community'));

		
		return $arrSettingOptions;

	}

	public  function renderModuleSettingsSectionHeader(array $arrSectionInfo)
	{
		parent::renderModuleSettingsSectionHeader($arrSectionInfo);
		
		MchWpUtils::addFilterHook(self::FILTER_FIELD_SETTINGS_TABLE_ROW_OUTPUT, function ($fieldTableRowOutput, $fieldKey){

			if($fieldKey === MembersDirectoryAdminModule::OPTION_USER_CARD_TAGLINE_FIELDS)
			{
				return BaseAdminModule::getFieldSettingsSectionDivider(esc_html__('User Card Settings', 'ultra-community')) . $fieldTableRowOutput ;
			}

			if($fieldKey === MembersDirectoryAdminModule::OPTION_DIRECTORY_CUSTOM_CSS)
			{
				return BaseAdminModule::getFieldSettingsSectionDivider(esc_html__('Members Directory Custom CSS', 'ultra-community')) . $fieldTableRowOutput ;
			}

			return 	$fieldTableRowOutput;
		}, 10, 2);


		$title = $this->getOption(self::OPTION_DIRECTORY_TITLE) . ' - ' . __('Settings', 'ultra-community');

		$title = esc_html($title);

		$customPostId   = $this->getCustomPostTypeId();
		$customPostType = $this->getCustomPostType()->PostType;
		$moduleKey    = $this->getSettingKey();


		$headerOutput = <<<SH
			<div class = "uc-settings-section-header uc-clear">
				<h3>$title</h3>
				<ul class = "uc-settings-module-actions">

					<li>
						<button id = "btn-uc-members-directory-action-add-new-members-directory-$moduleKey" data-modulekey = "$moduleKey" data-custompostid = "$customPostId" data-customposttype = "$customPostType" class = "uc-button uc-button-primary"><i class="fa fa-plus"></i> Add New</button>
					</li>

					<li>
						<button id = "btn-uc-members-directory-action-delete-members-directory-$moduleKey" data-modulekey = "$moduleKey" data-custompostid = "$customPostId" data-customposttype = "$customPostType" class = "uc-button uc-button-danger"><i class="fa fa-trash"></i> Delete</button>
					</li>

				</ul>
			</div>
SH;



		echo $headerOutput;

	}


	public function renderModuleSettingsField(array $arrSettingsField)
	{
		$fieldKey   = key( $arrSettingsField );
		$fieldValue = $this->getOption( $fieldKey );


		add_filter($this->getFieldAttributesFilterName($fieldKey) . '-output-html', function( $outputHtml, $arrAttr ) use ($fieldKey, $fieldValue){

			if(!in_array($fieldKey, array(MembersDirectoryAdminModule::OPTION_USER_CARD_TAGLINE_FIELDS,
										    MembersDirectoryAdminModule::OPTION_USER_CARD_BELOW_TAGLINE_FIELDS,
											MembersDirectoryAdminModule::OPTION_USER_ACTIVITY_STATS_COUNTERS
											)))
			{
				return $outputHtml;
			}

			$hiddenElementOutputHtml = '<input type="hidden" name = "' . 'ordered-' . $arrAttr['name'] . '" value="' . implode(',', (array)$fieldValue) . '" />';

			return $hiddenElementOutputHtml . $outputHtml;

		}, 10, 2);




		MchWpUtils::addFilterHook($this->getFieldAttributesFilterName($fieldKey), function( $arrFieldAttributes ) use ($fieldKey, $fieldValue){


			if($fieldKey === MembersDirectoryAdminModule::OPTION_DIRECTORY_TITLE){
				$arrFieldAttributes['class'][] = 'uc-members-directory-title';
				return $arrFieldAttributes;

			}

			if($fieldKey === MembersDirectoryAdminModule::OPTION_ASSIGNED_PAGE_ID){
				$fieldValue = (int)$fieldValue;
				$arrFieldAttributes['class'][] = 'uc-select2';
				$arrFieldAttributes['value']   = $fieldValue;

				$arrFieldAttributes['options'] = array(
						$fieldValue => null
				);

				if( $wpPost = WpPostRepository::findByPostId($fieldValue) ){
					$arrFieldAttributes['options'][$fieldValue] = esc_html('Page ( ID: ' . $fieldValue . ' ) - ( Rel URL: ' . wp_make_link_relative(untrailingslashit(MchWpUtils::getPageUrl($fieldValue))) . ' )');
				}

			}


			if($fieldKey === MembersDirectoryAdminModule::OPTION_LIST_USER_ROLES  ) //|| $fieldKey === MembersDirectoryAdminModule::OPTION_ALLOWED_USER_ROLES
			{
				$arrFieldAttributes['multiple'] = 'multiple';
				$arrFieldAttributes['class'][]  = 'uc-select2-multiple';
				$arrFieldAttributes['value']    = $fieldValue;
				$arrFieldAttributes['options']  = array();

				foreach ((array)PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_ROLE) as $customPostUserRole) {
					$adminModule = PostTypeController::getAssociatedAdminModuleInstance($customPostUserRole);
					if (!$adminModule instanceof UserRoleAdminModule)
						continue;

					$arrFieldAttributes['options'][$customPostUserRole->PostId] = $adminModule->getOption(UserRoleAdminModule::OPTION_ROLE_TITLE);
				}

			}

//			if($fieldKey === MembersDirectoryAdminModule::OPTION_PAGINATION_TYPE)
//			{
//				$arrFieldAttributes['class'][]  = 'uc-select2';
//				$arrFieldAttributes['value']    = $fieldValue;
//				$arrFieldAttributes['options']  = array(
//													MembersDirectoryAdminModule::PAGINATION_TYPE_INFINITE_SCROLL => __('Infinite Scroll', 'ultra-community'),
//													MembersDirectoryAdminModule::PAGINATION_TYPE_STANDARD_PAGINATION => __('Standard Pagination', 'ultra-community'),
//												);
//
//			}

			if($fieldKey === MembersDirectoryAdminModule::OPTION_USER_ACTIVITY_STATS_COUNTERS)
			{

				$arrFieldAttributes['value']    = (array)$fieldValue;
				$arrFieldAttributes['multiple'] = 'multiple';
				$arrFieldAttributes['class'][]  = 'uc-select2-multiple';
				$arrFieldAttributes['options']  = array();

//				$arrCounters = (array)apply_filters(UltraCommHooks::FILTER_USER_PROFILE_CARD_ACTIVITY_STATS_COUNTERS, array(
//						MembersDirectoryAdminModule::USER_STATS_COUNTER_POSTS    => __('Posts', 'ultra-community'),
//						MembersDirectoryAdminModule::USER_STATS_COUNTER_COMMENTS => __('Comments', 'ultra-community'),
//					)
//				);

				$arrCounters = array(
						MembersDirectoryAdminModule::USER_STATS_COUNTER_POSTS    => __('Posts', 'ultra-community'),
						MembersDirectoryAdminModule::USER_STATS_COUNTER_COMMENTS => __('Comments', 'ultra-community'),
				);

				foreach ($arrFieldAttributes['value'] as $index => $savedCounter)
				{
					if(!isset($arrCounters[$savedCounter])){
						unset($arrFieldAttributes['value'][$index]);
						continue;
					}

					$arrFieldAttributes['options'][$savedCounter] =  $arrCounters[$savedCounter];
				}

				foreach ($arrCounters as $counterKey => $counterName)
				{
					if(isset($arrFieldAttributes['options'][$counterKey]))
						continue;
					$arrFieldAttributes['options'][$counterKey] =  $counterName;
				}

			}


			if($fieldKey === MembersDirectoryAdminModule::OPTION_USER_CARD_TAGLINE_FIELDS || $fieldKey === MembersDirectoryAdminModule::OPTION_USER_CARD_BELOW_TAGLINE_FIELDS)
			{
				$arrFieldAttributes['multiple'] = 'multiple';
				$arrFieldAttributes['class'][]  = 'uc-select2-multiple';
				$arrFieldAttributes['value']    = $fieldValue;
				$arrFieldAttributes['options']  = array();


				$arrAvailableFields = MembersDirectoryAdminModule::getUserTagLineSelectOptions();
				$arrFieldAttributes['options'] = (array)$arrFieldAttributes['options'];
				foreach((array)$fieldValue as $savedFormField)
				{
					if(!isset($arrAvailableFields[$savedFormField]))
						continue;

					$arrFieldAttributes['options'][$savedFormField] = $arrAvailableFields[$savedFormField];
					unset($arrAvailableFields[$savedFormField]);
				}

				foreach($arrAvailableFields as $key => $availableField)
				{
					$arrFieldAttributes['options'][$key] = $availableField;
				}

			}

			return $arrFieldAttributes;

		});

		return parent::renderModuleSettingsField($arrSettingsField);

	}


	public static function getUserTagLineSelectOptions()
	{
		$arrAvailableFields = array();

		foreach (PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_PROFILE_FORM) as $profileFormCpt)
		{

			if( null === $profileFormAdminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($profileFormCpt) )
				continue;
			/**
			 * @var $formField BaseField
			 */
			foreach( (array)$profileFormAdminModuleInstance->getOption(BaseFormAdminModule::OPTION_FORM_FIELDS) as $formField)
			{

				switch (true)
				{
					case $formField instanceof DividerField  :
					case $formField instanceof UserNameField :
					case $formField instanceof UserNameOrEmailField :
					case $formField instanceof UserPasswordField :
					case $formField instanceof UserDisplayNameField:
					case $formField instanceof UserNickNameField :
					case $formField instanceof ProfileSectionField :
						$formField = null;

						break;
				}

				if(!$formField instanceof BaseField)
					continue;

				if($formField->getUserEntityMappedFieldName() || ($formField instanceof SocialNetworkUrlField) )
				{
					$fieldClassName = MchUtils::getClassShortNameFromNameSpace($formField);
					if(isset($arrAvailableFields[$fieldClassName]))
						continue;

					$arrAvailableFields[$fieldClassName] = $formField->getDisplayableFieldType();

					continue;
				}

				$arrAvailableFields[$formField->UniqueId] = $formField->Name;
			}
		}

		foreach (BaseFormAdminModule::getGroupedFormFields() as $arrDefinedFields)
		{
			foreach ($arrDefinedFields as $definedField)
			{
				if(
					//$definedField instanceof UserNameField ||
						$definedField instanceof UserNameOrEmailField ||
						$definedField instanceof UserPasswordField ||
						$definedField instanceof UserDisplayNameField || $definedField instanceof UserNickNameField
				){
					continue;
				}

				if(!$definedField->getUserEntityMappedFieldName())
					continue;

				$fieldClassName = MchUtils::getClassShortNameFromNameSpace($definedField);
				if(isset($arrAvailableFields[$fieldClassName]))
					continue;

				$arrAvailableFields[$fieldClassName] = $definedField->getDisplayableFieldType();
			}
		}

		unset($arrAvailableFields[MchUtils::getClassShortNameFromNameSpace(new SocialNetworkUrlField())]); // separately handled

		return $arrAvailableFields;
	}


}