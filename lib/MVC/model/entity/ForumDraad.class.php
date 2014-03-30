<?php

/**
 * ForumDraad.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een ForumDraad zit in een deelforum en bevat forumposts.
 * 
 */
class ForumDraad extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $draad_id;
	/**
	 * Forum waaronder dit topic valt
	 * @var int
	 */
	public $forum_id;
	/**
	 * Uid van auteur
	 * @var string
	 */
	public $lid_id;
	/**
	 * Titel
	 * @var string
	 */
	public $titel;
	/**
	 * Datum en tijd van aanmaken
	 * @var string
	 */
	public $datum_tijd;
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
	 * Aantal zichtbare posts in dit topic
	 * @var int
	 */
	public $aantal_posts;
	/**
	 * Gesloten (posten niet meer mogelijk)
	 * @var boolean
	 */
	public $gesloten;
	/**
	 * Verwijderd
	 * @var boolean
	 */
	public $verwijderd;
	/**
	 * Wacht op goedkeuring
	 * @var boolean
	 */
	public $wacht_goedkeuring;
	/**
	 * Plakkerig (altijd bovenaan weergeven)
	 * @var boolean
	 */
	public $plakkerig;
	/**
	 * Belangrijk markering
	 * @var boolean
	 */
	public $belangrijk;
	/**
	 * Forumposts
	 * @var ForumPost[]
	 */
	private $forum_posts;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'draad_id' => 'int(11) NOT NULL AUTO_INCREMENT',
		'forum_id' => 'int(11) NOT NULL',
		'lid_id' => 'varchar(4) NOT NULL',
		'titel' => 'varchar(255) NOT NULL',
		'datum_tijd' => 'datetime NOT NULL',
		'laatst_gepost' => 'datetime NOT NULL',
		'laatste_post_id' => 'int(11) NOT NULL',
		'laatste_lid_id' => 'varchar(4) NOT NULL',
		'aantal_posts' => 'int(11) NOT NULL',
		'gesloten' => 'boolean NOT NULL',
		'verwijderd' => 'boolean NOT NULL',
		'wacht_goedkeuring' => 'boolean NOT NULL',
		'plakkerig' => 'boolean NOT NULL',
		'belangrijk' => 'boolean NOT NULL'
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('draad_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'forum_draden';

	/**
	 * Lazy loading by foreign key.
	 * 
	 * @return ForumPost[]
	 */
	public function getForumPosts() {
		if (!isset($this->forum_posts)) {
			$this->setForumPosts(ForumPostsModel::instance()->getForumPostsVoorDraad($this->draad_id));
		}
		return $this->forum_posts;
	}

	public function hasForumPosts() {
		return sizeof($this->getForumPosts()) > 0;
	}

	public function setForumPosts(array $forum_posts) {
		$this->forum_posts = $forum_posts;
	}

}
