<?php

namespace CsrDelft\model\entity\forum;

use CsrDelft\model\forum\ForumDelenModel;
use CsrDelft\model\forum\ForumDradenGelezenModel;
use CsrDelft\model\forum\ForumDradenVerbergenModel;
use CsrDelft\model\forum\ForumDradenMeldingModel;
use CsrDelft\model\forum\ForumPostsModel;
use CsrDelft\model\InstellingenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;
use CsrDelft\view\ChartTimeSeries;

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
	 * Lidnummer van auteur
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
	 * @var string
	 */
	public $belangrijk;
	/**
	 * Eerste post altijd bovenaan weergeven
	 * @var boolean
	 */
	public $eerste_post_plakkerig;
	/**
	 * Een post per pagina
	 * @var boolean
	 */
	public $pagina_per_post;
	/**
	 * Forumposts
	 * @var ForumPost[]
	 */
	private $forum_posts;
	/**
	 * Aantal ongelezen posts
	 * @var int
	 */
	private $aantal_ongelezen_posts;
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
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'draad_id' => array(T::Integer, false, 'auto_increment'),
		'forum_id' => array(T::Integer),
		'gedeeld_met' => array(T::Integer, true),
		'uid' => array(T::UID),
		'titel' => array(T::String),
		'datum_tijd' => array(T::DateTime),
		'laatst_gewijzigd' => array(T::DateTime, true),
		'laatste_post_id' => array(T::Integer, true),
		'laatste_wijziging_uid' => array(T::UID, true),
		'gesloten' => array(T::Boolean),
		'verwijderd' => array(T::Boolean),
		'wacht_goedkeuring' => array(T::Boolean),
		'plakkerig' => array(T::Boolean),
		'belangrijk' => array(T::String, true),
		'eerste_post_plakkerig' => array(T::Boolean),
		'pagina_per_post' => array(T::Boolean)
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

	public function getForumDeel() {
		return ForumDelenModel::get($this->forum_id);
	}

	public function getGedeeldMet() {
		return ForumDelenModel::get($this->gedeeld_met);
	}

	public function isGedeeld() {
		return !empty($this->gedeeld_met);
	}

	public function magLezen() {
		if ($this->verwijderd AND !$this->magModereren()) {
			return false;
		}
		if (!LoginModel::mag('P_LOGGED_IN') AND $this->gesloten AND strtotime($this->laatst_gewijzigd) < strtotime(InstellingenModel::get('forum', 'externen_geentoegang_gesloten'))) {
			return false;
		}
		return $this->getForumDeel()->magLezen() OR ($this->isGedeeld() AND $this->getGedeeldMet()->magLezen());
	}

	public function magPosten() {
		if ($this->verwijderd OR $this->gesloten) {
			return false;
		}
		return $this->getForumDeel()->magPosten() OR ($this->isGedeeld() AND $this->getGedeeldMet()->magPosten());
	}

	public function magModereren() {
		return $this->getForumDeel()->magModereren() OR ($this->isGedeeld() AND $this->getGedeeldMet()->magModereren());
	}

	public function magStatistiekBekijken() {
		return $this->magModereren() OR ($this->uid != 'x999' AND $this->uid === LoginModel::getUid());
	}

	public function magVerbergen() {
		return !$this->belangrijk AND LoginModel::mag('P_LOGGED_IN');
	}

	public function magMeldingKrijgen() {
		return $this->magLezen();
	}

	public function isVerborgen() {
		if (!isset($this->verbergen)) {
			$this->verbergen = ForumDradenVerbergenModel::instance()->getVerbergenVoorLid($this);
		}
		return $this->verbergen;
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
	 * @return ForumDraadGelezen|false $gelezen
	 */
	public function getWanneerGelezen() {
		return ForumDradenGelezenModel::instance()->getWanneerGelezenDoorLid($this);
	}

	public function isOngelezen() {
		$gelezen = $this->getWanneerGelezen();
		if ($gelezen) {
			if (strtotime($this->laatst_gewijzigd) > strtotime($gelezen->datum_tijd)) {
				return true;
			}
			return false;
		}
		return true;
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
		$this->getForumPosts();
		return !empty($this->forum_posts);
	}

	/**
	 * Public for search results and all sorts of prefetching.
	 *
	 * @param array $forum_posts
	 */
	public function setForumPosts(array $forum_posts) {
		$this->forum_posts = $forum_posts;
	}

	public function getAantalOngelezenPosts() {
		if (!isset($this->aantal_ongelezen_posts)) {
			$where = 'draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE';
			$params = array($this->draad_id);
			$gelezen = $this->getWanneerGelezen();
			if ($gelezen) {
				$where .= ' AND laatst_gewijzigd > ?';
				$params[] = $gelezen->datum_tijd;
			}
			$this->aantal_ongelezen_posts = ForumPostsModel::instance()->count($where, $params);
		}
		return $this->aantal_ongelezen_posts;
	}

	public function getStats() {
		return ForumPostsModel::instance()->getStatsVoorDraad($this);
	}

	public function getStatsJson() {
		$formatter = new ChartTimeSeries(array($this->getStats()));
		return $formatter->getJson($formatter->getModel());
	}

}
