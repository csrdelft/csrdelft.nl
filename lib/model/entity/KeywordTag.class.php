<?php

namespace CsrDelft\model\entity;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * KeywordTag.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class KeywordTag extends PersistentEntity {

	/**
	 * @see PersistentEntity Unique Universal Identifier
	 * @var string
	 */
	public $refuuid;
	/**
	 * Single keyword
	 * @var string
	 */
	public $keyword;
	/**
	 * Getagged door
	 * @var string
	 */
	public $door;
	/**
	 * Gemaakt op datum en tijd
	 * @var string
	 */
	public $wanneer;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'refuuid' => array(T::String),
		'keyword' => array(T::String),
		'door' => array(T::UID),
		'wanneer' => array(T::DateTime)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('refuuid', 'keyword');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'keyword_tags';

}
