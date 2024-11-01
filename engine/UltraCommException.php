<?php
namespace UltraCommunity;

use UltraCommunity\MchLib\Exceptions\MchLibException;

class UltraCommException extends MchLibException
{
	public function __construct($errMessage, $exceptionCode = 0)
	{
		parent::__construct($errMessage, $exceptionCode);
	}

}