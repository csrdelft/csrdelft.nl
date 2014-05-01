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
	public $laatste_lid_id;
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
	protected static $persistent_fields = array(
		'forum_id' => array('int', 11, false, null, 'auto_increment'),
		'categorie_id' => array('int', 11),
		'titel' => array('string', 255),
		'omschrijving' => array('text'),
		'laatst_gewijzigd' => array('datetime', null, true),
		'laatste_post_id' => array('int', 11, true,),
		'laatste_lid_id' => array('string', 4, true),
		'aantal_draden' => array('int', 11),
		'aantal_posts' => array('int', 11),
		'rechten_lezen' => array('string', 255),
		'rechten_posten' => array('string', 255),
		'rechten_modereren' => array('string', 255),
		'volgorde' => array('int', 11)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_keys = array('forum_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'forum_delen';

	public function magLezen($rss = false) {
		return LoginLid::mag('P_FORUM_READ', $rss) AND LoginLid::mag($this->rechten_lezen, $rss);
	}

	public function magPosten() {
		return $this->magLezen() AND LoginLid::mag($this->rechten_posten);
	}

	public function magModereren() {
		return $this->magPosten() AND LoginLid::mag($this->rechten_modereren);
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
			$this->setForumDraden(ForumDradenModel::instance()->getForumDradenVoorDeel($this->forum_id));
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
