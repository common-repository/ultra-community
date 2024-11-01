<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */
namespace UltraCommunity\MchLib\Tasks;

class MchWpTaskScheduler
{

	CONST INTERVAL_WEEKLY  =  604800;
	CONST INTERVAL_MONTHLY = 2635200;
	CONST INTERVAL_DAILY = 86400;
	CONST INTERVAL_HOURLY = 3600;
	CONST INTERVAL_TWICE_HOURLY = 1800;
	CONST INTERVAL_TWICE_DAILY = 43200;
	
	private $arrTasks = null;
	private $arrDefaultRecurrences = null;

	protected function __construct()
	{
		$this->arrTasks = array();

		$this->arrDefaultRecurrences = wp_get_schedules();

		$this->arrDefaultRecurrences['weekly'] = array(
			'interval' => self::INTERVAL_WEEKLY,
			'display' => __('Once Weekly')
		);

		$this->arrDefaultRecurrences['monthly'] = array(
			'interval' => self::INTERVAL_MONTHLY,
			'display' => __('Once a month')
		);
		
		$this->arrDefaultRecurrences['daily'] = array(
			'interval' => self::INTERVAL_DAILY,
			'display' => __('Once a day')
		);
		
		$this->arrDefaultRecurrences['twicedaily'] = array(
			'interval' => self::INTERVAL_TWICE_DAILY,
			'display' => __('Twice Daily')
		);
		
		$this->arrDefaultRecurrences['hourly'] = array(
			'interval' => self::INTERVAL_HOURLY,
			'display' => __('Once Hourly')
		);
		
		$this->arrDefaultRecurrences['twicehourly'] = array(
			'interval' => self::INTERVAL_TWICE_HOURLY,
			'display' => __('Twice Hourly')
		);
		
		
		add_filter('cron_schedules', array($this, 'generateCustomCronSchedules'), 10);
		
		
		add_action('MchWpTaskScheduler', function ($mchWpTask){
			(!$mchWpTask instanceof MchWpTask) ?: $mchWpTask->run();
		});
		
	}

	public function registerTask(MchWpTask $mchTask)
	{
		$this->arrTasks[] = $mchTask;
		//add_action($mchTask->getTaskCronActionHookName(), array($mchTask, 'run'));
	}
	public function hasRegisteredTasks()
	{
		return isset($this->arrTasks[0]);
	}
	
	public function scheduleRegisteredTasks()
	{
		foreach($this->arrTasks as $mchTask)
		{
			if(false !== wp_next_scheduled('MchWpTaskScheduler', array($mchTask))) {
				continue;
			}
			
			$mchTask->isRecurringTask() ? wp_schedule_event( time(), $this->getFormattedRecurrence($mchTask->getRunningInterval()), 'MchWpTaskScheduler', array($mchTask) )
										: wp_schedule_single_event(  $mchTask->getRunningInterval(), 'MchWpTaskScheduler' , array($mchTask) );
			
		}
	}


	public function unScheduleRegisteredTask(MchWpTask $mchTask)
	{
		wp_clear_scheduled_hook( 'MchWpTaskScheduler', array($mchTask) );
	}

	public function unScheduleRegisteredTasks()
	{
		foreach($this->arrTasks as $mchTask)
		{
			$timestamp = wp_next_scheduled($mchTask->getTaskCronActionHookName());
			(false !== $timestamp) ? wp_unschedule_event($timestamp, $mchTask->getTaskCronActionHookName()) : null;
		}

		$this->arrTasks = array();
	}


	public function generateCustomCronSchedules($arrCronSchedules)
	{

		$arrCronSchedules = array_merge($arrCronSchedules, $this->arrDefaultRecurrences);
		foreach($this->arrTasks as $mchTask)
		{
			if(!$mchTask->isRecurringTask() || isset($this->arrDefaultRecurrences[$this->getFormattedRecurrence($mchTask->getRunningInterval())]))
				continue;

			$arrCronSchedules[$this->getFormattedRecurrence($mchTask->getRunningInterval())] = array(
				'interval' => $mchTask->getRunningInterval(),
				'display'  => sprintf( _n('Every %d second', 'Every %d seconds', $mchTask->getRunningInterval(), 'ultra-community'), $mchTask->getRunningInterval() )
			);
		}

		
//		print_r($arrCronSchedules);exit();
		
		return $arrCronSchedules;
	}


	private function getFormattedRecurrence($interval)
	{
		static $arrFormattedRecurrence = array();
		if(isset($arrFormattedRecurrence[$interval]))
			return $arrFormattedRecurrence[$interval];

		foreach($this->arrDefaultRecurrences as $recurrence => $arrRecurrence)
			if(isset($arrRecurrence['interval']) &&((int)$arrRecurrence['interval'] === (int)$interval))
				return $arrFormattedRecurrence[$interval] = $recurrence;

		return $arrFormattedRecurrence[$interval] = "mch-wp-$interval";
	}

	public static function getInstance()
	{
		static $taskSchedulerInstance = null;
		return null !== $taskSchedulerInstance ? $taskSchedulerInstance : $taskSchedulerInstance = new self();
	}
}

