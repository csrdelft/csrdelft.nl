<?php

/**
 * ForumDeel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een deelforum zit in een forumcategorie bevat ForumDraden.
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
	 * Datum en tijd van laatst geplaatste of gewijzigde post
	 * @var string
	 */
	public $laatst_gewijzigd;
	/**
	 * Id van de laatst geplaatste of gewijzigde post
	 * @var int
	 */
	public $laatste_post_id;
	/**
	 * Uid van de auteur van de laatst geplaatste of gewijzigde post
	 * @var string
	 */
	public $laatste_wijziging_uid;
	/**
	 * Aantal draden in dit forum
	 * @var int
	 */
	public $aantal_draden;
	/**
	 * Aantal zichtbare posts in dit forum
	 * @var int
	 */
	public $aantal_posts;
	/**
	 * Rechten benodigd voor lezen
	 * @var string
	 */
	public $rechten_lezen;
	/**
	 * Rechten benodigd voor posten
	 * @var string
	 */
	public $rechten_posten;
	/**
	 * Rechten benodigd voor modereren
	 * @var string
	 */
	public $rechten_modereren;
	/**
	 * Weergave volgorde
	 * @var int
	 */
	public $volgorde;
	/**
	 * Forumdraden
	 * @var ForumDraad[]
	 */
	private $forum_draden;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'forum_id'				 => array(T::Integer, false, 'auto_increment'),
		'categorie_id'			 => array(T::Integer),
		'titel'					 => array(T::String),
		'omschrijving'			 => array(T::Text),
		'laatst_gewijzigd'		 => array(T::DateTime, true),
		'laatste_post_id'		 => array(T::Integer, true),
		'laatste_wijziging_uid'	 => array(T::UID, true),
		'aantal_draden'			 => array(T::Integer),
		'aantal_posts'			 => array(T::Integer),
		'rechten_lezen'			 => array(T::String),
		'rechten_posten'		 => array(T::String),
		'rechten_modereren'		 => array(T::String),
		'volgorde'				 => array(T::Integer)
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

	public function magLezen($rss = false) {
		return LoginModel::mag('P_FORUM_READ', $rss) AND LoginModel::mag($this->rechten_lezen, $rss);
	}

	public function magPosten() {
		return LoginModel::mag($this->rechten_posten);
	}

	public function magModereren() {
		return LoginModel::mag($this->rechten_modereren);
	}

	public function isOpenbaar() {
		return strpos($this->rechten_lezen, 'P_FORUM_READ') !== false;
	}

	/**
	 * Lazy loading by foreign key.
	 * 
	 * @return ForumDraad[]
	 */
	public function getForumDraden() {
		if (!isset($this->forum_draden)) {
			$this->setForumDraden(ForumDradenModel::instance()->getForumDradenVoorDeel($this));
		}
		return $this->forum_draden;
	}

	public function hasForumDraden() {
		return sizeof($this->getForumDraden()) > 0;
	}

	public function setForumDraden(array $forum_draden) {
		$this->forum_draden = $forum_draden;
	}

}
