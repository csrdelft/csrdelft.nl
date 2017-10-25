<?php

namespace CsrDelft\model\entity\forum;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * ForumDraadVolgen.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een ForumDraad kan worden gevolgd door een lid.
 *
 */
class ForumDraadVolgen extends PersistentEntity {

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
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'draad_id' => array(T::Integer),
		'uid' => array(T::UID)
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
