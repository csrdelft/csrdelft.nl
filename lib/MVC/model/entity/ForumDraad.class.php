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
	 * Datum en tijd van laatst geplaatste of gewijzigde post
	 * @var string
	 */
	public $laatst_gewijzigd;
	/**
	 * Id van de laatst geplaatste of gewijzigde post
	 * @var string
	 */
	public $laatste_post_id;
	/**
	 * Uid van de auteur van de laatst geplaatste of gewijzigde post
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
	 * Altijd bovenaan weergeven
	 * @var boolean
	 */
	public $plakkerig;
	/**
	 * Belangrijk markering
	 * @var boolean
	 */
	public $belangrijk;
	/**
	 * Eerste post altijd bovenaan weergeven
	 * @var boolean
	 */
	public $eerste_post_plakkerig;
	/**
	 * Forumposts
	 * @var ForumPost[]
	 */
	private $forum_posts;
	/**
	 * Moment gelezen door gebruiker
	 * @var string
	 */
	private $wanneer_gelezen;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'draad_id' => array('int', 11, false, null, 'auto_increment'),
		'forum_id' => array('int', 11),
		'lid_id' => array('string', 4),
		'titel' => array('string', 255),
		'datum_tijd' => array('datetime'),
		'laatst_gewijzigd' => array('datetime', null, true),
		'laatste_post_id' => array('int', 11, true),
		'laatste_lid_id' => array('string', 4, true),
		'aantal_posts' => array('int', 11),
		'gesloten' => array('boolean'),
		'verwijderd' => array('boolean'),
		'wacht_goedkeuring' => array('boolean'),
		'plakkerig' => array('boolean'),
		'belangrijk' => array('boolean'),
		'eerste_post_plakkerig' => array('boolean')
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

	public function getWanneerGelezen() {
		if ($this->wanneer_gelezen === null) {
			return '0000-00-00 00:00:00';
		}
		return $this->wanneer_gelezen;
	}

	public function alGelezen() {
		if (strtotime($this->laatst_gewijzigd) <= strtotime($this->getWanneerGelezen())) {
			return true;
		}
		return false;
	}

	public function setWanneerGelezen(ForumDraadGelezen $gelezen) {
		$this->wanneer_gelezen = $gelezen->datum_tijd;
	}

	/**
	 * Lazy loading by foreign key.
	 * 
	 * @return ForumPost[]
	 */
	public function getForumPosts() {
		if (!isset($this->forum_posts)) {
			$this->setForumPosts(ForumPostsModel::instance()->getForumPostsVoorDraad($this));
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
