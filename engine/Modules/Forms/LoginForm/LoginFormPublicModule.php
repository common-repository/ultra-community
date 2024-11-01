<?php

namespace UltraCommunity\Modules\Forms\LoginForm;

use UltraCommunity\Controllers\UserController;
use UltraCommunity\Entities\UserMetaEntity;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\Repository\WpUserRepository;
use UltraCommunity\Modules\Forms\BaseForm\BaseFormPublicModule;
use UltraCommunity\Modules\Forms\FormFields\BaseField;
use UltraCommunity\Modules\Forms\FormFields\UserEmailField;
use UltraCommunity\Modules\Forms\FormFields\UserNameField;
use UltraCommunity\Modules\Forms\FormFields\UserNameOrEmailField;
use UltraCommunity\Modules\Forms\FormFields\UserPasswordField;
use UltraCommunity\Modules\UserRole\UserRoleAdminModule;
use UltraCommunity\Repository\UserRepository;
use UltraCommunity\UltraCommException;
use UltraCommunity\UltraCommHelper;
use UltraCommunity\UltraCommHooks;

class LoginFormPublicModule extends BaseFormPublicModule
{

	protected function __construct()
	{
		parent::__construct();
	}

	private function registerLoginHooks()
	{

	}

	public function authenticateUser()
	{

		if(null === $this->getCustomPostType()){
			throw new UltraCommException(__('An error has been encountered while trying to authenticate!', 'ultra-community'));
		}

		$this->registerLoginHooks();

		$userName = $userPassword = null;

		
		/**
		 * @var $postedField BaseField
		 */
		$arrPostedFields = $this->getFieldsByInstance(new UserNameField());
		if ( ! MchUtils::isNullOrEmpty($postedField = reset($arrPostedFields))) {
			if($postedField->isValid(true)){
				$userName = $postedField->Value;
			}
		}


		if(empty($userName)){
			$arrPostedFields = $this->getFieldsByInstance(new UserNameOrEmailField());
			if ( ! MchUtils::isNullOrEmpty($postedField = reset($arrPostedFields))) {
				if($postedField->isValid(true)){
					$userName = $postedField->Value;
				}
			}
		}
		
		
		if(empty($userName)){
			$arrPostedFields = $this->getFieldsByInstance(new UserEmailField());
			if ( ! MchUtils::isNullOrEmpty($postedField = reset($arrPostedFields))) {
				if($postedField->isValid(true)){
					$userName = $postedField->Value;
				}
			}
		}

		$arrPostedFields = $this->getFieldsByInstance(new UserPasswordField());
		if ( ! MchUtils::isNullOrEmpty($postedField = reset($arrPostedFields))) {
			if($postedField->isValid(true)){
				$userPassword = $postedField->Value;
			}
		}

		if(empty($userName)){
			throw new UltraCommException(__('Please provide a valid Username', 'ultra-community'));
		}

		if(empty($userPassword)){
			throw new UltraCommException(__('Please provide a valid Password', 'ultra-community'));
		}

		/**
		 *  TODO Add WPBruiser here
		 */


		do_action(UltraCommHooks::ACTION_BEFORE_USER_LOG_IN, $userName);

		$arrCredentials = array(
			'user_login'    => $userName,
			'user_password' => $userPassword,
			'remember'      => isset($_POST['rememberme'])
		);


		//WPBruiser HERE


		$wpUser = is_email($userName) ? WpUserRepository::getUserByEmail($userName) : WpUserRepository::getUserByUserName($userName);



		if( ! MchWpUtils::isWPUser($wpUser) ){
			throw new UltraCommException(__('Invalid username or password!', 'ultra-community'));
		}


		$userEntity = UserRepository::getUserEntityBy($wpUser);

		if(empty($userEntity->UserMetaEntity->UserStatus))
		{
			$userEntity->UserMetaEntity->UserStatus = UserMetaEntity::USER_STATUS_APPROVED;
			UserController::saveUserInfo($userEntity);
		}

		switch ($userEntity->UserMetaEntity->UserStatus)
		{
			case UserMetaEntity::USER_STATUS_APPROVED:
				break;

			case UserMetaEntity::USER_STATUS_AWAITING_REVIEW :
				throw new UltraCommException(__('Your account is awaiting admin approval!', 'ultra-community'));
				break;

			case UserMetaEntity::USER_STATUS_AWAITING_EMAIL_CONFIRMATION :
				throw new UltraCommException(__('Your account has not been confirmed yet!', 'ultra-community'));
				break;

			default :
				throw new UltraCommException(__('Your account is awaiting admin moderation!', 'ultra-community'));
				break;
		}

//		$userRoleInstance = UltraCommHelper::getUserRolePublicInstanceByUserInfo($userEntity);
//		if(null === $userRoleInstance){
//			throw new UltraCommException(__('Your account is awaiting admin moderation', 'ultra-community'));
//		}

		$userToLogin = wp_signon($arrCredentials);
		if( ! MchWpUtils::isWPUser($userToLogin) ){
			throw new UltraCommException(__('Invalid username or password', 'ultra-community'));
		}

		wp_set_current_user($userToLogin->ID);

		do_action(UltraCommHooks::ACTION_AFTER_USER_LOGGED_IN, clone $userEntity);

	}

}