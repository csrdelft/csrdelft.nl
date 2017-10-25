<?php

namespace CsrDelft\model\entity\forum;

use CsrDelft\model\entity\security\AuthenticationMethod;
use CsrDelft\model\forum\ForumDradenModel;
use CsrDelft\model\forum\ForumModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

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
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'forum_id' => array(T::Integer, false, 'auto_increment'),
		'categorie_id' => array(T::Integer),
		'titel' => array(T::String),
		'omschrijving' => array(T::Text),
		'rechten_lezen' => array(T::String),
		'rechten_posten' => array(T::String),
		'rechten_modereren' => array(T::String),
		'volgorde' => array(T::Integer)
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

	public function getForumCategorie() {
		return ForumModel::get($this->categorie_id);
	}

	public function magLezen($rss = false) {
		$auth = ($rss ? AuthenticationMethod::getTypeOptions() : null);
		return LoginModel::mag('P_FORUM_READ', $auth) AND LoginModel::mag($this->rechten_lezen, $auth) AND $this->getForumCategorie()->magLezen();
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
		$this->getForumDraden();
		return !empty($this->forum_draden);
	}

	/**
	 * Public for search results and all sorts of prefetching.
	 *
	 * @param array $forum_draden
	 */
	public function setForumDraden(array $forum_draden) {
		$this->forum_draden = $forum_draden;
	}

}
