<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\MchLib\Tasks;

use UltraCommunity\MchLib\Utils\MchUtils;

interface MchWpITask
{
	public function run();
	public function getTaskCronActionHookName();
}

abstract class MchWpTask implements MchWpITask
{
	private $isRecurringTask = false;
	private $runningInterval = null;
	private $arrParameters   = null;
	
	public function __construct($runningInterval, $isRecurring)
	{
		$this->runningInterval = (int)$runningInterval;
		$this->isRecurringTask = (bool)$isRecurring;
	}

	public function setTaskParameters(array $arrParams)
	{
		$this->arrParameters = $arrParams;
	}
	
	public function isRecurringTask()
	{
		return $this->isRecurringTask;
	}

	public function getRunningInterval()
	{
		return $this->runningInterval;
	}
	
	public function getTaskCronActionHookName()
	{
		return get_class($this) . '-' . $this->runningInterval . '-' . var_export($this->isRecurringTask, true);
	}
	
//	public function __sleep()
//	{
//		$arrObjectProperties = get_object_vars(MchUtils::filterObjectEmptyProperties($this));
//		unset(
//			$arrObjectProperties['isRecurringTask'],
//			$arrObjectProperties['runningInterval']
//		);
//
//		return array_keys($arrObjectProperties);
//
//	}

	
}
