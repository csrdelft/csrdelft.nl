<?php

require_once 'model/entity/groepen/Commissie.class.php';

/**
 * Bestuur.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een bestuur is een speciaal type van een commissie.
 * 
 */
class Bestuur extends Commissie {

	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'besturen';

}
