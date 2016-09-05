<?php

/**
 * BijbelroosterModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class BijbelroosterModel extends PersistenceModel {

	const ORM = 'Bijbelrooster';

	protected static $instance;

	/**
	 * Haalt het bijbelrooster op tussen de opgegeven data.
	 * 
	 * @param int $van Timestamp
	 * @param int $tot Timestamp
	 * @return Bijbelrooster[]|Bijbelrooster (implements Agendeerbaar)
	 */
	public function getBijbelroosterTussen($van, $tot) {
		return $this->find('dag >= ? AND dag <= ?', array(date('Y-m-d', $van), date('Y-m-d', $tot)));
	}

}
