<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\FrontPages;


use UltraCommunity\Controllers\FrontPageController;
use UltraCommunity\Controllers\NotificationsController;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Controllers\TemplatesController;
use UltraCommunity\Controllers\UserController;
use UltraCommunity\MchLib\Utils\MchHtmlUtils;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\Repository\WpPostRepository;
use UltraCommunity\MchLib\WordPress\Repository\WpUserRepository;
use UltraCommunity\Modules\Forms\ForgotPasswordForm\ForgotPasswordFormAdminModule;
use UltraCommunity\Modules\Forms\ForgotPasswordForm\ForgotPasswordFormPublicModule;
use UltraCommunity\Modules\GeneralSettings\FrontPage\FrontPageSettingsPublicModule;
use UltraCommunity\PostsType\ForgotPasswordFormPostType;
use UltraCommunity\Repository\UserRepository;
use UltraCommunity\UltraCommException;
use UltraCommunity\UltraCommHooks;
use UltraCommunity\UltraCommUtils;

class ForgotPasswordPage extends BasePage
{
	CONST USER_RESET_PASSWORD_TOKEN_QUERY_ARG   = 'uc-reset-password-token';

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

		if($customPostType->PostType !== PostTypeController::POST_TYPE_FORGOT_PASSWORD_FORM)
			return null;


		$formPublicModuleInstance = PostTypeController::getAssociatedPublicModuleInstance($customPostType);
		$headerTitle         = wp_kses_data($formPublicModuleInstance->getOption(ForgotPasswordFormAdminModule::OPTION_FORM_HEADER_TITLE));

		$submitButtonText    = esc_html($formPublicModuleInstance->getOption(ForgotPasswordFormAdminModule::OPTION_FORM_PRIMARY_BUTTON_TEXT));

		$additionalClasses   = implode(' ', array('uc-forgot-pass-form-' . $formPublicModuleInstance->getCustomPostTypeId()));


		$arrFormFields = array();
		foreach ((array)$formPublicModuleInstance->getOption(ForgotPasswordFormAdminModule::OPTION_FORM_FIELDS) as $formField)
		{
			$arrFormFields[] = $formField;
		}


		$showResetPasswordForm  = $this->shouldShowResetPasswordForm();
		$passwordResetUserId    = $showResetPasswordForm ? $_GET['user-id'] : null;
		$passwordResetUserToken = $showResetPasswordForm ? $_GET[self::USER_RESET_PASSWORD_TOKEN_QUERY_ARG] : null;


		$arrTemplateParameters = compact(
			'formPublicModuleInstance',
			'arrFormFields',
			'headerTitle',
			'submitButtonText',
			'additionalClasses',
			'showResetPasswordForm', 'passwordResetUserId', 'passwordResetUserToken'
		);

		$selfInstance = $this;
		MchWpUtils::addActionHook(UltraCommHooks::ACTION_FORGOT_PASSWORD_FORM_BOTTOM, function () use($selfInstance, $arrTemplateParameters){

			echo MchHtmlUtils::createFormElement(MchHtmlUtils::FORM_ELEMENT_INPUT_HIDDEN, array( 'name' => $selfInstance->getPostRequestActionKey(), 'value' => $selfInstance->getModuleCustomPostType()->PostId));
			echo MchHtmlUtils::createFormElement(MchHtmlUtils::FORM_ELEMENT_INPUT_HIDDEN, array( 'name' => 'txtPasswordResetUserId', 'value' => $arrTemplateParameters['passwordResetUserId']));
			echo MchHtmlUtils::createFormElement(MchHtmlUtils::FORM_ELEMENT_INPUT_HIDDEN, array( 'name' => 'txtPasswordResetUserToken', 'value' => $arrTemplateParameters['passwordResetUserToken']));

		});

		return TemplatesController::getTemplateOutputContent(TemplatesController::FORGOT_PASSWORD_FORM_TEMPLATE, $arrTemplateParameters);

	}


	public function processRequest()
	{
		if(UserController::isUserLoggedIn() && !UltraCommUtils::isPreviewRequest()){
			MchWpUtils::logOutCurrentUser(FrontPageController::getActivePageUrl());
		}

		MchWpUtils::sendNoCacheHeaders();

//		if($this->isPostRequest())
//		{
//			$this->processForgotPassword();
//		}


		if($this->shouldShowResetPasswordForm())
		{
			if(!MchWpUtils::isWPUser($wpUser = WpUserRepository::getUserById($_GET['user-id'])) || !MchWpUtils::isWPUser(check_password_reset_key($_GET[self::USER_RESET_PASSWORD_TOKEN_QUERY_ARG], $wpUser->user_login))){
				unset($_GET['user-id']); // not to show the reset password form
				$this->setErrorMessage(__('Invalid reset password url !', 'ultra-community'));
				return;
			}

			if( $this->isPostRequest() ){
				$this->processResetPassword($wpUser);
			}

		}

	}

	private function processResetPassword($wpUser)
	{
		!isset($_POST['txtPassword'])        ?: $_POST['txtPassword']        = trim($_POST['txtPassword']);
		!isset($_POST['txtConfirmPassword']) ?: $_POST['txtConfirmPassword'] = trim($_POST['txtConfirmPassword']);

		if(empty($_POST['txtPassword'])){
			$this->setErrorMessage(__('Please provide your new password!', 'ultra-community'));
			return;
		}

		if(empty($_POST['txtConfirmPassword'])){
			$this->setErrorMessage(__('Please confirm your new password!', 'ultra-community'));
			return;
		}

		if($_POST['txtPassword'] !== $_POST['txtConfirmPassword']){
			$this->setErrorMessage(__('Password and confirmed password do not match!', 'ultra-community'));
			return;
		}

		reset_password($wpUser, $_POST['txtPassword']);

		add_filter( 'send_password_change_email', '__return_false');

		NotificationsController::sendNotification(NotificationsController::NOTIFICATION_EMAIL_PASSWORD_CHANGED, UserRepository::getUserEntityBy($wpUser));

		$this->redirectWithSuccess(FrontPageSettingsPublicModule::getLoginPageUrl(), self::PAGE_MESSAGE_PASSWORD_CHANGED);
	}

	private function processForgotPassword()
	{
		/**
		 * @var $publicLoginModuleInstance ForgotPasswordFormPublicModule
		 */
		if( MchUtils::isNullOrEmpty($publicModuleInstance = PostTypeController::getAssociatedPublicModuleInstance($this->getModuleCustomPostType())) ){
			FrontPageController::redirectToHomePage(); // change it to redirect to activepage with error
		}

		$selfInstance = $this;
		MchWpUtils::addActionHook(UltraCommHooks::ACTION_AFTER_RESET_PASSWORD_EMAIL_SENT, function () use($selfInstance){
			$selfInstance->setSuccessMessage(__('An email with a link to reset your password has been sent to you!', 'ultra-community'));
		}, PHP_INT_MAX);

		try
		{
			$publicModuleInstance->processForgotPassword();
		}
		catch(UltraCommException $ue)
		{
			$this->setErrorMessage($ue->getMessage());
		}

	}

	public function shouldShowResetPasswordForm()
	{
		return ( !empty($_GET['user-id']) && !empty($_GET[self::USER_RESET_PASSWORD_TOKEN_QUERY_ARG]) && MchValidator::isInteger($_GET['user-id']) );
	}

	public function isAuthenticationRequired()
	{
		return false;
	}

	public function getContentMarkup()
	{
		return MchUtils::captureOutputBuffer(function(){
			current_filter() !== 'the_content' ? the_content() : null;
		});
	}


	public function getPageCustomCss()
	{
		return parent::getPageCustomCss();
	}

	public function getHeaderMarkup()
	{
		return null;
	}

	public function getNavBarMarkup()
	{
		return null;
	}

	public function getSideBarMarkup()
	{
		return null;
	}
	
	public function getSubMenuTemplateArguments()
	{
		return array();
	}
	
}