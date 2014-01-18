<?php

/**
 * ForumTopic.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een forumtopic zit in een deelforum en bevat forumposts.
 * 
 */
class ForumTopic extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $topic_id = null;
	/**
	 * Forum waaronder dit topic valt
	 * @var int
	 */
	public $forum_id = null;
	/**
	 * Uid van auteur
	 * @var string
	 */
	public $lid_id = '';
	/**
	 * Titel
	 * @var string
	 */
	public $titel = '';
	/**
	 * Datum en tijd van aanmaken
	 * @var string
	 */
	public $datum_tijd = null;
	/**
	 * Datum en tijd van laatst geplaatste post
	 * @var string
	 */
	public $laatst_gepost = null;
	/**
	 * Id van de laatst geplaatste post
	 * @var string
	 */
	public $laatste_post_id = null;
	/**
	 * Uid van de auteur van de laatst geplaatste post
	 * @var string
	 */
	public $laatste_lid_id = null;
	/**
	 * Aantal zichtbare posts in dit topic
	 * @var int
	 */
	public $aantal_posts = 0;
	/**
	 * Zichtbaar, verwijderd of wacht op goedkeuring
	 * @var string
	 */
	public $status = 'zichtbaar';
	/**
	 * Open of gesloten
	 * @var boolean
	 */
	public $gesloten = false;
	/**
	 * Plakkerig of niet
	 * @var boolean
	 */
	public $sticky = false;
	/**
	 * Belangrijk markering of niet
	 * @var boolean
	 */
	public $belangrijk = false;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'topic_id' => 'int(11) NOT NULL AUTO_INCREMENT',
		'forum_id' => 'int(11) NOT NULL',
		'lid_id' => 'varchar(4) NOT NULL',
		'titel' => 'varchar(255) NOT NULL',
		'datum_tijd' => 'datetime NOT NULL',
		'laatst_gepost' => 'datetime NOT NULL',
		'laatste_post_id' => 'int(11) NOT NULL',
		'laatste_lid_id' => 'varchar(4) NOT NULL',
		'aantal_posts' => 'int(11) NOT NULL',
		'status' => 'varchar(25) NOT NULL',
		'gesloten' => 'tinyint(1) NOT NULL',
		'sticky' => 'tinyint(1) NOT NULL',
		'belangrijk' => 'tinyint(1) NOT NULL'
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('topic_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'forum_topics';

}
