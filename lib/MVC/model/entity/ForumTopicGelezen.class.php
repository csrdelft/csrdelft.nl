<?php

/**
 * ForumTopicGelezen.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een forumtopic kan worden gelezen door een lid op een bepaald moment.
 * 
 */
class ForumTopicGelezen extends PersistentEntity {

	/**
	 * Shared primary key
	 * @var int
	 */
	public $topic_id;
	/**
	 * Shared primary key
	 * @var string
	 */
	public $lid_id;
	/**
	 * Datum en tijd van laatst gelezen
	 * @var string
	 */
	public $datum_tijd;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'topic_id' => 'int(11) NOT NULL',
		'lid_id' => 'varchar(4) NOT NULL',
		'datum_tijd' => 'datetime NOT NULL'
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('topic_id', 'lid_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'forum_topic_gelezen';

}
