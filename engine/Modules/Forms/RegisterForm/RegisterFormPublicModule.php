<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Modules\Forms\RegisterForm;

use UltraCommunity\Controllers\FrontPageController;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Controllers\UserController;
use UltraCommunity\Controllers\UserRoleController;
use UltraCommunity\Entities\UserMetaEntity;
use UltraCommunity\Entities\UserEntity;
use UltraCommunity\FrontPages\UserSettingsPage;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\Repository\WpUserRepository;
use UltraCommunity\Modules\Forms\BaseForm\BaseFormAdminModule;
use UltraCommunity\Modules\Forms\BaseForm\BaseFormPublicModule;
use UltraCommunity\Modules\Forms\FormFields\BaseField;
use UltraCommunity\Modules\Forms\FormFields\UserEmailField;
use UltraCommunity\Modules\Forms\FormFields\UserFirstNameField;
use UltraCommunity\Modules\Forms\FormFields\UserFullNameField;
use UltraCommunity\Modules\Forms\FormFields\UserLastNameField;
use UltraCommunity\Modules\Forms\FormFields\UserNameField;
use UltraCommunity\Modules\Forms\FormFields\UserNameOrEmailField;
use UltraCommunity\Modules\Forms\FormFields\UserPasswordField;
use UltraCommunity\Modules\UserRole\UserRoleAdminModule;
use UltraCommunity\Repository\UserRepository;
use UltraCommunity\UltraCommException;
use UltraCommunity\UltraCommHelper;
use UltraCommunity\UltraCommHooks;

class RegisterFormPublicModule extends BaseFormPublicModule
{

	protected function __construct()
	{
		parent::__construct();
	}

	private function registerRegistrationHooks()
	{
//		MchWpUtils::addActionHook(UltraCommHooks::ACTION_AFTER_USER_REGISTERED, function (UserEntity $userEntity, $arrAdditionalInfo){
//
//		}, 99, 2);

	}


	public function processUserRegistration()
	{

		$userEntity = new UserEntity();
		$userName = $userPassword = $userEmail = $userFirstName = $userLastName = $userFullName = null;

		//$this->registerRegistrationHooks();

		$arrPostedFields = $this->getFieldsByInstance(new UserNameOrEmailField());
		/**
		 * @var $postedField BaseField
		 */
		if ( ! MchUtils::isNullOrEmpty($postedField = reset($arrPostedFields))) {
			$postedField->IsRequired = true;
			if($postedField->isValid(true)){
				MchValidator::isEmail($postedField->Value) ? $userEmail = $postedField->Value : $userName = $postedField->Value;
			}
		}

		$arrPostedFields = $this->getFieldsByInstance(new UserNameField());
		if ( ! MchUtils::isNullOrEmpty($postedField = reset($arrPostedFields))) {
			if($postedField->isValid(true)){
				$userName = $postedField->Value;
			}
		}

		$arrPostedFields = $this->getFieldsByInstance(new UserEmailField());
		if ( ! MchUtils::isNullOrEmpty($postedField = reset($arrPostedFields))) {
			if($postedField->isValid(true)){
				$userEmail = $postedField->Value;
			}
		}


		$arrPostedFields = $this->getFieldsByInstance(new UserFirstNameField());
		if ( ! MchUtils::isNullOrEmpty($postedField = reset($arrPostedFields))) {
			if($postedField->isValid(true)){
				$userFirstName = $postedField->Value;
			}
		}

		$arrPostedFields = $this->getFieldsByInstance(new UserLastNameField());
		if ( ! MchUtils::isNullOrEmpty($postedField = reset($arrPostedFields))) {
			if($postedField->isValid(true)){
				$userLastName = $postedField->Value;
			}
		}

		$arrPostedFields = $this->getFieldsByInstance(new UserFullNameField());
		if ( ! MchUtils::isNullOrEmpty($postedField = reset($arrPostedFields))) {
			if($postedField->isValid(true)){
				$userFullName = $postedField->Value;
			}
		}

//		if(empty($userName))
//		{
//			$userFullName = trim($userFullName .$userFirstName . $userLastName);
//
//			if(!empty($userFullName))
//			{
//
//				$userFullName = str_replace('-', '', MchWpUtils::formatUrlPath($userFullName));
//				$fullNameLength = mb_strlen($userFullName);
//				$fullNameLength < 50 ?: $fullNameLength = 50;
//
//				if($fullNameLength > 3)
//				{
//					$userName = mb_substr($userFullName, 0, 3);
//					for($i = 3; $i < $fullNameLength; ++$i)
//					{
//						$userName = sanitize_user( $userName . $userFullName[$i], true );
//						 if(validate_username($userName) && !username_exists($userName)){
//							 break;
//						 }
//					}
//
//					isset($userName[3]) ?:  $userName = null;
//				}
//			}
//
//		}
//
//		if(empty($userName) && !empty($userEmail))
//		{
//			$userName = sanitize_user(strstr($userEmail, '@', true));
//
//			if(!empty($userName))
//			{
//				if ( ! validate_username( $userName ) || username_exists( $userName ) )
//				{
//					for ( $i = 1; $i < 10; ++ $i )
//					{
//						$tmpUserName = 	sanitize_user($userName . $i);
//						if((validate_username($tmpUserName) && !username_exists($tmpUserName)))
//						{
//							$userName = $tmpUserName;break;
//						}
//					}
//				}
//
//			}
//		}


		$arrPostedFields = $this->getFieldsByInstance(new UserPasswordField());
		if ( ! MchUtils::isNullOrEmpty($postedField = reset($arrPostedFields)) ) {
			$userPassword = $postedField->Value;
			if(empty($userPassword)){
				throw new UltraCommException(__('Please provide a valid Password', 'ultra-community'));
			}
			$postedField = null;
			$arrSuppliedPasswords = array();
			foreach($arrPostedFields as $postedField){
				$arrSuppliedPasswords[] = $postedField->Value;
			}

			if(1 !== count(array_flip($arrSuppliedPasswords))){
				throw new UltraCommException(__("Passwords you've entered don't match!", 'ultra-community'));
			}

		}

		if(empty($userName)){
			throw new UltraCommException(__('Cannot continue registration without a valid username!', 'ultra-community'));
		}

		$userEntity->UserName = $userName;
		$userEntity->Email    = $userEmail;
		$userEntity->Password = empty($userPassword) ? wp_generate_password( 8, false ) : $userPassword;

		$userEntity->UserMetaEntity instanceof UserMetaEntity ?: $userEntity->UserMetaEntity = new UserMetaEntity();

		foreach($this->getAllFields() as $postedField)
		{
			if(!$postedField->isValid(true))
				continue;

			$mappedProperty = $postedField->getUserEntityMappedFieldName();
			if(null !== $mappedProperty && MchUtils::stringStartsWith($mappedProperty, '_meta_')){
				$mappedProperty = str_replace('_meta_','', $mappedProperty);
				!property_exists($userEntity->UserMetaEntity, $mappedProperty) ?: $userEntity->UserMetaEntity->{$mappedProperty} = $postedField->Value;
				continue;
			}

			if(null !== $mappedProperty)
			{
				if(!property_exists($userEntity, $mappedProperty))
					continue;

				if(!empty($userEntity->{$mappedProperty}))
					$userEntity->{$mappedProperty} = $postedField->Value;

				continue;
			}

			$userEntity->UserMetaEntity->RegisterFormValues[$postedField->UniqueId] = $postedField->Value;
		}

		$userRolePostType       = PostTypeController::getPostTypeInstanceByPostId($this->getOption(BaseFormAdminModule::OPTION_USER_ROLE_CUSTOM_POST_ID));
		$userRolePublicInstance = PostTypeController::getAssociatedPublicModuleInstance($userRolePostType);

		if(null === $userRolePublicInstance || (null === $userRolePublicInstance->getOption(UserRoleAdminModule::OPTION_ROLE_SLUG))){
			throw new UltraCommException(__('An error has been encountered during registration!', 'ultra-community'));
		}

		if(in_array($userRolePublicInstance->getOption(UserRoleAdminModule::OPTION_ROLE_SLUG), array(UserRoleController::ROLE_ADMIN_SLUG))){
			throw new UltraCommException(__('Administrators cannot be registered through front-end!', 'ultra-community'));
		}

		$userEntity->IsUltraCommUser = true;

		do_action(UltraCommHooks::ACTION_BEFORE_USER_REGISTRATION, $userEntity, $this);

		$userId = UserRepository::saveUser($userEntity);

		if(null === ($wpUser = WpUserRepository::getUserById($userId))){
			throw new UltraCommException(__('An error has been encountered during registration!', 'ultra-community'));
		}


		foreach($this->getAllFields() as $postedField)
		{
			if(empty($postedField->MappedUserMetaKey))
				continue;

			if($postedField->getUserEntityMappedFieldName())
				continue;

			if(!isset($userEntity->UserMetaEntity->RegisterFormValues[$postedField->UniqueId]))
				continue;

			update_user_meta($userId, $postedField->MappedUserMetaKey, $postedField->Value);

		}


		$wpUser->set_role($userRolePublicInstance->getOption(UserRoleAdminModule::OPTION_ROLE_SLUG));

		$userEntity = UserRepository::getUserEntityBy($wpUser);

		$arrAdditionalInfo = array(
									'autoLogin'      => false,
									'redirectTo'     => null,
									'successMessage' => null,
								);


		$userEntity->UserMetaEntity->UserStatus = UserMetaEntity::USER_STATUS_AWAITING_REVIEW;

		switch ($userRolePublicInstance->getOption(UserRoleAdminModule::OPTION_AFTER_REGISTRATION_ACTION))
		{
			case UserRoleAdminModule::REGISTRATION_ACTION_AUTO_APPROVE :
				$userEntity->UserMetaEntity->UserStatus = UserMetaEntity::USER_STATUS_APPROVED;
				break;
			case UserRoleAdminModule::REGISTRATION_ACTION_ADMIN_REVIEW :
				$userEntity->UserMetaEntity->UserStatus = UserMetaEntity::USER_STATUS_AWAITING_REVIEW;
				break;
			case UserRoleAdminModule::REGISTRATION_ACTION_SEND_EMAIL:
				$userEntity->UserMetaEntity->UserStatus = UserMetaEntity::USER_STATUS_AWAITING_EMAIL_CONFIRMATION;
				break;
		}



		switch ($userEntity->UserMetaEntity->UserStatus)
		{
			case UserMetaEntity::USER_STATUS_APPROVED:

				$arrAdditionalInfo['autoLogin']      = true;

				$arrAdditionalInfo['redirectTo']     = !MchUtils::isNullOrEmpty($redirectUrl = $userRolePublicInstance->getOption(UserRoleAdminModule::OPTION_AFTER_REGISTRATION_REDIRECT_URL))
														? $redirectUrl : FrontPageController::getUserSettingsPageUrlBySection(UserSettingsPage::SETTINGS_SECTION_PROFILE_FORM_SECTION, $userEntity->NiceName, null, false);

				$arrAdditionalInfo['successMessage'] = __('Thank you for registering with us!', 'ultra-community');

				break;

			case UserMetaEntity::USER_STATUS_AWAITING_REVIEW:

				$arrAdditionalInfo['successMessage'] = __('Thank you for registering with us! One of our team member will review your account and will be in touch with you shortly.', 'ultra-community');
				$arrAdditionalInfo['redirectTo'] = home_url('/');

				break;

			case UserMetaEntity::USER_STATUS_AWAITING_EMAIL_CONFIRMATION :

				UserRepository::saveUserRegistrationEmailToken($userEntity->Id, wp_generate_password(14, false, false));

				$arrAdditionalInfo['successMessage'] = __('Thank you for registering with us! Please check your email and follow the link to complete the registration.', 'ultra-community');
				$arrAdditionalInfo['redirectTo'] = home_url('/');

				break;
		}

		$arrAdditionalInfo = apply_filters(UltraCommHooks::FILTER_REGISTRATION_ADDITIONAL_INFO, $arrAdditionalInfo, clone $userEntity, $this);

		empty($arrAdditionalInfo['autoLogin']) ?: MchWpUtils::autoLogInUser($userEntity->Id);

		unset($arrAdditionalInfo['autoLogin']);

		UserController::changeUserStatus( $userEntity->Id, $userEntity->UserMetaEntity->UserStatus);

		do_action(UltraCommHooks::ACTION_AFTER_USER_REGISTERED, clone $userEntity, $arrAdditionalInfo, $this);

	}

}