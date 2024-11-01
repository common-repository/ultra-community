<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Entities;


use UltraCommunity\Modules\GeneralSettings\Emails\EmailsSettingsPublicModule;
use UltraCommunity\Modules\GeneralSettings\Emails\EmailsSettingsAdminModule;

class EmailEntity
{
	public $Subject = null;
	public $RecipientEmail = null;
	public $SenderEmail = null;
	public $SenderName = null;
	public $EmailContent = null;

	public $HasHtmlContent = true;

	public function __construct($recipientEmail, $subject, $emailContent = null, $senderName = null, $senderEmail = null)
	{
		$this->RecipientEmail = $recipientEmail;
		$this->Subject = $subject;
		$this->EmailContent = $emailContent;

		$this->SenderName  = ( !empty($senderName)  ) ? $senderName  : EmailsSettingsPublicModule::getInstance()->getOption(EmailsSettingsAdminModule::OPTION_SENDER_NAME);
		$this->SenderEmail = ( !empty($senderEmail) ) ? $senderEmail : EmailsSettingsPublicModule::getInstance()->getOption(EmailsSettingsAdminModule::OPTION_SENDER_EMAIL_ADDRESS);

	}

}