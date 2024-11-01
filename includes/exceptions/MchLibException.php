<?php
namespace UltraCommunity\MchLib\Exceptions;

class MchLibException extends \Exception
{
	public function __construct($errMessage, $flag = 0)
	{
		parent::__construct($errMessage, $flag);
	}
}