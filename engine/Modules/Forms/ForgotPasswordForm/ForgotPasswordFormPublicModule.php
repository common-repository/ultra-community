<?php


namespace UltraCommunity\Modules\Forms\ForgotPasswordForm;

use UltraCommunity\Controllers\NotificationsController;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Modules\Forms\BaseForm\BaseFormPublicModule;
use UltraCommunity\Modules\Forms\FormFields\BaseField;
use UltraCommunity\Modules\Forms\FormFields\UserEmailField;
use UltraCommunity\Modules\Forms\FormFields\UserNameField;
use UltraCommunity\Modules\Forms\FormFields\UserNameOrEmailField;
use UltraCommunity\Repository\UserRepository;
use UltraCommunity\UltraCommException;
use UltraCommunity\UltraCommHooks;

class ForgotPasswordFormPublicModule extends BaseFormPublicModule
{
	protected function __construct()
	{
		parent::__construct();
	}

	private function registerForgotPasswordHooks()
	{}

	public function processForgotPassword()
	{
		$this->registerForgotPasswordHooks();

		$userName = null;

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

		if(empty($userName)){
			throw new UltraCommException(__('Please provide a valid username or email address!', 'ultra-community'));
		}

		$userEntity = UserRepository::getUserEntityBy($userName);

		if(null === $userEntity){
			throw new UltraCommException(__('Please provide a valid username or email address!', 'ultra-community'));
		}

		NotificationsController::sendNotification(NotificationsController::NOTIFICATION_EMAIL_PASSWORD_CHANGE_REQUEST, $userEntity);

		do_action(UltraCommHooks::ACTION_AFTER_RESET_PASSWORD_EMAIL_SENT, $userEntity->toWPUser());
		
	}

}