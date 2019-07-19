<?php

namespace CsrDelft\model;

use CsrDelft\model\entity\ExecutionTime;
use CsrDelft\Orm\PersistenceModel;

/**
 * TimerModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class TimerModel extends PersistenceModel {

	const ORM = ExecutionTime::class;

	/**
	 * Time before and after view
	 * @var float
	 */
	private $time_before_view = 0;

	public function time() {
		$this->time_before_view = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
	}

	public function log() {
		$time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
		$req = substr(REQUEST_URI, 0, 255);
		/** @var ExecutionTime $measurement */
		$measurement = $this->retrieveByPrimaryKey(array($req));
		if ($measurement) {
			$measurement->counter++;
			$measurement->total_time += $this->time_before_view;
			$measurement->total_time_view += $time - $this->time_before_view;
			$this->update($measurement);
		} else {
			$measurement = new ExecutionTime();
			$measurement->request = $req;
			$measurement->counter = 1;
			$measurement->total_time = $this->time_before_view;
			$measurement->total_time_view = $time - $this->time_before_view;
			$this->create($measurement);
		}
	}

}
