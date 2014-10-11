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
	 * Forum waarmee dit topic gedeeld is
	 * @var int
	 */
	public $gedeeld_met;
	/**
	 * Uid van auteur
	 * @var string
	 */
	public $uid;
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
	public $laatste_wijziging_uid;
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
	 * @var ForumDraadGelezen
	 */
	private $wanneer_gelezen;
	/**
	 * Lijst van lezers (wanneer)
	 * @var ForumDraadGelezen[]
	 */
	private $lezers;
	/**
	 * Aantal lezers
	 * @var int
	 */
	private $aantal_lezers;
	/**
	 * Verbergen voor gebruiker
	 * @var boolean
	 */
	private $verbergen;
	/**
	 * Volgen door gebruiker
	 * @var boolean
	 */
	private $volgen;
	/**
	 * Volgers
	 * @var array
	 */
	private $volgers;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'draad_id'				 => array(T::Integer, false, 'auto_increment'),
		'forum_id'				 => array(T::Integer),
		'gedeeld_met'			 => array(T::Integer, true),
		'uid'					 => array(T::UID),
		'titel'					 => array(T::String),
		'datum_tijd'			 => array(T::DateTime),
		'laatst_gewijzigd'		 => array(T::DateTime, true),
		'laatste_post_id'		 => array(T::Integer, true),
		'laatste_wijziging_uid'	 => array(T::UID, true),
		'aantal_posts'			 => array(T::Integer),
		'gesloten'				 => array(T::Boolean),
		'verwijderd'			 => array(T::Boolean),
		'wacht_goedkeuring'		 => array(T::Boolean),
		'plakkerig'				 => array(T::Boolean),
		'belangrijk'			 => array(T::Boolean),
		'eerste_post_plakkerig'	 => array(T::Boolean)
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

	public function magVerbergen() {
		return !$this->belangrijk AND LoginModel::mag('P_LOGGED_IN');
	}

	public function isVerborgen() {
		if (!isset($this->verbergen)) {
			$this->verbergen = ForumDradenVerbergenModel::instance()->getVerbergenVoorLid($this);
		}
		return $this->verbergen;
	}

	public function magVolgen() {
		return LoginModel::mag('P_LOGGED_IN');
	}

	public function isGevolgd() {
		if (!isset($this->volgen)) {
			$this->volgen = ForumDradenVolgenModel::instance()->getVolgenVoorLid($this);
		}
		return $this->volgen;
	}

	public function getVolgers() {
		if (!isset($this->volgers)) {
			$this->volgers = ForumDradenVolgenModel::instance()->getVolgersVanDraad($this);
		}
		return $this->volgers;
	}

	public function getLezers() {
		if (!isset($this->lezers)) {
			$this->lezers = ForumDradenGelezenModel::instance()->getLezersVanDraad($this);
		}
		return $this->lezers;
	}

	public function getAantalLezers() {
		if (!isset($this->aantal_lezers)) {
			$this->aantal_lezers = count($this->getLezers());
		}
		return $this->aantal_lezers;
	}

	/**
	 * FALSE if ongelezen!
	 * 
	 * @return ForumDraadGelezen|boolean $gelezen
	 */
	public function getWanneerGelezen() {
		if (!isset($this->wanneer_gelezen)) {
			$this->setWanneerGelezen(ForumDradenGelezenModel::instance()->getWanneerGelezenDoorLid($this));
		}
		return $this->wanneer_gelezen;
	}

	public function onGelezen() {
		if ($this->getWanneerGelezen() AND strtotime($this->laatst_gewijzigd) > strtotime($this->wanneer_gelezen->datum_tijd)) {
			return true;
		}
		return false;
	}

	private function setWanneerGelezen($gelezen) {
		$this->wanneer_gelezen = $gelezen;
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

	/**
	 * Public for search results and all sorts of prefetching.
	 * 
	 * @param array $forum_posts
	 */
	public function setForumPosts(array $forum_posts) {
		$this->forum_posts = $forum_posts;
	}

}
