<?php
/**
 * Copyright (c) 2017 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Controllers;


use UltraCommunity\MchLib\Tasks\MchWpTask;
use UltraCommunity\MchLib\Tasks\MchWpTaskScheduler;

class TasksController
{
	//private static $arrRegisteredTasks = array();
	
	public static function registerTask(MchWpTask $wpTask)
	{
		MchWpTaskScheduler::getInstance()->registerTask($wpTask);
	}
	
	public static function hasRegisteredTasks()
	{
		return MchWpTaskScheduler::getInstance()->hasRegisteredTasks();
	}

//	public static function scheduleRegisteredTasks()
//	{
		
//		foreach(self::getGdbcTasks() as $gdbcTask)
//		{
//			MchWpTaskScheduler::getInstance()->registerTask($gdbcTask);
//		}
		
//		MchWpTaskScheduler::getInstance()->scheduleRegisteredTasks();
//	}
	
//	public static function unScheduleGdbcTasks()
//	{
//		foreach(self::getGdbcTasks() as $gdbcTask)
//		{
//			MchWpTaskScheduler::getInstance()->registerTask($gdbcTask);
//			MchWpTaskScheduler::getInstance()->unScheduleRegisteredTask($gdbcTask);
//		}
//	}
	
	private function __construct(){}
	private function __clone(){}
}