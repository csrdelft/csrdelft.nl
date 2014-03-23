<?php

/**
 * ForumDeel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een deelforum zit in een forumcategorie bevat forumtopics.
 * 
 */
class ForumDeel extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $forum_id;
	/**
	 * Dit forum valt onder deze categorie
	 * @var int
	 */
	public $categorie_id;
	/**
	 * Titel
	 * @var string
	 */
	public $titel;
	/**
	 * Omschrijving
	 * @var string
	 */
	public $omschrijving;
	/**
	 * Datum en tijd van laatst geplaatste post
	 * @var string
	 */
	public $laatst_gepost;
	/**
	 * Id van de laatst geplaatste post
	 * @var string
	 */
	public $laatste_post_id;
	/**
	 * Uid van de auteur van de laatst geplaatste post
	 * @var string
	 */
	public $laatste_lid_id;
	/**
	 * Aantal topics in dit forum
	 * @var int
	 */
	public $aantal_topics = 0;
	/**
	 * Aantal zichtbare posts in dit forum
	 * @var int
	 */
	public $aantal_posts = 0;
	/**
	 * Rechten benodigd voor lezen
	 * @var string
	 */
	public $zichtbaar_voor = 'P_FORUM_READ';
	/**
	 * Rechten benodigd voor posten
	 * @var string
	 */
	public $schrijfrechten = 'P_FORUM_POST';
	/**
	 * Weergave volgorde
	 * @var int
	 */
	public $prioriteit;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'forum_id' => 'int(11) NOT NULL AUTO_INCREMENT',
		'categorie_id' => 'int(11) NOT NULL',
		'titel' => 'varchar(255) NOT NULL',
		'omschrijving' => 'text NOT NULL',
		'laatst_gepost' => 'datetime NOT NULL',
		'laatste_post_id' => 'int(11) NOT NULL',
		'laatste_lid_id' => 'varchar(4) NOT NULL',
		'aantal_topics' => 'int(11) NOT NULL',
		'aantal_posts' => 'int(11) NOT NULL',
		'zichtbaar_voor' => 'varchar(25) NOT NULL',
		'schrijfrechten' => 'varchar(25) NOT NULL',
		'prioriteit' => 'int(11) NOT NULL'
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('forum_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'forum_delen';

}
