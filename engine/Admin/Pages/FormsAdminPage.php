<?php

namespace UltraCommunity\Admin\Pages;


use UltraCommunity\Controllers\ModulesController;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Controllers\ShortCodesController;
use UltraCommunity\MchLib\Modules\MchGroupedModules;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Modules\Forms\BaseForm\BaseFormAdminModule;
use UltraCommunity\Modules\Forms\ForgotPasswordForm\ForgotPasswordFormAdminModule;
use UltraCommunity\Modules\Forms\FormFields\BaseField;
use UltraCommunity\Modules\Forms\FormFields\SocialNetworkUrlField;
use UltraCommunity\Modules\Forms\LoginForm\LoginFormAdminModule;
use UltraCommunity\Modules\Forms\RegisterForm\RegisterFormAdminModule;
use UltraCommunity\Modules\Forms\UserProfileForm\UserProfileFormAdminModule;
use UltraCommunity\UltraCommUtils;

class FormsAdminPage extends BaseAdminPage
{
	CONST REQUEST_FORMS_TYPE = 'uc-forms';
	private static $arrFormsType = array();

	public static function getActiveFormType()
	{

		if(empty(self::$arrFormsType))
			self::getAllFormsType();

		if(!empty($_GET[self::REQUEST_FORMS_TYPE]) && isset(self::$arrFormsType[$_GET[self::REQUEST_FORMS_TYPE]])){
			return $_GET[self::REQUEST_FORMS_TYPE];
		}

		if(!empty($_POST['_wp_http_referer']))
		{
			$postedArguments = wp_parse_args($_POST['_wp_http_referer']);
			if(!empty($postedArguments[self::REQUEST_FORMS_TYPE]) && isset(self::$arrFormsType[$postedArguments[self::REQUEST_FORMS_TYPE]])){
				return $postedArguments[self::REQUEST_FORMS_TYPE];
			}

		}

		reset(self::$arrFormsType);
		return key(self::$arrFormsType);

	}


	public static function getAllFormsType()
	{
		if(!empty(self::$arrFormsType))
			return self::$arrFormsType;

		self::$arrFormsType = array(
			'user-profile'    => esc_html__('User Profile Forms', 'ultra-community'),
			'registration'    => esc_html__('Registration Forms', 'ultra-community'),
			'login'           => esc_html__('User Login Forms', 'ultra-community'),
			'forgot-password' => esc_html__('Forgot Password Forms', 'ultra-community'),
		);

		return self::$arrFormsType;
	}

	public function __construct( $pageMenuTitle )
	{
		parent::__construct( $pageMenuTitle );

		$this->registerFormsModules();

		MchWpUtils::addFilterHook(parent::FILTER_MODULE_SUBTAB_URL, function ($subTabUrl, $adminModuleInstance, $pageInstance){

			if(!is_a($pageInstance, __CLASS__))
				return $subTabUrl;

			return  add_query_arg( FormsAdminPage::REQUEST_FORMS_TYPE, FormsAdminPage::getActiveFormType(), $subTabUrl );

		}, 10, 3);


		MchWpUtils::addActionHook('mch-admin-page-top', function ($pageInstance){

			if(!is_a($pageInstance, __CLASS__))
				return;


$subMenuList = '';
foreach (FormsAdminPage::getAllFormsType() as $formType => $formDescription)
{
	$subMenuClass = ($formType === FormsAdminPage::getActiveFormType()) ? 'current' : '';
	$pageUrl = esc_url(add_query_arg( FormsAdminPage::REQUEST_FORMS_TYPE, $formType, $pageInstance->getAdminUrl() ));
	$subMenuList .= "<li><a href=\"$pageUrl\" class=\"$subMenuClass\">$formDescription</a></li>";
}


$formsSubMenu = <<<FORMSSUBMENU
<div class="wp-filter">
    <ul class="filter-links">
        $subMenuList
    </ul>
</div>
FORMSSUBMENU;

		echo $formsSubMenu;
		});



		MchWpUtils::addFilterHook(parent::FILTER_MODULE_SUBTAB_NAME, function ($moduleDisplayName, $adminModuleInstance, $pageInstance){

			if(!is_a($pageInstance, __CLASS__) || !($adminModuleInstance instanceof BaseFormAdminModule))
				return $moduleDisplayName;

			$title = $adminModuleInstance->getOption($adminModuleInstance::OPTION_FORM_TITLE);
			return empty($title) ? $moduleDisplayName : $title;

		}, 10, 3);



		MchWpUtils::addActionHook(parent::ACTION_AFTER_SETTINGS_FORM_SUBMIT_BUTTON, function($pageInstance, $moduleSettingKey){
			/**
			 * @var $pageInstance FormsAdminPage
			 */
			if( ! is_a($pageInstance, __CLASS__))
				return;

			foreach($pageInstance->getActiveAdminModules() as $activeAdminModuleInstance)
			{
				if($moduleSettingKey !== $activeAdminModuleInstance->getSettingKey())
					continue;

				$customPostTypeId = $activeAdminModuleInstance->getCustomPostTypeId();

				$htmlCode = '';
				$htmlCode .= '<div class="uc-settings-section-header uc-clear"><h3>' . __('Form Fields', 'ultracomm') . '</h3></div>';

				$htmlCode .= "<div class=\"uch uc-form-fields-wrapper\">";

				$htmlCode .= '<div class="uc-g">';

				$htmlCode .= '<div class="uc-form-fields-list-holder uc-u-1">';

				$htmlCode .= FormsAdminPage::getFormFieldsListOutputContent($activeAdminModuleInstance);

				$htmlCode .= '<div class =  "uc-form-fields-list-message uc-form-add-new-field uc-hidden"><h4 class="uc-notice uc-notice-success"></h4></div>';

				$addNewFieldButtonText = __('Add New Form Field', 'ultra-community');

				if($customPostTypeId)
				{
					$htmlCode .= '<div class="uc-form-add-new-field">';
					$htmlCode .= "<button data-postid = \"$customPostTypeId\" class=\"uc-button uc-button-primary btn-uc-form-add-new-field \" >";
					$htmlCode .= "<i class=\"fa fa-plus\"></i> $addNewFieldButtonText";
					$htmlCode .= "</button>";
					$htmlCode .= '</div>';

				}


				$htmlCode .= '</div>';
				$htmlCode .= '</div>';
				$htmlCode .= '</div>';

				echo $htmlCode;


//				if($activeAdminModuleInstance->getCustomPostType()->PostType === PostTypeController::POST_TYPE_USER_PROFILE_FORM)
//				{
//					$htmlCode = '';
//					$htmlCode .= '<div class="uc-settings-section-header uc-clear" style="margin-top: 30px !important;"><h3 class="uc-clear">';
//					$htmlCode .= __( 'User Profile Card Fields', 'ultra-community' );
//					$htmlCode .= ' - ';
//					$htmlCode .= __( 'the fields below will appear in user profile card, underneath avatar image.', 'ultracomm' );
//					$htmlCode .= '';
//					$htmlCode .= '</h3>';
//					$htmlCode .= '</div>';
//
//					$htmlCode .= "<div class=\"uch uc-form-fields-wrapper\">";
//
//					$htmlCode .= '<div class="uc-g">';
//
//					$htmlCode .= '<div class="uc-form-fields-list-holder uc-u-1">';
//
//					$htmlCode .= FormsAdminPage::getProfileHeaderFieldsListOutputContent( $activeAdminModuleInstance );
//
//					$htmlCode .= '<div class =  "uc-form-fields-list-message uc-form-add-new-field uc-hidden"><h4 class="uc-notice uc-notice-success"></h4></div>';
//
//					$addNewFieldButtonText = __( 'Add New User Profile Card Field', 'ultra-community' );
//
//					if ( $customPostTypeId ) {
//						$htmlCode .= '<div class="uc-form-add-new-field">';
//						$htmlCode .= "<button data-postid = \"$customPostTypeId\" class=\"uc-button uc-button-primary btn-uc-add-new-profile-header-field \" >";
//						$htmlCode .= "<i class=\"fa fa-plus\"></i> $addNewFieldButtonText";
//						$htmlCode .= "</button>";
//						$htmlCode .= '</div>';
//
//					}
//
//
//					$htmlCode .= '</div>';
//					$htmlCode .= '</div>';
//					$htmlCode .= '</div>';
//
//					echo $htmlCode;
//
//				}


				break;
			}


		}, 10, 2);

	}


	private function registerFormsModules()
	{
		$activeFormType = self::getActiveFormType();

		if($activeFormType === 'user-profile' && ModulesController::isModuleRegistered(ModulesController::MODULE_USER_PROFILE_FORM))
		{
			$arrGroupedModules = array();
			foreach ( PostTypeController::getPublishedPosts( PostTypeController::POST_TYPE_USER_PROFILE_FORM ) as $publishedPostType ) {
				$formAdminModuleInstance = UserProfileFormAdminModule::getInstance( true );
				$formAdminModuleInstance->setCustomPostType( $publishedPostType );
				$arrGroupedModules[] = $formAdminModuleInstance;
				unset( $formAdminModuleInstance );
			}

			$this->registerGroupedModules(array(new MchGroupedModules(__('UserProfile Forms Settings', 'ultra-community'), $arrGroupedModules)));

		}

		if($activeFormType === 'registration' && ModulesController::isModuleRegistered(ModulesController::MODULE_REGISTER_FORM))
		{
			$arrGroupedModules = array();
			foreach ( PostTypeController::getPublishedPosts( PostTypeController::POST_TYPE_REGISTER_FORM ) as $publishedPostType ) {
				$formAdminModuleInstance = RegisterFormAdminModule::getInstance( true );
				$formAdminModuleInstance->setCustomPostType( $publishedPostType );
				$arrGroupedModules[] = $formAdminModuleInstance;
				unset( $formAdminModuleInstance );
			}

			$this->registerGroupedModules(array(new MchGroupedModules(__('Register Forms Settings', 'ultra-community'), $arrGroupedModules)));

		}

		if($activeFormType === 'login' &&  ModulesController::isModuleRegistered(ModulesController::MODULE_LOGIN_FORM))
		{
			$arrGroupedModules = array();
			foreach ( PostTypeController::getPublishedPosts( PostTypeController::POST_TYPE_LOGIN_FORM ) as $publishedPostType ) {
				$formAdminModuleInstance = LoginFormAdminModule::getInstance( true );
				$formAdminModuleInstance->setCustomPostType( $publishedPostType );
				$arrGroupedModules[] = $formAdminModuleInstance;
				unset( $formAdminModuleInstance );
			}

			$this->registerGroupedModules(array(new MchGroupedModules(__('Login Forms Settings', 'ultra-community'), $arrGroupedModules)));

		}

		if($activeFormType === 'forgot-password' &&  ModulesController::isModuleRegistered(ModulesController::MODULE_FORGOT_PASSWORD_FORM))
		{
			$arrGroupedModules = array();
			foreach ( PostTypeController::getPublishedPosts( PostTypeController::POST_TYPE_FORGOT_PASSWORD_FORM ) as $publishedPostType ) {
				$formAdminModuleInstance = ForgotPasswordFormAdminModule::getInstance( true );
				$formAdminModuleInstance->setCustomPostType( $publishedPostType );
				$arrGroupedModules[] = $formAdminModuleInstance;
				unset( $formAdminModuleInstance );
			}

			$this->registerGroupedModules(array(new MchGroupedModules(__('Forgot Password Forms Settings', 'ultra-community'), $arrGroupedModules)));

		}

	}

	public function getPageHiddenContent()
	{
		$outputContent = '';

		/**
		 * @var $activeAdminModule \UltraCommunity\Modules\BaseAdminModule
		 */
		foreach ($this->getActiveAdminModules() as $activeAdminModule)
		{
			$text = null;
			if($activeAdminModule->getCustomPostType())
			{
				switch ($activeAdminModule->getCustomPostType()->PostType)
				{
					case PostTypeController::POST_TYPE_LOGIN_FORM :
						$text =  __('Login Form', 'ultra-community');
						break;
					case PostTypeController::POST_TYPE_REGISTER_FORM :
						$text = __('Registration Form', 'ultra-community');
						break;
					case PostTypeController::POST_TYPE_USER_PROFILE_FORM :
						$text =  __('User Profile Form', 'ultra-community');
						break;
					case PostTypeController::POST_TYPE_FORGOT_PASSWORD_FORM :
						$text =  __('Forgot Password Form', 'ultra-community');
						break;

				}
			}

			$confirmationText = __('Are you sure you want to delete this ', 'ultra-community') ;
			$outputContent .= $this->getConfirmationPopupContent(__('Delete', 'ultra-community') . " $text", $confirmationText . strtolower($text) . '?', 'uc-popup-delete-form-' . $activeAdminModule->getSettingKey());


			$addNewFormPopupContent = <<<ADDNEWFORM
<p style="font-size: 1.1em;font-weight: 500;text-align: center;margin: 15px 0;">Adding New $text</p>
ADDNEWFORM;

			$outputContent .= UltraCommUtils::getWrappedPopupHolderContent(
									'uc-popup-add-new-form-' . $activeAdminModule->getSettingKey(),
									__('Add New ', 'ultra-community') . " $text",
									$addNewFormPopupContent, null
							);

			$embedText = __('To embed this form on your site, please copy and paste the following shortcode inside a page', 'ultra-community');


			$embedShortCode = ShortCodesController::getEmbeddableShortCode($activeAdminModule->getCustomPostType());


			$embedOutputContent = <<<EMBEDFORM
<div class = "uc-gap-20"></div>

	<p style="font-size: 1.1em;font-weight: 400;text-align: center;margin:0;">$embedText</p>

		<p style="font-size: 1.3em; width: 70%; font-weight: 500;text-align: center;padding:10px 0; margin:15px auto; border:1px solid #ccc; color:#000;">$embedShortCode</p>

<div class = "uc-gap-20"></div>
EMBEDFORM;

			$outputContent .= UltraCommUtils::getWrappedPopupHolderContent(
				'uc-popup-embed-form-short-code-' . $activeAdminModule->getSettingKey(),
				__('Embed ', 'ultra-community') . " $text",
				$embedOutputContent, null
			);

		}


		$saveFieldFooterContent = <<<FC

		<div class = "uc-g">
			<div class = "uc-u-1-1">
				<button class = "uc-button uc-button-primary">Submit</button>
			</div>
		</div>

FC;

//		$outputContent .= UltraCommUtils::getWrappedPopupHolderContent( 'uc-popup-form-fields-type-list', __( 'Choose Field Type', 'ultra-community' ), BaseFormAdminModule::renderAllFormFieldsTypeForAdminModal(), $saveFieldFooterContent );

		$outputContent .= UltraCommUtils::getWrappedPopupHolderContent( 'uc-popup-form-fields-type-list', __( 'Choose Field Type', 'ultra-community' ), '', $saveFieldFooterContent );


		//$outputContent .= UltraCommUtils::getWrappedPopupHolderContent( 'uc-popup-profile-header-fields-list', __( 'Add profile header field', 'ultra-community' ),$activeAdminModule->renderAllProfileHeaderFieldsForAdminModal(), '' );



		$outputContent .= $this->getConfirmationPopupContent(__('Delete Field', 'ultra-community'), __('Are you sure you want to delete this field?', 'ultra-community') , 'uc-popup-delete-form-field');



		return $outputContent;
	}


	public static function getFormFieldsListOutputContent(BaseFormAdminModule $formAdminModule)
	{
		$customPostTypeId = $formAdminModule->getCustomPostTypeId();

		$htmlCode = '';

		$htmlCode .= "<ul  id = \"uc-form-fields-list-$customPostTypeId\" class=\"uc-form-fields-list  uc-sortable-list\">";

		$fieldCounter = 0;
		foreach ((array)$formAdminModule->getOption(BaseFormAdminModule::OPTION_FORM_FIELDS) as $formField)
		{
			$formFieldId = $formField->UniqueId . '-' . $customPostTypeId;

			$htmlCode .= "<li id=\"$formFieldId\" data-formcustompostid = \"$customPostTypeId\">";

			$htmlCode .= '<ul class="form-field-info uc-clear">';
			$htmlCode .= '<li>';
			$htmlCode .= "<span>{$formField->Name}</span>";
			$htmlCode .= '</li>';
			$htmlCode .= '</ul>';

			$editButtonTitle   = __('Edit Field', 'ultracomm');
			$deleteButtonTitle = __('Delete Field', 'ultracomm');


			$htmlCode .= '<ul class="form-field-actions">';
			$htmlCode .= '<li>';
			$htmlCode .= "<button data-postid = \"{$customPostTypeId}\"  data-fieldid = \"{$formField->UniqueId}\" class=\"uc-button uc-button-primary uc-tooltip uc-edit-form-field\" title = \"$editButtonTitle\"><i class=\"fa fa-pencil-square\"></i></button>";
			$htmlCode .= '</li>';
			$htmlCode .= '<li>';
			$htmlCode .= "<button data-postid = \"{$customPostTypeId}\" data-fieldid = \"$formField->UniqueId\" class=\"uc-button uc-button-danger uc-tooltip uc-delete-form-field\" title = \"$deleteButtonTitle\"><i class=\"fa fa-trash\"></i></button>";
			$htmlCode .= '</li>';
			$htmlCode .= '</ul>';


			$htmlCode .= '</li>';

		}

		$htmlCode .= '</ul>';

		return $htmlCode;
	}

	public static function getProfileHeaderFieldsListOutputContent(BaseFormAdminModule $formAdminModule)
	{
		$customPostTypeId = $formAdminModule->getCustomPostTypeId();

		$htmlCode = '';

		$htmlCode .= "<ul  id = \"uc-profile-header-fields-list-$customPostTypeId\" class=\"uc-form-fields-list\">";

		foreach ((array)$formAdminModule->getOption(BaseFormAdminModule::OPTION_PROFILE_HEADER_FIELDS) as $fieldKey => $fieldUniqueIdOrClass)
		{

			$profileHeaderField =  $formAdminModule->getFormFieldByUniqueId($fieldUniqueIdOrClass);
			if(null === $profileHeaderField)
			{
				if(MchUtils::isNullOrEmpty($profileHeaderField = BaseFormAdminModule::getFieldInstanceByShortClassName($fieldUniqueIdOrClass))){
					continue;
				}

				$profileHeaderField->Name = $profileHeaderField->getDisplayableFieldType();

			}

			if(null === $profileHeaderField){
				continue;
			}

//			if($profileHeaderField instanceof SocialNetworkUrlField)
//			{
//				$profileHeaderField->Name = $profileHeaderField->getDisplayableFieldType($profileHeaderField);
//			}


			$htmlCode .= "<li id = \"{$fieldKey}\" data-formcustompostid = \"$customPostTypeId\">";

			$htmlCode .= '<ul class="form-field-info uc-clear">';
			$htmlCode .= '<li>';
			$htmlCode .= "<span>{$profileHeaderField->Name}</span>";
			$htmlCode .= '</li>';
			$htmlCode .= '</ul>';

			$deleteButtonTitle = __('Delete Field', 'ultracomm');

			$htmlCode .= '<ul class="form-field-actions">';
			$htmlCode .= '<li>';
			$htmlCode .= "<button data-postid = \"{$customPostTypeId}\" data-fieldid = \"$fieldKey\" class=\"uc-button uc-button-danger uc-tooltip uc-delete-profile-header-field\" title = \"$deleteButtonTitle\"><i class=\"fa fa-trash\"></i></button>";
			$htmlCode .= '</li>';
			$htmlCode .= '</ul>';


			$htmlCode .= '</li>';

		}

		$htmlCode .= '</ul>';

		return $htmlCode;
	}

}

