<?php

namespace CsrDelft\model\entity\forum;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

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
	 * Foreign key
	 * @var int
	 */
	public $forum_id;
	/**
	 * Shared primary key
	 * Foreign key
	 * @var int
	 */
	public $draad_id;
	/**
	 * Lidnummer
	 * Shared primary key
	 * Foreign key
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
	 * Concept titel
	 * @var string
	 */
	public $titel;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'forum_id' => array(T::Integer),
		'draad_id' => array(T::Integer),
		'uid' => array(T::UID),
		'datum_tijd' => array(T::DateTime),
		'concept' => array(T::Text, true),
		'titel' => array(T::String, true)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('forum_id', 'draad_id', 'uid');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'forum_draden_reageren';

}
