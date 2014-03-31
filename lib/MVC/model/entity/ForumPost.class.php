<?php

/**
 * ForumPost.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een forumpost zit in een ForumDraad.
 * 
 */
class ForumPost extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $post_id;
	/**
	 * Deze post is van dit draadje
	 * @var int
	 */
	public $draad_id;
	/**
	 * Uid van auteur
	 * @var string
	 */
	public $lid_id;
	/**
	 * Tekst
	 * @var string
	 */
	public $tekst;
	/**
	 * Datum en tijd van aanmaken
	 * @var string
	 */
	public $datum_tijd;
	/**
	 * Datum en tijd van laatste bewerking
	 * @var string
	 */
	public $laatst_bewerkt;
	/**
	 * Bewerking logboek
	 * @var string
	 */
	public $bewerkt_tekst;
	/**
	 * Verwijderd
	 * @var boolean
	 */
	public $verwijderd;
	/**
	 * IP adres van de auteur
	 * @var string
	 */
	public $auteur_ip;
	/**
	 * Wacht op goedkeuring
	 * @var boolean
	 */
	public $wacht_goedkeuring;
	/**
	 * Reden van wegfilteren
	 * @var string
	 */
	public $gefilterd;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'post_id' => 'int(11) NOT NULL AUTO_INCREMENT',
		'draad_id' => 'int(11) NOT NULL',
		'lid_id' => 'varchar(4) NOT NULL',
		'tekst' => 'text NOT NULL',
		'datum_tijd' => 'datetime NOT NULL',
		'laatst_bewerkt' => 'datetime DEFAULT NULL',
		'bewerkt_tekst' => 'text DEFAULT NULL',
		'verwijderd' => 'boolean NOT NULL',
		'auteur_ip' => 'varchar(255) NOT NULL',
		'wacht_goedkeuring' => 'boolean NOT NULL'
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
