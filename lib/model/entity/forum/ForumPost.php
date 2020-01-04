<?php

namespace CsrDelft\model\entity\forum;

use CsrDelft\model\forum\ForumDradenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * ForumPost.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een forumpost zit in een ForumDraad.
 *
 */
class ForumPost extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $post_id;
	/**
	 * Deze post is van dit draadje
	 * @var int
	 */
	public $draad_id;
	/**
	 * Lidnummer van auteur
	 * @var string
	 */
	public $uid;
	/**
	 * Tekst
	 * @var string
	 */
	public $tekst;
	/**
	 * Datum en tijd van aanmaken
	 * @var string
	 */
	public $datum_tijd;
	/**
	 * Datum en tijd van laatste bewerking
	 * @var string
	 */
	public $laatst_gewijzigd;
	/**
	 * Bewerking logboek
	 * @var string
	 */
	public $bewerkt_tekst;
	/**
	 * Verwijderd
	 * @var boolean
	 */
	public $verwijderd;
	/**
	 * IP adres van de auteur
	 * @var string
	 */
	public $auteur_ip;
	/**
	 * Wacht op goedkeuring
	 * @var boolean
	 */
	public $wacht_goedkeuring;
	/**
	 * Reden van wegfilteren
	 * @var string
	 */
	public $gefilterd;
	/**
	 * Aantal lezers dat deze post gelezen heeft
	 * @var int
	 */
	private $aantal_gelezen;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'post_id' => array(T::Integer, false, 'auto_increment'),
		'draad_id' => array(T::Integer),
		'uid' => array(T::UID),
		'tekst' => array(T::Text),
		'datum_tijd' => array(T::DateTime),
		'laatst_gewijzigd' => array(T::DateTime),
		'bewerkt_tekst' => array(T::Text, true),
		'verwijderd' => array(T::Boolean),
		'auteur_ip' => array(T::String),
		'wacht_goedkeuring' => array(T::Boolean)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('post_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'forum_posts';

	public function getForumDraad() {
		return ForumDradenModel::instance()->get($this->draad_id);
	}

	public function magCiteren() {
		return LoginModel::mag(P_LOGGED_IN) AND $this->getForumDraad()->magPosten();
	}

	public function magBewerken() {
		$draad = $this->getForumDraad();
		if ($draad->magModereren()) {
			return true;
		}
		if (!$draad->magPosten()) {
			return false;
		}
		return $this->uid === LoginModel::getUid() AND LoginModel::mag(P_LOGGED_IN);
	}

	public function getAantalGelezen() {
		if (!isset($this->aantal_gelezen)) {
			$this->aantal_gelezen = 0;
			foreach ($this->getForumDraad()->getLezers() as $gelezen) {
				if ($this->laatst_gewijzigd AND $this->laatst_gewijzigd <= $gelezen->datum_tijd) {
					$this->aantal_gelezen++;
				}
			}
		}
		return $this->aantal_gelezen;
	}

	public function getGelezenPercentage() {
		return $this->getAantalGelezen() * 100 / $this->getForumDraad()->getAantalLezers();
	}

	public function getLink($external = false) {
	    return ($external ? CSR_ROOT : '') . "/forum/reactie/" . $this->post_id . "#" . $this->post_id;
    }

}
