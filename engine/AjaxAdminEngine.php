<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity;

use UltraCommunity\Admin\Pages\FormsAdminPage;
use UltraCommunity\Controllers\FrontPageController;
use UltraCommunity\Controllers\ModulesController;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Controllers\TasksController;
use UltraCommunity\Controllers\UserRoleController;
use UltraCommunity\MchLib\Modules\MchModulesController;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\Repository\WpPostRepository;
use UltraCommunity\Modules\Appearance\GroupProfile\GroupProfileAppearanceAdminModule;
use UltraCommunity\Modules\Appearance\UserProfile\UserProfileAppearanceAdminModule;
use UltraCommunity\Modules\CustomTabs\CustomTabsAdminModule;
use UltraCommunity\Modules\Forms\BaseForm\BaseFormAdminModule;
use UltraCommunity\Modules\Forms\FormFields\BaseField;
use UltraCommunity\Modules\Forms\FormFields\CheckBoxField;
use UltraCommunity\Modules\Forms\FormFields\DropDownField;
use UltraCommunity\Modules\Forms\FormFields\RadioButtonField;
use UltraCommunity\Modules\Forms\FormFields\SocialNetworkUrlField;
use UltraCommunity\Modules\Forms\RegisterForm\RegisterFormAdminModule;
use UltraCommunity\Modules\MembersDirectory\MembersDirectoryAdminModule;
use UltraCommunity\Modules\SocialConnect\SocialConnectAdminModule;
use UltraCommunity\Modules\UserRole\UserRoleAdminModule;
use UltraCommunity\Modules\UserSubscriptions\Controllers\RestrictionRulesController;
use UltraCommunity\Modules\UserSubscriptions\Controllers\UserSubscriptionController;
use UltraCommunity\Modules\UserSubscriptions\Entities\RestrictionRuleEntity;
use UltraCommunity\Modules\UserSubscriptions\Entities\SubscriptionLevelEntity;
use UltraCommunity\Modules\UserSubscriptions\SubModules\RestrictionRules\RestrictionRulesAdminModule;
use UltraCommunity\Modules\UserSubscriptions\SubModules\SubscriptionLevels\SubscriptionLevelsAdminModule;
use UltraCommunity\Tasks\UserRolesMapperTask;

final class AjaxAdminEngine
{
	CONST  FULLY_QUALIFIED_CLASS_NAME = __CLASS__;

	public static function getAllAvailableFormFieldsType()
	{
		if(empty($_POST['formCustomPostId'])){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received', 'ultracomm'));
		}

		if(MchUtils::isNullOrEmpty( $formAdminModuleInstance  =  PostTypeController::getAssociatedAdminModuleInstance( PostTypeController::getPostTypeInstanceByPostId($_POST['formCustomPostId']) ))) {
			MchWpUtils::sendAjaxErrorMessage( __( 'Invalid POST request received', 'ultracomm' ) );
		}

		MchWpUtils::sendAjaxSuccessMessage(BaseFormAdminModule::renderAllFormFieldsTypeForAdminModal($formAdminModuleInstance));

	}

	public static function saveHeaderProfileField()
	{
		if(empty($_POST['formCustomPostId']) || empty($_POST['formFieldType'])){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received', 'ultracomm'));
		}

		if(MchUtils::isNullOrEmpty( $formAdminModuleInstance  =  PostTypeController::getAssociatedAdminModuleInstance( PostTypeController::getMappedPostTypeInstance( WpPostRepository::findByPostId( (int)$_POST['formCustomPostId']  ))))) {
			MchWpUtils::sendAjaxErrorMessage( __( 'Invalid POST request received', 'ultracomm' ) );
		}

		$formFieldType = MchWpUtils::sanitizeText($_POST['formFieldType']);

		if(null === ($formFieldInstance = BaseFormAdminModule::getFieldInstanceByShortClassName($formFieldType))){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received - Invalid FormFieldType', 'ultracomm'));
		}


		$arrProfileHeaderFields = (array)$formAdminModuleInstance->getOption(BaseFormAdminModule::OPTION_PROFILE_HEADER_FIELDS);

		$fieldUniqueId = empty($_POST['fieldUniqueId']) ? $formFieldType : MchWpUtils::sanitizeText($_POST['fieldUniqueId']);

		if($formFieldType == MchUtils::getClassShortNameFromNameSpace(new SocialNetworkUrlField())){
			$fieldUniqueId = $formFieldType;
		}


		$fieldKey = $fieldUniqueId . '-' . ((int)$_POST['formCustomPostId']) . '-' . (count($arrProfileHeaderFields) + 1 );
		$arrProfileHeaderFields[$fieldKey] = $fieldUniqueId;

		$formAdminModuleInstance->saveOption(BaseFormAdminModule::OPTION_PROFILE_HEADER_FIELDS, $arrProfileHeaderFields);


		MchWpUtils::sendAjaxSuccessMessage(FormsAdminPage::getProfileHeaderFieldsListOutputContent($formAdminModuleInstance));

	}

	public static function deleteProfileHeaderField()
	{
		if(empty($_POST['formCustomPostId'])){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received', 'ultracomm'));
		}

		if(MchUtils::isNullOrEmpty( $formAdminModuleInstance  =  PostTypeController::getAssociatedAdminModuleInstance( PostTypeController::getMappedPostTypeInstance( WpPostRepository::findByPostId( (int)$_POST['formCustomPostId']  ))))) {
			MchWpUtils::sendAjaxErrorMessage( __( 'Invalid POST request received', 'ultracomm' ) );
		}

		if(empty($_POST['fieldUniqueId'])){ // in this case fieldUniqueId is the array key
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received! Field information is missing!', 'ultracomm'));
		}

		$arrProfileHeaderFields = (array)$formAdminModuleInstance->getOption(BaseFormAdminModule::OPTION_PROFILE_HEADER_FIELDS);
		unset($arrProfileHeaderFields[$_POST['fieldUniqueId']]);

		$formAdminModuleInstance->saveOption(BaseFormAdminModule::OPTION_PROFILE_HEADER_FIELDS, $arrProfileHeaderFields);

		MchWpUtils::sendAjaxSuccessMessage('The field was successfully deleted!');

		//MchWpUtils::sendAjaxSuccessMessage(FormsAdminPage::getProfileHeaderFieldsListOutputContent($formAdminModuleInstance));

	}

	public static function getProfileHeaderFields()
	{
		if(empty($_POST['formCustomPostId'])){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received', 'ultracomm'));
		}

		if(MchUtils::isNullOrEmpty( $formAdminModuleInstance  =  PostTypeController::getAssociatedAdminModuleInstance( PostTypeController::getMappedPostTypeInstance( WpPostRepository::findByPostId( (int)$_POST['formCustomPostId']  ))))) {
			MchWpUtils::sendAjaxErrorMessage( __( 'Invalid POST request received', 'ultracomm' ) );
		}

		MchWpUtils::sendAjaxSuccessMessage( $formAdminModuleInstance->renderAllProfileHeaderFieldsForAdminModal() );
	}

	public static function deleteFormField()
	{
		if(empty($_POST['fieldUniqueId']) || empty($_POST['formCustomPostId'])){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received', 'ultracomm'));
		}

		try
		{
			$customPostType  = PostTypeController::getMappedPostTypeInstance(WpPostRepository::findByPostId($_POST['formCustomPostId']));
			$formAdminModule = PostTypeController::getAssociatedAdminModuleInstance($customPostType);
			if(null === $formAdminModule){
				MchWpUtils::sendAjaxErrorMessage(__('Cannot find form custom post type', 'ultra-community'));
			}

			$arrFormFields = (array)$formAdminModule->getOption(BaseFormAdminModule::OPTION_FORM_FIELDS);

			for($i = 0, $size = count($arrFormFields); $i < $size; ++$i){

				if(!isset($arrFormFields[$i]->UniqueId) || $arrFormFields[$i]->UniqueId !== $_POST['fieldUniqueId'])
					continue;

				unset($arrFormFields[$i]);

				break;
			}

			$formAdminModule->saveOption(BaseFormAdminModule::OPTION_FORM_FIELDS, array_values($arrFormFields));
		}
		catch (UltraCommException $ue)
		{
			MchWpUtils::sendAjaxErrorMessage($ue->getMessage());
		}

		MchWpUtils::sendAjaxSuccessMessage('The field was successfully deleted!');
	}


	public static function saveFormFieldSettings()
	{
		if( empty($_POST['formFieldSettings']) ){ //empty($_POST['formFieldType']) ||
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received!', 'ultra-community'));
		}

		$formAdminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance(PostTypeController::getMappedPostTypeInstance(WpPostRepository::findByPostId($_POST['formCustomPostId'])));
		if(! $formAdminModuleInstance instanceof BaseFormAdminModule){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received!', 'ultra-community'));
		}

		$arrFieldProperties = wp_parse_args(stripslashes_deep($_POST['formFieldSettings']));

		if(empty($arrFieldProperties['FieldTypeClass'])){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received - FieldTypeClass is missing', 'ultra-community'));
		}

		$formFieldInstance = BaseFormAdminModule::getFieldInstanceByShortClassName($arrFieldProperties['FieldTypeClass']);

		if(empty($formFieldInstance)){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received - FieldTypeClass is invalid!', 'ultra-community'));
		}


		/**
		 * @var $formFieldInstance BaseField
		 */
		$formFieldInstance = MchUtils::populateObjectFromArray($formFieldInstance, $arrFieldProperties);

		$formFieldInstance->FrontEndVisibilityUserRoles = empty($arrFieldProperties['FrontEndVisibilityUserRoles']) ? array() :  $arrFieldProperties['FrontEndVisibilityUserRoles'];


		if(!empty($formFieldInstance->OptionsList) && !empty($arrFieldProperties['OptionsList']))
		{
			switch (true)
			{
				case $formFieldInstance instanceof CheckBoxField :
				case $formFieldInstance instanceof DropDownField :
				case $formFieldInstance instanceof RadioButtonField :
					$formFieldInstance->OptionsList = MchUtils::normalizeNewLine($arrFieldProperties['OptionsList']);
					$formFieldInstance->OptionsList = (array)array_filter(explode(PHP_EOL, $formFieldInstance->OptionsList));

					for ($i = 0, $size = count($formFieldInstance->OptionsList); $i < $size; ++$i) {

						$valueKey = MchUtils::getRandomHtmlElementId();
						while(isset($formFieldInstance->OptionsList[$valueKey])){
							$valueKey = MchUtils::getRandomHtmlElementId();
						}

						$formFieldInstance->OptionsList[$valueKey] = MchWpUtils::sanitizeText($formFieldInstance->OptionsList[$i]);
						unset($formFieldInstance->OptionsList[$i]);
					}

					break;

				default:
					break;
			}
		}



		try
		{
			$formAdminModuleInstance->saveFormFieldSettings($formFieldInstance);
		}
		catch(UltraCommException $ue)
		{
			MchWpUtils::sendAjaxErrorMessage($ue->getMessage(), 'ultra-community');
		}


		$formFieldsListOutput = FormsAdminPage::getFormFieldsListOutputContent($formAdminModuleInstance);

		$ajaxMessage = empty($formFieldInstance->UniqueId) ? __('The field was successfully added!', 'ultra-community') : __('The settings were successfully saved!', 'ultra-community');

		MchWpUtils::sendAjaxSuccessMessage( array('message' => $ajaxMessage, 'html' => $formFieldsListOutput) );


	}


	public static function saveProfileHeaderFieldsOrder()
	{
		if(empty($_POST['formFields']) || empty($_POST['formCustomPostId'])){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received', 'ultracomm'));
		}

		$arrReceivedFormFields = json_decode(stripslashes_deep($_POST['formFields']), true);
		if(empty($arrReceivedFormFields) || !is_array($arrReceivedFormFields)){
			exit;
		}

		$customPostId    = (int)$_POST['formCustomPostId'];
		$customPostType  = PostTypeController::getMappedPostTypeInstance(WpPostRepository::findByPostId($customPostId));

		$formAdminModule = PostTypeController::getAssociatedAdminModuleInstance($customPostType);

		if(null === $formAdminModule){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received. Custom Post Type cannot be found!', 'ultracomm'));
		}

		$arrReceivedFormFields = array_flip($arrReceivedFormFields);

		foreach((array)$formAdminModule->getOption(BaseFormAdminModule::OPTION_PROFILE_HEADER_FIELDS) as $fieldKey => $formFieldUniqueId){
			if( isset($arrReceivedFormFields[$fieldKey]) ){
				$arrReceivedFormFields[$fieldKey] = $formFieldUniqueId;
				continue;
			}
		}

		$arrReceivedFormFields = array_filter($arrReceivedFormFields);

		$formAdminModule->saveOption(BaseFormAdminModule::OPTION_PROFILE_HEADER_FIELDS, $arrReceivedFormFields);

		MchWpUtils::sendAjaxSuccessMessage(__('The order of fields was successfully saved!', 'utracomm'));
	}


	public static function saveFormFieldsOrder()
	{
		if(empty($_POST['formFields']) || empty($_POST['formCustomPostId'])){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received', 'ultracomm'));
		}

		$arrReceivedFormFields = json_decode(stripslashes_deep($_POST['formFields']), true);
		if(empty($arrReceivedFormFields) || !is_array($arrReceivedFormFields)){
			exit;
		}

		$arrFieldParts = explode('-', $arrReceivedFormFields[0]);
		$customPostId    = (int)$_POST['formCustomPostId'];
		$customPostType  = PostTypeController::getMappedPostTypeInstance(WpPostRepository::findByPostId($customPostId));


		$formAdminModule = PostTypeController::getAssociatedAdminModuleInstance($customPostType);

		if(null === $formAdminModule){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received. Custom Post Type cannot be found!', 'ultracomm'));
		}

		$arrReceivedFormFields = array_flip($arrReceivedFormFields);

		foreach((array)$formAdminModule->getOption(BaseFormAdminModule::OPTION_FORM_FIELDS) as $formField){
			if( isset($arrReceivedFormFields[$formField->UniqueId . '-' . $customPostId]) ){
				$arrReceivedFormFields[$formField->UniqueId . '-' . $customPostId] = $formField;
			}
		}

		$arrReceivedFormFields = array_values($arrReceivedFormFields);
		for($i = 0, $arrSize = count($arrReceivedFormFields); $i < $arrSize; ++$i){
			if(!$arrReceivedFormFields[$i] instanceof BaseField)
				unset($arrReceivedFormFields[$i]);
		}

		$formAdminModule->saveOption(BaseFormAdminModule::OPTION_FORM_FIELDS, $arrReceivedFormFields);

		MchWpUtils::sendAjaxSuccessMessage(__('The order of fields was successfully saved!', 'utracomm'));
	}


	public static function getFieldTypeSettings()
	{
		if(empty($_POST['formFieldType'])){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received!', 'ultra-community'));
		}

		if(empty($_POST['formCustomPostId'])){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received!', 'ultra-community'));
		}

		$customPostType  = PostTypeController::getMappedPostTypeInstance(WpPostRepository::findByPostId(absint($_POST['formCustomPostId'])));
		$formAdminModule = PostTypeController::getAssociatedAdminModuleInstance($customPostType);

		if(empty($formAdminModule)) {
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received - Invalid Custom Post Type', 'ultracomm'));
		}

		$formFieldType = MchWpUtils::sanitizeText($_POST['formFieldType']);

		if(null === ($formFieldInstance = BaseFormAdminModule::getFieldInstanceByShortClassName($formFieldType))){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received - Invalid FormFieldType', 'ultracomm'));
		}

		if(!empty($_POST['registerFormFieldUniqueId']) && !empty($_POST['registerFormCustomPostId'])){

			$_POST['registerFormCustomPostId']  = MchWpUtils::sanitizeText($_POST['registerFormCustomPostId']);
			$_POST['registerFormFieldUniqueId'] = MchWpUtils::sanitizeText($_POST['registerFormFieldUniqueId']);

			if (!MchUtils::isNullOrEmpty( $registerFormAdminModuleInstance =  PostTypeController::getAssociatedAdminModuleInstance(PostTypeController::getPostTypeInstanceByPostId($_POST['registerFormCustomPostId'])))){
				if($registerFieldInstance = $registerFormAdminModuleInstance->getFormFieldByUniqueId($_POST['registerFormFieldUniqueId'])){
					$formFieldInstance = $registerFieldInstance;
					$formFieldInstance->RegisterFormCustomPostId  = $_POST['registerFormCustomPostId'];
					$formFieldInstance->RegisterFormFieldUniqueId = $_POST['registerFormFieldUniqueId'];
				}
			}
		}



		MchWpUtils::sendAjaxSuccessMessage($formAdminModule->renderFieldSettingsForAdminModal($formFieldInstance));

	}

	public static function getFormFieldSettings()
	{

		if(empty($_POST['formFieldUniqueId'])){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received!', 'ultra-community'));
		}

		if(empty($_POST['formCustomPostId'])){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received!', 'ultra-community'));
		}

		$customPostType  = PostTypeController::getMappedPostTypeInstance(WpPostRepository::findByPostId(absint($_POST['formCustomPostId'])));
		$formAdminModule = PostTypeController::getAssociatedAdminModuleInstance($customPostType);

		if(empty($formAdminModule)) {
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received - Invalid Custom Post Type', 'ultra-community'));
		}

		if(null === $formFieldInstance = $formAdminModule->getFormFieldByUniqueId($_POST['formFieldUniqueId'])){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received - Invalid Field', 'ultra-community'));
		}



		MchWpUtils::sendAjaxSuccessMessage($formAdminModule->renderFieldSettingsForAdminModal($formFieldInstance));


	}


	public static function deleteMembersDirectory()
	{

		if(empty($_POST['membersDirectoryCustomPostId']) || !MchValidator::isInteger($_POST['membersDirectoryCustomPostId'])){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received!', 'ultra-community'));
		}

		$adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance(PostTypeController::getPostTypeInstanceByPostId($_POST['membersDirectoryCustomPostId']));
		if(null === $adminModuleInstance){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received!', 'ultra-community'));
		}

		$adminModuleInstance->deleteAllSettingOptions();
		WpPostRepository::delete($_POST['membersDirectoryCustomPostId']);

		MchWpUtils::sendAjaxSuccessMessage(__('Members Directory successfully deleted!', 'ultra-community'));
	}

	public static function addNewMembersDirectory()
	{

		$customPostType = PostTypeController::getPostTypeInstance(PostTypeController::POST_TYPE_MEMBERS_DIRECTORY);

		$customPostType->PostId = PostTypeController::publishPostType($customPostType);
		if(empty($customPostType->PostId)){
			MchWpUtils::sendAjaxErrorMessage(__('An error occured while trying to create members directory', 'ultra-community'));
		}

		$assignedPageId = FrontPageController::publishPage(FrontPageController::PAGE_MEMBERS_DIRECTORY);
		if(empty($assignedPageId)){
			MchWpUtils::sendAjaxErrorMessage(__('An error occured while trying to create members directory', 'ultra-community'));
		}

		$adminModuleInstance = MembersDirectoryAdminModule::getInstance(true);
		$adminModuleInstance->setCustomPostType($customPostType);
		$adminModuleInstance->saveDefaultOptions();

		$adminModuleInstance->saveOption(MembersDirectoryAdminModule::OPTION_ASSIGNED_PAGE_ID, (int)$assignedPageId);

		MchWpUtils::sendAjaxSuccessMessage(__('Members Directory successfully created!', 'ultra-community'));

	}

	public static function addNewForm()
	{
		if(empty($_POST['formPostType'])){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received', 'ultra-community'));
		}

		if(null === ($defaultUserRolePostType = UserRoleController::getDefaultUserRolePostType())){
			MchWpUtils::sendAjaxErrorMessage(__('Unable to retrieve default user role!', 'ultra-community'));
		}


		try
		{
			$formTitle = null;

			$formCustomPostTypeKey = MchWpUtils::sanitizeText($_POST['formPostType']);

			if( !MchUtils::isNullOrEmpty($arrPublishedPosts = PostTypeController::getPublishedPosts($formCustomPostTypeKey, true)) ){
				foreach ($arrPublishedPosts as $publishedPost){
					$adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($publishedPost);
					if(! $adminModuleInstance instanceof BaseFormAdminModule)
						continue;

					if(MchUtils::isNullOrEmpty($adminModuleInstance->getDefaultOptionValue(BaseFormAdminModule::OPTION_FORM_TITLE)))
						continue;

					$formTitle = $adminModuleInstance->getDefaultOptionValue(BaseFormAdminModule::OPTION_FORM_TITLE);

					break;
				}
			}

			if(MchUtils::isNullOrEmpty($formCustomPostType = PostTypeController::getPostTypeInstance($formCustomPostTypeKey))){
				MchWpUtils::sendAjaxErrorMessage(__('Invalid CustomPostType received', 'ultra-community'));
			}

			$arrUnAssignedUserRoles = UltraCommHelper::getUnAssignedUserRolesForNewForm($formCustomPostType);
			if(in_array($formCustomPostTypeKey, array(PostTypeController::POST_TYPE_USER_PROFILE_FORM))){
				if(MchUtils::isNullOrEmpty($arrUnAssignedUserRoles)){
					MchWpUtils::sendAjaxErrorMessage(__('In order to create a new form please add a new User Role!', 'ultra-community'));
				}
			}

			$formCustomPostType->PostTitle = (null === $formTitle) ? __('New Form', 'ultra-community') : $formTitle;
			$formCustomPostType->PostId    = PostTypeController::publishPostType($formCustomPostType);

			if(empty($formCustomPostType->PostId)){
				MchWpUtils::sendAjaxErrorMessage(__('An error occurred while creating the post!', 'ultra-community'));
			}

			if(null === ($adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($formCustomPostType))){
				throw new UltraCommException(__('An error occurred while creating the post!', 'ultra-community'));
			}

			$adminModuleInstance->saveDefaultOptions();

			if($formCustomPostTypeKey === PostTypeController::POST_TYPE_USER_PROFILE_FORM)
			{
				if( ! MchUtils::isNullOrEmpty($userRolePostType = reset($arrUnAssignedUserRoles)) ){
					$adminModuleInstance->saveOption(BaseFormAdminModule::OPTION_USER_ROLE_CUSTOM_POST_ID, $userRolePostType->PostId);
				}
			}

//			if(in_array($formCustomPostTypeKey, array(PostTypeController::POST_TYPE_USER_PROFILE_FORM)))
//			{
//
//				if( ! MchUtils::isNullOrEmpty($userRolePostType = reset($arrUnAssignedUserRoles)) ){
//					$adminModuleInstance->saveOption(BaseFormAdminModule::OPTION_USER_ROLE_CUSTOM_POST_ID, $userRolePostType->PostId);
//				}
//			}

			if($formCustomPostTypeKey === PostTypeController::POST_TYPE_REGISTER_FORM)
			{

				$adminModuleInstance->saveOption(BaseFormAdminModule::OPTION_USER_ROLE_CUSTOM_POST_ID, $defaultUserRolePostType->PostId);
				$pageId = FrontPageController::publishPage(FrontPageController::PAGE_REGISTRATION, $adminModuleInstance);

				if(  ($wpPost = WpPostRepository::findByPostId($pageId)) ) {
					$adminModuleInstance->saveOption( RegisterFormAdminModule::OPTION_ASSIGNED_PAGE_ID, $pageId );
					$adminModuleInstance->saveOption( RegisterFormAdminModule::OPTION_ASSIGNED_PAGE_SLUG, $wpPost->post_name );
				}

			}
		}
		catch(UltraCommException $ue)
		{
			MchWpUtils::sendAjaxErrorMessage($ue->getMessage());
		}

		MchWpUtils::sendAjaxSuccessMessage(__('Your new form was successfully created!', 'ultra-community'));

	}

	public static function deleteForm()
	{
		if(empty($_POST['formCustomPostId'])){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received', 'ultra-community'));
		}

		try
		{
			$adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance(PostTypeController::getPostTypeInstanceByPostId($_POST['formCustomPostId']));

			if(null === $adminModuleInstance){
				MchWpUtils::sendAjaxErrorMessage(__('Invalid CustomPostType received', 'ultra-community'));
			}

			$adminModuleInstance->deleteAllSettingOptions();

			if(!WpPostRepository::delete($adminModuleInstance->getCustomPostTypeId())){
				MchWpUtils::sendAjaxErrorMessage(__('An error occurred while deleting the post!', 'ultra-community'));
			}

		}
		catch(UltraCommException $ue)
		{
			MchWpUtils::sendAjaxErrorMessage($ue->getMessage());
		}

		MchWpUtils::sendAjaxSuccessMessage(__('The form was successfully deleted!', 'ultra-community'));

	}

	public static function addNewUserRole()
	{
		try
		{

			$userRoleTitle = __('New Community Role', 'ultra-community');
			if( !MchUtils::isNullOrEmpty($arrPublishedPosts = PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_ROLE, true)) ){
				foreach ($arrPublishedPosts as $publishedPost){
					$adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($publishedPost);
					if(! $adminModuleInstance instanceof BaseFormAdminModule)
						continue;

					if(MchUtils::isNullOrEmpty($adminModuleInstance->getDefaultOptionValue(UserRoleAdminModule::OPTION_ROLE_TITLE)))
						continue;

					$userRoleTitle = $adminModuleInstance->getDefaultOptionValue(UserRoleAdminModule::OPTION_ROLE_TITLE);

					break;
				}
			}

			$customPostType = PostTypeController::getPostTypeInstance(PostTypeController::POST_TYPE_USER_ROLE);
			$customPostType->PostTitle = $userRoleTitle;

			$customPostType->PostId = PostTypeController::publishPostType($customPostType);

			if(empty($customPostType->PostId)){
				MchWpUtils::sendAjaxErrorMessage(__('An error occurred while creating a new User Role!', 'ultra-community'));
			}

			if(null === ($adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($customPostType))){
				MchWpUtils::sendAjaxErrorMessage(__('An error occurred while creating a new User Role!', 'ultra-community'));
			}

			if(null === ($userRoleKey = UserRoleController::generateUserRoleKeyFromDescription($customPostType->PostId))){
				MchWpUtils::sendAjaxErrorMessage(__('An error occurred while creating a new User Role!', 'ultra-community'));
			}

			$adminModuleInstance->saveOption(UserRoleAdminModule::OPTION_ROLE_SLUG, $userRoleKey);
			$adminModuleInstance->saveOption(UserRoleAdminModule::OPTION_ROLE_TITLE, $customPostType->PostTitle);


			foreach (UserRoleController::getDefaultUltraCommCapabilitiesByRole(UserRoleController::ROLE_MEMBER_SLUG) as $memberCapability => $enabled)
			{
				(!$enabled) ?: $adminModuleInstance->saveOption($memberCapability, true);
			}


		}
		catch (UltraCommException $ue)
		{
			MchWpUtils::sendAjaxErrorMessage($ue->getMessage());
		}

		MchWpUtils::sendAjaxSuccessMessage(__('A new user role was successfully created!', 'ultra-community'));

	}

	public static function deleteUserRole()
	{
		if(empty($_POST['userRoleCustomPostId'])){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received', 'ultra-community'));
		}

		try
		{
			$adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance(PostTypeController::getPostTypeInstanceByPostId($_POST['userRoleCustomPostId']));

			if(null === $adminModuleInstance){
				MchWpUtils::sendAjaxErrorMessage(__('Invalid CustomPostType received', 'ultra-community'));
			}

			wp_roles()->remove_role($adminModuleInstance->getOption(UserRoleAdminModule::OPTION_ROLE_SLUG));

			$adminModuleInstance->deleteAllSettingOptions();

			if(!WpPostRepository::delete($adminModuleInstance->getCustomPostTypeId())){
				MchWpUtils::sendAjaxErrorMessage(__('An error occurred while deleting the post!', 'ultra-community'));
			}

		}
		catch(UltraCommException $ue)
		{
			MchWpUtils::sendAjaxErrorMessage($ue->getMessage());
		}

		MchWpUtils::sendAjaxSuccessMessage(__('User role successfully deleted!', 'ultra-community'));

	}


	public static function addNewCustomTab()
	{

		if(!ModulesController::isModuleRegistered(ModulesController::MODULE_CUSTOM_TABS)){
			MchWpUtils::sendAjaxErrorMessage(__('Custom Tabs extension is missig', 'ultra-community'));
		}

		if(empty($_POST['targetId']) || !in_array($_POST['targetId'], array(CustomTabsAdminModule::CUSTOM_TAB_TARGET_USER_PROFILE_SECTION, CustomTabsAdminModule::CUSTOM_TAB_TARGET_GROUP_PROFILE_SECTION))){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid request received', 'ultra-community'));
		}

		$_POST['targetId'] = (int)$_POST['targetId'];

		try
		{

			$customTabPostType            = PostTypeController::getPostTypeInstance( PostTypeController::POST_TYPE_CUSTOM_TAB );
			$customTabPostType->PostId    = null;
			$customTabPostType->PostTitle = 'Tab Title';

			$customTabPostType->PostId = PostTypeController::publishPostType($customTabPostType);
			if(empty($customTabPostType->PostId)){
				MchWpUtils::sendAjaxErrorMessage(__('An error occurred while creating this tab!', 'ultra-community'));
			}

			$customTabAdminModule = CustomTabsAdminModule::getInstance(true);

			$customTabAdminModule->setCustomPostType($customTabPostType);
			$customTabAdminModule->saveOption(CustomTabsAdminModule::OPTION_CUSTOM_TAB_TITLE, $customTabPostType->PostTitle);
			$customTabAdminModule->saveOption(CustomTabsAdminModule::OPTION_CUSTOM_TAB_TARGET, $_POST['targetId']);


		}
		catch (UltraCommException $ue)
		{
			MchWpUtils::sendAjaxErrorMessage($ue->getMessage());
		}

		MchWpUtils::sendAjaxSuccessMessage(__('A new custom tab was successfully created!', 'ultra-community'));

	}


	public static function deleteCustomTab()
	{
		if(empty($_POST['customTabPostId'])){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid request received', 'ultra-community'));
		}

		try
		{

			$customTabPostType = PostTypeController::getPostTypeInstanceByPostId($_POST['customTabPostId']);
			if(empty($customTabPostType)){
				MchWpUtils::sendAjaxErrorMessage(__('Invalid CustomPostType received', 'ultra-community'));
			}

			$adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($customTabPostType);

			if($adminModuleInstance->getOption(CustomTabsAdminModule::OPTION_CUSTOM_TAB_TARGET) == CustomTabsAdminModule::CUSTOM_TAB_TARGET_USER_PROFILE_SECTION)
			{
				$arrActiveProfileSections = UserProfileAppearanceAdminModule::getDefinedUserProfileSections();
				unset($arrActiveProfileSections[$customTabPostType->PostSlug]);
				$appearanceAdminModuleInstance = ModulesController::getAdminModuleInstance(ModulesController::MODULE_APPEARANCE_USER_PROFILE, true);
				$appearanceAdminModuleInstance->saveOption(UserProfileAppearanceAdminModule::OPTION_USER_PROFILE_SECTIONS, array_keys($arrActiveProfileSections));

			}
			else
			{
				$arrActiveProfileSections = GroupProfileAppearanceAdminModule::getDefinedMenuSections();
				unset($arrActiveProfileSections[$customTabPostType->PostSlug]);

				$appearanceAdminModuleInstance = ModulesController::getAdminModuleInstance(ModulesController::MODULE_APPEARANCE_GROUP_PROFILE, true);
				$appearanceAdminModuleInstance->saveOption(GroupProfileAppearanceAdminModule::OPTION_GROUP_PROFILE_SECTIONS, array_keys($arrActiveProfileSections));

			}


			$adminModuleInstance->deleteAllSettingOptions();


			if(!WpPostRepository::delete($adminModuleInstance->getCustomPostTypeId())){
				MchWpUtils::sendAjaxErrorMessage(__('An error occurred while deleting the post!', 'ultra-community'));
			}

		}
		catch(UltraCommException $ue)
		{
			MchWpUtils::sendAjaxErrorMessage($ue->getMessage());
		}

		MchWpUtils::sendAjaxSuccessMessage(__('This Custom Tab was successfully deleted!', 'ultra-community'));

	}



	public static function addNewSocialConnectConfig()
	{

		if(!ModulesController::isModuleRegistered(ModulesController::MODULE_SOCIAL_CONNECT)){
			MchWpUtils::sendAjaxErrorMessage(__('Social Connect extension is missig', 'ultra-community'));
		}

		try
		{

			$customPostType = PostTypeController::getPostTypeInstance(PostTypeController::POST_TYPE_SOCIAL_CONNECT);
			$customPostType->PostTitle = __('New Configuration', 'ultra-community');;

			$customPostType->PostId = PostTypeController::publishPostType($customPostType);
			if(empty($customPostType->PostId)){
				MchWpUtils::sendAjaxErrorMessage(__('An error occurred while creating the post!', 'ultra-community'));
			}

			if(null === ($adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($customPostType))){
				MchWpUtils::sendAjaxErrorMessage(__('An error occurred while creating the post!', 'ultra-community'));
			}

			$adminModuleInstance->saveDefaultOptions();
			$adminModuleInstance->saveOption(SocialConnectAdminModule::OPTION_CONFIGURATION_TITLE, $customPostType->PostTitle);

		}
		catch (UltraCommException $ue)
		{
			MchWpUtils::sendAjaxErrorMessage($ue->getMessage());
		}

		MchWpUtils::sendAjaxSuccessMessage(__('A new social connect configuration was successfully created!', 'ultra-community'));

	}


	public static function deleteSocialConnectConfig()
	{
		if(empty($_POST['socialConnectCustomPostId'])){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received', 'ultra-community'));
		}

		try
		{
			$adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance(PostTypeController::getPostTypeInstanceByPostId($_POST['socialConnectCustomPostId']));

			if(null === $adminModuleInstance){
				MchWpUtils::sendAjaxErrorMessage(__('Invalid CustomPostType received', 'ultra-community'));
			}

			$adminModuleInstance->deleteAllSettingOptions();


			if(!WpPostRepository::delete($adminModuleInstance->getCustomPostTypeId())){
				MchWpUtils::sendAjaxErrorMessage(__('An error occurred while deleting the post!', 'ultra-community'));
			}

		}
		catch(UltraCommException $ue)
		{
			MchWpUtils::sendAjaxErrorMessage($ue->getMessage());
		}

		MchWpUtils::sendAjaxSuccessMessage(__('Social Connect configuration was successfully deleted!', 'ultra-community'));

	}



	public static function loadSubscriptionLevelForm()
	{

		if(!MchModulesController::isModuleRegistered(ModulesController::MODULE_USER_SUBSCRIPTIONS))
			MchWpUtils::sendAjaxErrorMessage(__('Invalid Request received', 'ultra-community'));


		$subscriptionLevelId = ( !empty($_POST['subscriptionLevelId']) && MchValidator::isInteger($_POST['subscriptionLevelId']) ) ? (int)$_POST['subscriptionLevelId'] : 0;

		$formOutput = SubscriptionLevelsAdminModule::getSubscriptionLevelForm($subscriptionLevelId);

		MchWpUtils::sendAjaxSuccessMessage($formOutput);

	}

	public static function saveUserSubscriptionLevel()
	{
		if(!MchModulesController::isModuleRegistered(ModulesController::MODULE_USER_SUBSCRIPTIONS))
			MchWpUtils::sendAjaxErrorMessage(__('Invalid Request received', 'ultra-community'));

		if( empty($_POST['formFields']) ){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid POST request received!', 'ultra-community'));
		}


		$arrFormFields = wp_parse_args(stripslashes_deep($_POST['formFields']));

		$subscriptionLevelEntity =  MchUtils::populateObjectFromArray(new SubscriptionLevelEntity(), $arrFormFields);

		try
		{
			UserSubscriptionController::saveSubscriptionLevel($subscriptionLevelEntity);
		}
		catch (UltraCommException $ue)
		{
			MchWpUtils::sendAjaxErrorMessage($ue->getMessage());
		}

		MchWpUtils::sendAjaxSuccessMessage( $subscriptionLevelEntity->Name . ' ' . __('was successfully saved!', 'ultra-community'));

	}

	public static function saveSubscriptionAccessLevel()
	{
		if(!MchModulesController::isModuleRegistered(ModulesController::MODULE_USER_SUBSCRIPTIONS))
			MchWpUtils::sendAjaxErrorMessage(__('Invalid Request received', 'ultra-community'));

		$_POST['sortedLevels'] = json_decode(stripslashes_deep($_POST['sortedLevels']), true);

		if(empty($_POST['sortedLevels'])){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid Request received', 'ultra-community'));
		}

		$_POST['sortedLevels'] = array_map(function ($subscriptionId){ return (int)str_replace('subscriptionLevelId-', '', $subscriptionId);}, $_POST['sortedLevels']);

		foreach ($_POST['sortedLevels'] as $index => $subscriptionId)
		{
			if(null === ($subscriptionLevelEntity = UserSubscriptionController::getSubscriptionLevelByKey($subscriptionId)))
				continue;

			$subscriptionLevelEntity->AccessLevel = $index + 1;

			try
			{
				UserSubscriptionController::saveSubscriptionLevel($subscriptionLevelEntity);
			}
			catch (UltraCommException $ue)
			{
				MchWpUtils::sendAjaxErrorMessage($ue->getMessage());
			}

		}

		MchWpUtils::sendAjaxSuccessMessage(__('The access level of all subscriptions was successfully changed !', 'ultra-community'));


	}

	public static function deleteUserSubscriptionLevel()
	{
		if(!MchModulesController::isModuleRegistered(ModulesController::MODULE_USER_SUBSCRIPTIONS))
			MchWpUtils::sendAjaxErrorMessage(__('Invalid Request received', 'ultra-community'));

		try
		{
			$subscriptionId = !empty($_POST['subscriptionLevelId']) && MchValidator::isInteger($_POST['subscriptionLevelId']) ? (int)$_POST['subscriptionLevelId'] : 0;
			UserSubscriptionController::deleteSubscriptionLevel($subscriptionId);
		}
		catch (UltraCommException $ue)
		{
			MchWpUtils::sendAjaxErrorMessage($ue->getMessage());
		}

		MchWpUtils::sendAjaxSuccessMessage(__('Subscription Level successfully deleted !', 'ultra-community'));
	}


	public static function loadRestrictionRuleForm()
	{
		if(!MchModulesController::isModuleRegistered(ModulesController::MODULE_USER_SUBSCRIPTIONS))
			MchWpUtils::sendAjaxErrorMessage(__('Invalid Request received', 'ultra-community'));

		$restrictionRuleId = isset($_POST['restrictionRuleId']) && MchValidator::isInteger($_POST['restrictionRuleId']) ? (int)$_POST['restrictionRuleId'] : null;

		$formOutput = RestrictionRulesAdminModule::getRestrictionRuleForm($restrictionRuleId);

		MchWpUtils::sendAjaxSuccessMessage($formOutput);

	}

	public static function deleteRestrictionRule()
	{
		if(!MchModulesController::isModuleRegistered(ModulesController::MODULE_USER_SUBSCRIPTIONS))
			MchWpUtils::sendAjaxErrorMessage(__('Invalid Request received', 'ultra-community'));

		$restrictionRuleId = isset($_POST['restrictionRuleId']) && MchValidator::isInteger($_POST['restrictionRuleId']) ? (int)$_POST['restrictionRuleId'] : null;

		RestrictionRulesController::deleteRestrictionRule($restrictionRuleId);

		MchWpUtils::sendAjaxSuccessMessage(__('Restriction rule successfully deleted !', 'ultra-community'));

	}

	public static function searchPostTypeToRestrict()
	{
		$arrQueryParams = array(
			'post_status' => 'publish', 'has_password' => false, 'orderby' => 'date', 'order' => 'DESC',
			'post_type'   => empty($_POST['postType']) ? null : MchWpUtils::sanitizeText($_POST['postType']),
			's'          => empty($_POST['search'])   ? null : MchWpUtils::sanitizeText($_POST['search'])
		);

		$wpQuery = new \WP_Query(array_filter($arrQueryParams));
		if(!$wpQuery->have_posts())
			return json_encode(array());

		$arrPosts = array();
		while($wpQuery->have_posts())
		{
			$wpQuery->the_post();
			$arrPosts[] = array($wpQuery->post->ID, $wpQuery->post->post_title);
		}

		echo json_encode($arrPosts);
		exit;

	}

	public static function searchTaxonomyToRestrict()
	{
		$arrQueryParams = array(

			'taxonomy'   => empty($_POST['taxonomy']) ? null : MchWpUtils::sanitizeText($_POST['taxonomy']),
			'hide_empty' => false,
			'fields'     => 'all',
			'name__like' => empty($_POST['search'])   ? null : MchWpUtils::sanitizeText($_POST['search'])

		);

		$arrTaxonomies = array();
		$arrTerms = (array)get_terms( $arrQueryParams );

		foreach ($arrTerms as $wpTermObject){
			$arrTaxonomies[] = array($wpTermObject->term_taxonomy_id, $wpTermObject->name);
		}

		echo json_encode($arrTaxonomies);
		exit;
	}


	public static function saveRestrictionRule()
	{
		if(!MchModulesController::isModuleRegistered(ModulesController::MODULE_USER_SUBSCRIPTIONS))
			MchWpUtils::sendAjaxErrorMessage(__('Invalid Request received', 'ultra-community'));

		if(empty($_POST['formFields']))
			MchWpUtils::sendAjaxErrorMessage(__('Invalid Request received', 'ultra-community'));


		try
		{
			$arrFormFields = wp_parse_args(stripslashes_deep($_POST['formFields']));
			RestrictionRulesController::saveRestrictionRule($arrFormFields);
		}
		catch (UltraCommException $ue)
		{
			MchWpUtils::sendAjaxErrorMessage($ue->getMessage());
		}

		MchWpUtils::sendAjaxSuccessMessage(__('Content restriction rule was successfully saved!', 'ultra-community'));

	}


	private function __construct(){
	}
	private function __clone(){
	}
	private function __wakeup(){
	}
}