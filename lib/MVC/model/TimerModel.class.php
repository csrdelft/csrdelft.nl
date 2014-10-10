<?php

/**
 * TimerModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class TimerModel extends PersistenceModel {

	const orm = 'ExecutionTime';

	protected static $instance;
	/**
	 * Log twice: before and after view
	 * @var float
	 */
	private $time_before_view;

	public function time() {
		$this->time_before_view = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
	}

	public function log() {
		$time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
		$measurement = $this->retrieveByPrimaryKey(array(REQUEST_URI));
		if ($measurement) {
			$measurement->counter++;
			$measurement->total_time += $this->time_before_view;
			$measurement->total_time_view += $time;
			$this->update($measurement);
		} else {
			$measurement = new ExecutionTime();
			$measurement->request = REQUEST_URI;
			$measurement->counter = 1;
			$measurement->total_time = $this->time_before_view;
			$measurement->total_time_view = $time;
			$this->create($measurement);
		}
	}

}
