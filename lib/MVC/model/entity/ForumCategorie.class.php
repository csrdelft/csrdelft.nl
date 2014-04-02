<?php

/**
 * ForumCategorie.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een forum categorie bevat deelfora.
 * 
 */
class ForumCategorie extends PersistentEntity {

	/**
	 * Primary key
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
	 * Rechten benodigd voor bekijken
	 * @var string
	 */
	public $rechten_lezen;
	/**
	 * Weergave volgorde
	 * @var int
	 */
	public $volgorde;
	/**
	 * Forumdelen
	 * @var ForumDeel[]
	 */
	private $forum_delen;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'categorie_id' => 'int(11) NOT NULL AUTO_INCREMENT',
		'titel' => 'varchar(255) NOT NULL',
		'omschrijving' => 'text NOT NULL',
		'rechten_lezen' => 'varchar(255) NOT NULL',
		'volgorde' => 'int(11) NOT NULL'
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('categorie_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'forum_categorien';

	public function magLezen() {
		return LoginLid::mag($this->rechten_lezen);
	}

	/**
	 * Lazy loading by foreign key.
	 * 
	 * @return ForumDeel[]
	 */
	public function getForumDelen() {
		if (!isset($this->forum_delen)) {
			$this->setForumDelen(ForumDelenModel::instance()->getForumDelenVoorCategorie($this->categorie_id));
		}
		return $this->forum_delen;
	}

	public function hasForumDelen() {
		return sizeof($this->getForumDelen()) > 0;
	}

	public function setForumDelen(array $forum_delen) {
		$this->forum_delen = $forum_delen;
	}

}
