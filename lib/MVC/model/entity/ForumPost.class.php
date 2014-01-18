<?php

/**
 * ForumPost.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een menu-item instantie beschrijft een menu onderdeel van een menu-boom
 * en heeft daarom een parent.
 * 
 */
class ForumPost extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $post_id = null;
	/**
	 * Deze post is van dit draadje
	 * @var int
	 */
	public $topic_id = 0;
	/**
	 * Uid van lid
	 * @var string
	 */
	public $lid_id = 0;
	/**
	 * Tekst
	 * @var string
	 */
	public $tekst = '';
	/**
	 * Datum en tijd van aanmaken
	 * @var string
	 */
	public $datum_tijd = null;
	/**
	 * Datum en tijd van laatste bewerking
	 * @var string
	 */
	public $laatst_bewerkt = null;
	/**
	 * Zichtbaar, verwijderd of wacht op goedkeuring
	 * @var string
	 */
	public $status = status;
	/**
	 * IP adres van de auteur
	 * @var string
	 */
	public $auteur_ip = null;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'post_id' => 'int(11) NOT NULL AUTO_INCREMENT',
		'topic_id' => 'int(11) NOT NULL',
		'lid_id' => 'varchar(4) NOT NULL',
		'tekst' => 'text NOT NULL',
		'datum_tijd' => 'varchar(255) NOT NULL',
		'laatst_bewerkt' => 'varchar(255) NOT NULL',
		'status' => 'tinyint(1) NOT NULL',
		'auteur_ip' => 'varchar(255) NOT NULL'
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('post_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'forum_posts';

}
