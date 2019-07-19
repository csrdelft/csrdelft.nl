<?php

namespace CsrDelft\model\entity;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * ExecutionTime.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class ExecutionTime extends PersistentEntity {

	/**
	 * Request URI
	 * @var string
	 */
	public $request;
	/**
	 * Count measurements
	 * @var int
	 */
	public $counter;
	/**
	 * Total time
	 * @var int
	 */
	public $total_time;
	/**
	 * Total time including view
	 * @var int
	 */
	public $total_time_view;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'request' => array(T::String),
		'counter' => array(T::Integer),
		'total_time' => array(T::Float),
		'total_time_view' => array(T::Float)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('request');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'execution_times';

}
