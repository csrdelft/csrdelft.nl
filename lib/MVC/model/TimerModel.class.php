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

	public function log($including_view = false) {
		$time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
		$measurement = $this->retrieveByPrimaryKey(array(REQUEST_URI));
		if ($measurement) {
			if ($including_view) {
				$measurement->total_time_view += $time;
			} else {
				$measurement->counter++;
				$measurement->total_time += $time;
			}
			$this->update($measurement);
		} else {
			$measurement = new ExecutionTime();
			$measurement->request = REQUEST_URI;
			$measurement->counter = 1;
			$measurement->total_time = $time;
			$measurement->total_time_view = 0;
			$this->create($measurement);
		}
	}

}
