<?php

namespace CsrDelft\model\entity\forum;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * ForumDeelMelding.class.php
 * Leden kunnen meldingen krijgen voor een forumdeel
 */
class ForumDeelMelding extends PersistentEntity {
	/**
	 * Shared primary key
	 * Foreign key
	 * @var int
	 */
	public $forum_id;

	/**
	 * Lidnummer
	 * Shared primary key
	 * Foreign key
	 * @var string
	 */
	public $uid;

	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'forum_id' => array(T::Integer),
		'uid' => array(T::UID)
	);

	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('forum_id', 'uid');

	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'forum_delen_meldingen';

}
