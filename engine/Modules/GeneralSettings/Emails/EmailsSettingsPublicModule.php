<?php
namespace UltraCommunity\Modules\GeneralSettings\Emails;

use UltraCommunity\Entities\EmailEntity;
use UltraCommunity\Entities\UserEntity;
use UltraCommunity\MchLib\Plugin\MchBasePlugin;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\Repository\UserRepository;
use UltraCommunity\UltraCommHooks;

class EmailsSettingsPublicModule extends \UltraCommunity\Modules\BasePublicModule
{
//	private $arrEmailFilters = array();
	public function __construct()
	{
		parent::__construct();
		
//		$this->arrEmailFilters[] = $this->addFilterHook('wp_mail_from', array($this, 'getSenderEmail'), PHP_INT_MAX, 1);
//		$this->arrEmailFilters[] = $this->addFilterHook('wp_mail_from_name', array($this, 'getSenderName'), PHP_INT_MAX, 1);
	}

//	public function getSenderEmail($originalSenderEmail)
//	{
//		return MchUtils::isNullOrEmpty($senderEmail = $this->getOption(EmailsSettingsAdminModule::OPTION_SENDER_EMAIL_ADDRESS)) ? $originalSenderEmail : $senderEmail;
//	}
//
//	public function getSenderName($originalSenderName)
//	{
//		return MchUtils::isNullOrEmpty($senderName = $this->getOption(EmailsSettingsAdminModule::OPTION_SENDER_NAME)) ? $originalSenderName : $senderName;
//	}
	
	/**
	 * @param UserEntity $userEntity
	 *
	 * @return EmailEntity|null
	 */
	public function getWelcomeEmailEntity(UserEntity $userEntity)
	{
		if(empty($userEntity->Email) || !MchValidator::isEmail($userEntity->Email) || !$this->getOption(EmailsSettingsAdminModule::OPTION_WELCOME_EMAIL_ENABLED)){
			return null;
		}

		$emailEntity = new EmailEntity($userEntity->Email, $this->getOption(EmailsSettingsAdminModule::OPTION_WELCOME_EMAIL_SUBJECT));

		$emailEntity->EmailContent = $this->getOption(EmailsSettingsAdminModule::OPTION_WELCOME_EMAIL_MESSAGE);

		MchUtils::isNullOrEmpty($this->getOption(EmailsSettingsAdminModule::OPTION_HTML_TEMPLATES_ENABLED)) ?: $emailEntity->EmailContent =  $this->getEmailContentByFileTemplate('welcome');

		return $emailEntity;
	}

	/**
	 * @param UserEntity $userEntity
	 *
	 * @return EmailEntity|null
	 */
	public function getAccountAwaitingConfirmationEmailEntity(UserEntity $userEntity)
	{

		if(empty($userEntity->Email) || !MchValidator::isEmail($userEntity->Email) || !$this->getOption(EmailsSettingsAdminModule::OPTION_ACCOUNT_AWAITING_EMAIL_CONFIRMATION_ENABLED)){
			return null;
		}

		$emailEntity = new EmailEntity($userEntity->Email, $this->getOption(EmailsSettingsAdminModule::OPTION_ACCOUNT_AWAITING_EMAIL_CONFIRMATION_SUBJECT));

		$emailEntity->EmailContent = $this->getOption(EmailsSettingsAdminModule::OPTION_ACCOUNT_AWAITING_EMAIL_CONFIRMATION_MESSAGE);

		MchUtils::isNullOrEmpty($this->getOption(EmailsSettingsAdminModule::OPTION_HTML_TEMPLATES_ENABLED)) ?: $emailEntity->EmailContent =  $this->getEmailContentByFileTemplate('welcome');

		return $emailEntity;
	}

	/**
	 * @param UserEntity $userEntity
	 *
	 * @return EmailEntity|null
	 */
	public function getAccountAwaitingReviewEmailEntity(UserEntity $userEntity)
	{
		if(empty($userEntity->Email) || !MchValidator::isEmail($userEntity->Email) || !$this->getOption(EmailsSettingsAdminModule::OPTION_ACCOUNT_AWAITING_REVIEW_ENABLED)){
			return null;
		}

		$emailEntity = new EmailEntity($userEntity->Email, $this->getOption(EmailsSettingsAdminModule::OPTION_ACCOUNT_AWAITING_REVIEW_SUBJECT));

		$emailEntity->EmailContent = $this->getOption(EmailsSettingsAdminModule::OPTION_ACCOUNT_AWAITING_REVIEW_MESSAGE);

		MchUtils::isNullOrEmpty($this->getOption(EmailsSettingsAdminModule::OPTION_HTML_TEMPLATES_ENABLED)) ?: $emailEntity->EmailContent =  $this->getEmailContentByFileTemplate('qqq');

		return $emailEntity;
	}


	/**
	 * @param UserEntity $userEntity
	 *
	 * @return EmailEntity|null
	 */
	public function getAccountApprovedEmailEntity(UserEntity $userEntity)
	{
		if(empty($userEntity->Email) || !MchValidator::isEmail($userEntity->Email) || !$this->getOption(EmailsSettingsAdminModule::OPTION_ACCOUNT_APPROVED_ENABLED)){
			return null;
		}

		$emailEntity = new EmailEntity($userEntity->Email, $this->getOption(EmailsSettingsAdminModule::OPTION_ACCOUNT_APPROVED_SUBJECT));

		$emailEntity->EmailContent = $this->getOption(EmailsSettingsAdminModule::OPTION_ACCOUNT_APPROVED_MESSAGE);

		MchUtils::isNullOrEmpty($this->getOption(EmailsSettingsAdminModule::OPTION_HTML_TEMPLATES_ENABLED)) ?: $emailEntity->EmailContent =  $this->getEmailContentByFileTemplate('qqq');

		return $emailEntity;
	}


	/**
	 * @param UserEntity $userEntity
	 *
	 * @return EmailEntity|null
	 */
	public function getAccountRejectedEmailEntity(UserEntity $userEntity)
	{
		if(empty($userEntity->Email) || !MchValidator::isEmail($userEntity->Email) || !$this->getOption(EmailsSettingsAdminModule::OPTION_ACCOUNT_REJECTED_ENABLED)){
			return null;
		}

		$emailEntity = new EmailEntity($userEntity->Email, $this->getOption(EmailsSettingsAdminModule::OPTION_ACCOUNT_REJECTED_SUBJECT));

		$emailEntity->EmailContent = $this->getOption(EmailsSettingsAdminModule::OPTION_ACCOUNT_REJECTED_MESSAGE);

		MchUtils::isNullOrEmpty($this->getOption(EmailsSettingsAdminModule::OPTION_HTML_TEMPLATES_ENABLED)) ?: $emailEntity->EmailContent =  $this->getEmailContentByFileTemplate('qqq');

		return $emailEntity;
	}

	/**
	 * @param UserEntity $userEntity
	 *
	 * @return EmailEntity|null
	 */
	public function getPasswordResetRequestEmailEntity(UserEntity $userEntity)
	{
		if(empty($userEntity->Email) || !MchValidator::isEmail($userEntity->Email) || !$this->getOption(EmailsSettingsAdminModule::OPTION_PASSWORD_RESET_REQUEST_ENABLED)){
			return null;
		}

		$emailEntity = new EmailEntity($userEntity->Email, $this->getOption(EmailsSettingsAdminModule::OPTION_PASSWORD_RESET_REQUEST_SUBJECT));

		$emailEntity->EmailContent = $this->getOption(EmailsSettingsAdminModule::OPTION_PASSWORD_RESET_REQUEST_MESSAGE);

		MchUtils::isNullOrEmpty($this->getOption(EmailsSettingsAdminModule::OPTION_HTML_TEMPLATES_ENABLED)) ?: $emailEntity->EmailContent =  $this->getEmailContentByFileTemplate('qqq');

		return $emailEntity;
	}

	/**
	 * @param UserEntity $userEntity
	 *
	 * @return EmailEntity|null
	 */
	public function getPasswordChangedEmailEntity(UserEntity $userEntity)
	{
		if(empty($userEntity->Email) || !MchValidator::isEmail($userEntity->Email) || !$this->getOption(EmailsSettingsAdminModule::OPTION_PASSWORD_CHANGED_ENABLED)){
			return null;
		}

		$emailEntity = new EmailEntity($userEntity->Email, $this->getOption(EmailsSettingsAdminModule::OPTION_PASSWORD_CHANGED_SUBJECT));

		$emailEntity->EmailContent = $this->getOption(EmailsSettingsAdminModule::OPTION_PASSWORD_CHANGED_MESSAGE);

		MchUtils::isNullOrEmpty($this->getOption(EmailsSettingsAdminModule::OPTION_HTML_TEMPLATES_ENABLED)) ?: $emailEntity->EmailContent =  $this->getEmailContentByFileTemplate('qqq');

		return $emailEntity;
	}

	private function getEmailContentByFileTemplate($fileTemplateName)
	{
		$templatesDirPath = MchBasePlugin::getPluginDirectoryPath() . '/engine/templates/emails/default/';

		$templatesDirPath = \apply_filters(UltraCommHooks::FILTER_EMAIL_TEMPLATES_DIR_PATH, $templatesDirPath);

		if(empty($templatesDirPath)){
			return null;
		}


		$templatesDirPath = trailingslashit($templatesDirPath);

		if(!@file_exists($layoutFile = $templatesDirPath  . 'email-base-layout.php')){
			return null;
		}

		if(!@file_exists($templateFile = $templatesDirPath  . basename($fileTemplateName) . '.php')){
			return null;
		}

		ob_start();

		include_once $layoutFile;

		$layoutFileContent =  ob_get_clean();

		ob_start();
			include_once $templateFile;

		$templateFileContent = ob_get_clean();

		return str_replace('{ultra-community-email-content}', $templateFileContent, $layoutFileContent);
	}

}