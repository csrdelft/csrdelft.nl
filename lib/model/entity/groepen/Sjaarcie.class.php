<?php

require_once 'model/entity/groepen/Commissie.class.php';

/**
 * Sjaarcie.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een sjaarcie is een speciale commissie met alleen maar sjaars en een ouderejaars Q.Q.-er.
 * 
 */
class Sjaarcie extends Commissie {

	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'sjaarcies';

}
