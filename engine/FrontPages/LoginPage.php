<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\FrontPages;
use UltraCommunity\Controllers\FrontPageController;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Controllers\TemplatesController;
use UltraCommunity\Controllers\UserController;
use UltraCommunity\Controllers\UserRoleController;
use UltraCommunity\Entities\UserEntity;
use UltraCommunity\Entities\UserMetaEntity;
use UltraCommunity\MchLib\Utils\MchHtmlUtils;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Modules\Forms\LoginForm\LoginFormAdminModule;
use UltraCommunity\Modules\Forms\LoginForm\LoginFormPublicModule;
use UltraCommunity\Modules\UserRole\UserRoleAdminModule;
use UltraCommunity\Repository\UserRepository;
use UltraCommunity\UltraCommHelper;
use UltraCommunity\UltraCommHooks;;
use UltraCommunity\UltraCommUtils;

class LoginPage extends BasePage
{
	CONST USER_ACTIVATE_ACCOUNT_TOKEN_QUERY_ARG = 'uc-activate-account-token';

	private static $errorMessage   = null;
	private static $successMessage = null;

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

		if($customPostType->PostType !== PostTypeController::POST_TYPE_LOGIN_FORM)
			return null;

		$loginFormPublicModuleInstance = PostTypeController::getAssociatedPublicModuleInstance($customPostType);


		$headerTitle         = wp_kses_data($loginFormPublicModuleInstance->getOption(LoginFormAdminModule::OPTION_FORM_HEADER_TITLE));

		$rememberMeLabel     = esc_html($loginFormPublicModuleInstance->getOption(LoginFormAdminModule::OPTION_FORM_REMEMBER_ME_LABEL));
		$forgotPasswordLabel = esc_html($loginFormPublicModuleInstance->getOption(LoginFormAdminModule::OPTION_FORM_FORGOT_PASSWORD_LABEL));
		$forgotPasswordUrl   = esc_url(FrontPageController::getPageUrl(FrontPageController::PAGE_FORGOT_PASSWORD));

		$submitButtonText    = esc_html($loginFormPublicModuleInstance->getOption(LoginFormAdminModule::OPTION_FORM_PRIMARY_BUTTON_TEXT));


		$registrationLabel  =  esc_html($loginFormPublicModuleInstance->getOption(LoginFormAdminModule::OPTION_REGISTRATION_TEXT));
		$registrationUrl    =  MchWpUtils::getPageUrl($loginFormPublicModuleInstance->getOption(LoginFormAdminModule::OPTION_REGISTRATION_PAGE_ID));


		$additionalClasses   = implode(' ', array('uc-login-form-' . $loginFormPublicModuleInstance->getCustomPostTypeId()));

		$errorMessage   = self::$errorMessage;
		$successMessage = self::$successMessage;

		self::$errorMessage   = null;
		self::$successMessage = null;

		$arrFormFields = array();
		foreach ((array)$loginFormPublicModuleInstance->getOption(LoginFormAdminModule::OPTION_FORM_FIELDS) as $formField)
		{
			$arrFormFields[] = $formField;
		}

		$selfInstance = $this;
		MchWpUtils::addActionHook(UltraCommHooks::ACTION_LOGIN_FORM_BOTTOM, function () use($selfInstance){
			echo MchHtmlUtils::createFormElement(MchHtmlUtils::FORM_ELEMENT_INPUT_HIDDEN, array( 'name' => $selfInstance->getPostRequestActionKey(), 'value' => $selfInstance->getModuleCustomPostType()->PostId));
		});

		$arrTemplateParameters = compact(
								'loginFormPublicModuleInstance',
								'arrFormFields',
								'headerTitle',
								'rememberMeLabel',
								'forgotPasswordLabel',
								'forgotPasswordUrl',
								'submitButtonText', 'errorMessage', 'successMessage', 'registrationUrl', 'registrationLabel',
								'additionalClasses');

		return preg_replace('#\s(id)="[^"]+"#', '', TemplatesController::getTemplateOutputContent(TemplatesController::LOGIN_FORM_TEMPLATE, $arrTemplateParameters));


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

	public function getContentMarkup()
	{
		return MchUtils::captureOutputBuffer(function(){
			current_filter() !== 'the_content' ? the_content() : null;
		});
	}



	public function processRequest()
	{
		if(!empty($_GET['uc-action']) && $_GET['uc-action'] === 'logout'){
			MchWpUtils::logOutCurrentUser(UserController::isUserLoggedIn() ? FrontPageController::getLoggedInUserProfileUrl() : FrontPageController::getLogInPageUrl());
		}

		if(UserController::isUserLoggedIn() && !UltraCommUtils::isPreviewRequest()){
			MchWpUtils::logOutCurrentUser(FrontPageController::getActivePageUrl());
		}

		MchWpUtils::sendNoCacheHeaders();

		$selfInstance = $this;

		MchWpUtils::addActionHook(UltraCommHooks::ACTION_AFTER_USER_LOGGED_IN, function ($userEntity = null) use ($selfInstance){

			if(!$userEntity instanceof UserEntity)
				return;

			$redirectTo = null;

			foreach(UserRoleController::getUserRolePostTypes($userEntity) as $userRolePostType)
			{
				if(null === ($userRoleInstance = PostTypeController::getAssociatedPublicModuleInstance($userRolePostType)))
					continue;

				$roleRedirectUrl = $userRoleInstance->getOption(UserRoleAdminModule::OPTION_AFTER_LOGIN_REDIRECT_URL);
				if(empty($roleRedirectUrl))
					continue;

				$redirectTo = $roleRedirectUrl; break;

			}

			!empty($redirectTo) ?: $redirectTo = UltraCommHelper::getUserProfileUrl($userEntity);


			if(empty($userEntity->UserMetaEntity->LastLoggedIn)){
				$redirectTo = FrontPageController::getUserSettingsPageUrlBySection(UserSettingsPage::SETTINGS_SECTION_PROFILE_FORM_SECTION, UserController::getLoggedInUser()->NiceName, null, false);
			}

			$userEntity->UserMetaEntity->LastLoggedIn = MchWpUtils::getSiteCurrentTimestamp();
			UserController::saveUserInfo($userEntity);

			!MchWpUtils::isAdminLoggedIn() ?: $redirectTo = admin_url();

			MchWpUtils::redirectToUrl(empty($redirectTo) ? home_url('/') : esc_url($redirectTo));

		}, 10, 1);


		do_action(UltraCommHooks::ACTION_BEFORE_STARTING_AUTHENTICATION);

		if(!empty($_GET['user-id']) && !empty($_GET[self::USER_ACTIVATE_ACCOUNT_TOKEN_QUERY_ARG]))
		{
			$this->processUserAccountActivation($_GET['user-id'], $_GET[LoginPage::USER_ACTIVATE_ACCOUNT_TOKEN_QUERY_ARG]);
		}

	}

	private function processUserAccountActivation($userId, $userToken)
	{
		$userId = (int)$userId;
		try
		{
			$storedUserToken = UserRepository::getUserRegistrationEmailToken($userId);
			if(empty($storedUserToken)){
				FrontPageController::redirectToLogInPage();
			}

			if($storedUserToken !== trim($userToken))
			{
				self::$errorMessage = esc_html__('There was an error while precessing your request!', 'ultra-community');
			}
			else
			{
				UserRepository::deleteUserRegistrationEmailToken($userId);
				UserController::changeUserStatus($userId, UserMetaEntity::USER_STATUS_APPROVED);
				self::$successMessage = esc_html__('Your account has been successfully activated!', 'ultra-community');
			}
		}
		catch(\Exception $ue)
		{
			self::$errorMessage = $ue->getMessage();
		}

	}

	private function processAuthentication()
	{
		/**
		 * @var $publicLoginModuleInstance LoginFormPublicModule
		 */
		if( MchUtils::isNullOrEmpty($publicLoginModuleInstance = PostTypeController::getAssociatedPublicModuleInstance($this->getModuleCustomPostType())) ){
			FrontPageController::redirectToHomePage(); // change it to redirect to activepage with error
		}
		try
		{
			$publicLoginModuleInstance->authenticateUser();
		}
		catch(\Exception $ue)
		{
			self::$errorMessage = $ue->getMessage();
			//$this->setErrorMessage($ue->getMessage());
		}
	}


	public function isAuthenticationRequired()
	{
		return false;
	}

	public function getPageCustomCss()
	{
		return parent::getPageCustomCss();
	}
	
	public function getSubMenuTemplateArguments()
	{
		// TODO: Implement getSubMenuTemplateArguments() method.
	}
}