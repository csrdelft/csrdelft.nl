<?php

namespace CsrDelft\entity\forum;

use CsrDelft\model\entity\forum\ForumDraad;
use CsrDelft\model\entity\security\AuthenticationMethod;
use CsrDelft\model\forum\ForumDradenModel;
use CsrDelft\model\forum\ForumModel;
use CsrDelft\model\security\LoginModel;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een deelforum zit in een forumcategorie bevat ForumDraden.
 * @ORM\Entity(repositoryClass="CsrDelft\repository\forum\ForumDelenRepository")
 * @ORM\Table("forum_delen")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class ForumDeel {
	/**
	 * Primary key
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 */
	public $forum_id;
	/**
	 * Dit forum valt onder deze categorie
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	public $categorie_id;
	/**
	 * Titel
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $titel;
	/**
	 * Omschrijving
	 * @var string
	 * @ORM\Column(type="text")
	 */
	public $omschrijving;
	/**
	 * Rechten benodigd voor lezen
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $rechten_lezen;
	/**
	 * Rechten benodigd voor posten
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $rechten_posten;
	/**
	 * Rechten benodigd voor modereren
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $rechten_modereren;
	/**
	 * Weergave volgorde
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	public $volgorde;
	/**
	 * Forumdraden
	 * @var ForumDraad[]
	 */
	private $forum_draden;

	public function getForumCategorie() {
		return ForumModel::instance()->get($this->categorie_id);
	}

	public function magLezen($rss = false) {
		$auth = ($rss ? AuthenticationMethod::getTypeOptions() : null);
		return LoginModel::mag(P_FORUM_READ, $auth) AND LoginModel::mag($this->rechten_lezen, $auth) AND $this->getForumCategorie()->magLezen();
	}

	public function magPosten() {
		return LoginModel::mag($this->rechten_posten);
	}

	public function magModereren() {
		return LoginModel::mag($this->rechten_modereren);
	}

	public function magMeldingKrijgen() {
		return $this->magLezen();
	}

	public function isOpenbaar() {
		return strpos($this->rechten_lezen, P_FORUM_READ) !== false;
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
