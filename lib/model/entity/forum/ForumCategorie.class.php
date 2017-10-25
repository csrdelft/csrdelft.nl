<?php

namespace CsrDelft\model\entity\forum;

use CsrDelft\model\forum\ForumDelenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

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
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'categorie_id' => array(T::Integer, false, 'auto_increment'),
		'titel' => array(T::String),
		'rechten_lezen' => array(T::String),
		'volgorde' => array(T::Integer)
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
		return LoginModel::mag($this->rechten_lezen);
	}

	/**
	 * Lazy loading by foreign key.
	 *
	 * @return ForumDeel[]
	 */
	public function getForumDelen() {
		if (!isset($this->forum_delen)) {
			$this->setForumDelen(ForumDelenModel::instance()->getForumDelenVoorCategorie($this));
		}
		return $this->forum_delen;
	}

	public function hasForumDelen() {
		$this->getForumDelen();
		return !empty($this->forum_delen);
	}

	/**
	 * Public for search results and all sorts of prefetching.
	 *
	 * @param array $forum_delen
	 */
	public function setForumDelen(array $forum_delen) {
		$this->forum_delen = $forum_delen;
	}

}
