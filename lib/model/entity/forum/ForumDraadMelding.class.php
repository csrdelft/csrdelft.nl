<?php

namespace CsrDelft\model\entity\forum;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * ForumDraadMelding.class.php
 * Leden kunnen meldingen krijgen voor een forumdraad
 */
class ForumDraadMelding extends PersistentEntity {

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
	 * Volgniveau
	 * @var string
	 */
	public $niveau = 'altijd';
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'draad_id' => array(T::Integer),
		'uid' => array(T::UID),
		'niveau' => array(T::Enumeration, false, ForumDraadMeldingNiveau::class)
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
	protected static $table_name = 'forum_draden_volgen';

}
