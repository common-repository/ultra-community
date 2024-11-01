<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Controllers;

use UltraCommunity\Entities\EmailEntity;
use UltraCommunity\Entities\UserEntity;
use UltraCommunity\Entities\UserMetaEntity;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Modules\GeneralSettings\Emails\EmailsSettingsPublicModule;
use UltraCommunity\Modules\GeneralSettings\FrontPage\FrontPageSettingsPublicModule;
use UltraCommunity\Repository\UserRepository;
use UltraCommunity\UltraCommHelper;
use UltraCommunity\UltraCommHooks;

class NotificationsController
{
	CONST NOTIFICATION_EMAIL_WELCOME_NEW_USER                    = 1;

	CONST NOTIFICATION_EMAIL_ACCOUNT_APPROVED                    = 2;
	CONST NOTIFICATION_EMAIL_ACCOUNT_REJECTED                    = 3;
	CONST NOTIFICATION_EMAIL_ACCOUNT_AWAITING_REVIEW             = 4;
	CONST NOTIFICATION_EMAIL_ACCOUNT_AWAITING_EMAIL_CONFIRMATION = 5;

	CONST NOTIFICATION_EMAIL_PASSWORD_CHANGE_REQUEST             = 6;
	CONST NOTIFICATION_EMAIL_PASSWORD_CHANGED                    = 7;

	CONST NOTIFICATION_EMAIL_USER_ACTIVITY_MENTION               = 20;

	/**
	 * @var EmailEntity
	 */
	private static $emailEntity = null;

	public static function sendNotification($notificationType, UserEntity $userEntity = null)
	{
		self::$emailEntity = null;

		switch($notificationType)
		{
			case self::NOTIFICATION_EMAIL_WELCOME_NEW_USER :
				self::$emailEntity = EmailsSettingsPublicModule::getInstance()->getWelcomeEmailEntity($userEntity);
				break;

			case self::NOTIFICATION_EMAIL_ACCOUNT_APPROVED :
				self::$emailEntity = EmailsSettingsPublicModule::getInstance()->getAccountApprovedEmailEntity($userEntity);
				break;

			case self::NOTIFICATION_EMAIL_ACCOUNT_REJECTED :
				self::$emailEntity = EmailsSettingsPublicModule::getInstance()->getAccountRejectedEmailEntity($userEntity);
				break;

			case self::NOTIFICATION_EMAIL_ACCOUNT_AWAITING_REVIEW :
				self::$emailEntity = EmailsSettingsPublicModule::getInstance()->getAccountAwaitingReviewEmailEntity($userEntity);
				break;

			case self::NOTIFICATION_EMAIL_ACCOUNT_AWAITING_EMAIL_CONFIRMATION :
				self::$emailEntity = EmailsSettingsPublicModule::getInstance()->getAccountAwaitingConfirmationEmailEntity($userEntity);
				break;

			case self::NOTIFICATION_EMAIL_PASSWORD_CHANGE_REQUEST :
				self::$emailEntity = EmailsSettingsPublicModule::getInstance()->getPasswordResetRequestEmailEntity($userEntity);
				break;

			case self::NOTIFICATION_EMAIL_PASSWORD_CHANGED :
				self::$emailEntity = EmailsSettingsPublicModule::getInstance()->getPasswordChangedEmailEntity($userEntity);
				break;

			default: break;
		}


		self::$emailEntity = apply_filters( UltraCommHooks::FILTER_EMAIL_NOTIFICATION_ENTITY , self::$emailEntity, $notificationType, $userEntity );


		if(self::$emailEntity instanceof EmailEntity){
			self::sendEmail($userEntity);
		}

		self::$emailEntity = null;
	}

	public static function sendEmail(UserEntity $userEntity = null)
	{
		if(empty(self::$emailEntity)){
			return;
		}

		!empty($userEntity) ?: $userEntity = new UserEntity();

		if(empty($userEntity->UserMetaEntity)){
			$userEntity = UserRepository::getUserEntityBy($userEntity);
		}

		!empty($userEntity) ?: $userEntity = new UserEntity();
		!empty($userEntity->UserMetaEntity) ?: $userEntity->UserMetaEntity = new UserMetaEntity();


		$arrCustomTags = array(
				'{site_url}'             => home_url( '/' ),
				'{site_name}'            => get_bloginfo( 'name' ),
				'{admin_name}'           => MchWpUtils::getAdminDisplayName(),
				'{admin_email}'          => get_bloginfo( 'admin_email' ),
				'{display_name}'         => $userEntity->DisplayName,
				'{user_display_name}'    => $userEntity->DisplayName,
				'{first_name}'           => $userEntity->UserMetaEntity->FirstName,
				'{last_name}'            => $userEntity->UserMetaEntity->LastName,
				'{username}'             => $userEntity->UserName,
				'{user_email}'           => $userEntity->Email,
				'{login_url}'            => FrontPageSettingsPublicModule::getLoginPageUrl(),
				'{activate_account_url}' => UltraCommHelper::getUserAccountConfirmationUrl($userEntity->Id),
				'{password_reset_url}'   => UltraCommHelper::getUserResetPasswordUrl($userEntity->Id),
				'{user_profile_url}'     => UltraCommHelper::getUserProfileUrl( $userEntity ),
		);

		self::$emailEntity->EmailContent = wp_kses_decode_entities(str_replace(array_keys($arrCustomTags), array_values($arrCustomTags), self::$emailEntity->EmailContent));
		self::$emailEntity->Subject      = wp_kses_decode_entities(str_replace(array_keys($arrCustomTags), array_values($arrCustomTags), self::$emailEntity->Subject));

		self::$emailEntity->EmailContent = make_clickable(wpautop(self::$emailEntity->EmailContent));

		$emailHeaders = array();
		$emailHeaders[] = 'Content-Type: text/html; charset=UTF-8';

		$emailEntity = &self::$emailEntity;
		MchWpUtils::addFilterHook('wp_mail_from', function ($originalFrom) use($emailEntity){
			return empty($emailEntity->SenderEmail) ? $originalFrom : $emailEntity->SenderEmail;
		}, PHP_INT_MAX);

		$emailEntity = &self::$emailEntity;
		MchWpUtils::addFilterHook('wp_mail_from_name', function ($originalFromName) use($emailEntity){
			return empty($emailEntity->SenderName) ? $originalFromName : $emailEntity->SenderName;
		}, PHP_INT_MAX);

		MchWpUtils::addFilterHook('wp_mail_content_type', function ($originalContentType) use($emailEntity){
			return empty($emailEntity) ? $originalContentType : 'text/html';
		}, PHP_INT_MAX);


		if( ! function_exists('wp_mail') ) {
			require_once( ABSPATH . 'wp-includes/pluggable.php' );
		}

		@wp_mail(self::$emailEntity->RecipientEmail, self::$emailEntity->Subject, self::$emailEntity->EmailContent);

		self::$emailEntity = null;
	}


}