<?php

require_once 'MCV/model/entity/Commissie.class.php';

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
	 * Type van groep
	 * @var string
	 */
	public static $class_name = 'Bestuur';

}
