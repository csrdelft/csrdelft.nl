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
		'forum_id' => 'int(11) NOT NULL AUTO_INCREMENT',
		'categorie_id' => 'int(11) NOT NULL',
		'titel' => 'varchar(255) NOT NULL',
		'omschrijving' => 'text NOT NULL',
		'laatst_gewijzigd' => 'datetime DEFAULT NULL',
		'laatste_post_id' => 'int(11) DEFAULT NULL',
		'laatste_lid_id' => 'varchar(4) DEFAULT NULL',
		'aantal_draden' => 'int(11) NOT NULL',
		'aantal_posts' => 'int(11) NOT NULL',
		'rechten_lezen' => 'varchar(255) NOT NULL',
		'rechten_posten' => 'varchar(255) NOT NULL',
		'rechten_modereren' => 'varchar(255) NOT NULL',
		'volgorde' => 'int(11) NOT NULL'
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
		return LoginLid::instance()->hasPermission('P_FORUM_READ', $rss) AND LoginLid::instance()->hasPermission($this->rechten_lezen, $rss);
	}

	public function magPosten() {
		return $this->magLezen() AND LoginLid::instance()->hasPermission($this->rechten_posten);
	}

	public function magModereren() {
		return $this->magPosten() AND LoginLid::instance()->hasPermission($this->rechten_modereren);
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
