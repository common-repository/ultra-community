<?php
namespace UltraCommunity\FrontPages;

use UltraCommunity\Controllers\FrontPageController;
use UltraCommunity\Controllers\NotificationsController;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Controllers\TemplatesController;
use UltraCommunity\Controllers\UserController;
use UltraCommunity\Entities\EmailEntity;
use UltraCommunity\Entities\UserEntity;
use UltraCommunity\Entities\UserMetaEntity;
use UltraCommunity\MchLib\Utils\MchHtmlUtils;
use UltraCommunity\MchLib\Utils\MchMinifier;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Modules\Forms\RegisterForm\RegisterFormAdminModule;
use UltraCommunity\Modules\Forms\RegisterForm\RegisterFormPublicModule;
use UltraCommunity\Modules\GeneralSettings\Emails\EmailsSettingsAdminModule;
use UltraCommunity\Modules\GeneralSettings\Emails\EmailsSettingsPublicModule;
use UltraCommunity\UltraCommException;
use UltraCommunity\UltraCommHooks;
use UltraCommunity\UltraCommUtils;

class RegisterPage extends BasePage
{
	public function __construct($pageId = null)
	{
		parent::__construct($pageId);

	}

	public function getShortCodeContent($arrAttributes, $shortCodeText = null, $shortCodeName = null)
	{
		$customPostType = empty($arrAttributes['id']) ? null : PostTypeController::getPostTypeInstanceByPostId($arrAttributes['id']);

		(null !== $customPostType) ?: $customPostType = $this->getModuleCustomPostType();

		if(null === $customPostType)
			return null;

		$this->setModuleCustomPostType($customPostType);

		if($customPostType->PostType !== PostTypeController::POST_TYPE_REGISTER_FORM)
			return null;

		
		$formPublicModuleInstance = PostTypeController::getAssociatedPublicModuleInstance($customPostType);
		
		$headerTitle         = wp_kses_data($formPublicModuleInstance->getOption(RegisterFormAdminModule::OPTION_FORM_HEADER_TITLE));
		$submitButtonText    = esc_html($formPublicModuleInstance->getOption(RegisterFormAdminModule::OPTION_FORM_PRIMARY_BUTTON_TEXT));
		$additionalClasses   = implode(' ', array('uc-register-form-' . $formPublicModuleInstance->getCustomPostTypeId()));

		$arrFormFields = array_values($formPublicModuleInstance->getAllFields());

		$arrTemplateParameters = compact(
				'formPublicModuleInstance',
				'arrFormFields',
				'headerTitle',
				'submitButtonText',
				'additionalClasses');

		
		$selfInstance = $this;
		MchWpUtils::addActionHook(UltraCommHooks::ACTION_REGISTRATION_FORM_BOTTOM, function () use($selfInstance){
			echo MchHtmlUtils::createFormElement(MchHtmlUtils::FORM_ELEMENT_INPUT_HIDDEN, array( 'name' => $selfInstance->getPostRequestActionKey(), 'value' => $selfInstance->getModuleCustomPostType()->PostId));
		});

		return TemplatesController::getTemplateOutputContent(TemplatesController::REGISTRATION_FORM_TEMPLATE, $arrTemplateParameters);


	}

	public function processRequest()
	{
		if(UserController::isUserLoggedIn() && !UltraCommUtils::isPreviewRequest()){
			MchWpUtils::logOutCurrentUser(FrontPageController::getActivePageUrl());
		}

		MchWpUtils::sendNoCacheHeaders();
		
		
		if(!$this->getModuleCustomPostType())
		{
			foreach(PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_REGISTER_FORM) as $registerFormPostType){
				if(null === ($publicInstance = PostTypeController::getAssociatedPublicModuleInstance($registerFormPostType)) || $this->PageId != $publicInstance->getOption(RegisterFormAdminModule::OPTION_ASSIGNED_PAGE_ID))
					continue;
				$this->setModuleCustomPostType($registerFormPostType);
				break;
			}
			
		}
		
		
		do_action(UltraCommHooks::ACTION_BEFORE_STARTING_REGISTRATION);

//		if($this->isPostRequest())
//		{
//			$this->processRegistration();
//		}
	}

	private function processRegistration()
	{
		/**
		 * @var $publicRegisterModuleInstance RegisterFormPublicModule
		 */
		if( MchUtils::isNullOrEmpty($publicRegisterModuleInstance = PostTypeController::getAssociatedPublicModuleInstance($this->getModuleCustomPostType())) ){
			FrontPageController::redirectToHomePage(); // change it to redirect to activepage with error
		}

		if(UserController::isUserLoggedIn()){
			MchWpUtils::logOutCurrentUser($this->getPageUrl());
		}

		try
		{
			$selfInstance = &$this;

			MchWpUtils::addActionHook(UltraCommHooks::ACTION_AFTER_USER_REGISTERED, function (UserEntity $userEntity = null, $arrAdditionalInfo) use(&$selfInstance){

				empty($arrAdditionalInfo['successMessage']) ?: $selfInstance->setSuccessMessage($arrAdditionalInfo['successMessage']);
				empty($arrAdditionalInfo['redirectTo'])     ?: $selfInstance->setRedirectTo($arrAdditionalInfo['redirectTo']);

			}, 99, 2);

			$publicRegisterModuleInstance->processUserRegistration();
		}
		catch(\Exception $ue)
		{
			$this->setErrorMessage($ue->getMessage());
		}

	}

	public function getContentMarkup() {
		return MchUtils::captureOutputBuffer(function(){
			current_filter() !== 'the_content' ? the_content() : null;
		});
	}


	public function isAuthenticationRequired()
	{
		return false;
	}

	public function getPageCustomCss()
	{
		return parent::getPageCustomCss();
	}

	public function getHeaderMarkup() {
		return null;
	}

	public function getNavBarMarkup() {
		return null;
	}

	public function getSideBarMarkup() {
		return null;
	}
	
	public function getSubMenuTemplateArguments()
	{
		return array();
	}
	
}