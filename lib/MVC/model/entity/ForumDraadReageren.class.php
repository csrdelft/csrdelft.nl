<?php

/**
 * ForumDraadReageren.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Concept berichten opslaan per draadje.
 * Bijhouden als iemand bezig is een reactie te schrijven.
 * 
 */
class ForumDraadReageren extends PersistentEntity {

	/**
	 * Shared primary key
	 * @var int
	 */
	public $draad_id;
	/**
	 * Shared primary key
	 * @var string
	 */
	public $uid;
	/**
	 * Datum en tijd van start reageren
	 * @var string
	 */
	public $datum_tijd;
	/**
	 * Opgeslagen concept bericht
	 * @var string
	 */
	public $concept;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'draad_id'	 => array(T::Integer),
		'uid'		 => array(T::UID),
		'datum_tijd' => array(T::DateTime),
		'concept'	 => array(T::Text, true)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('draad_id', 'uid');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'forum_draden_reageren';

}
