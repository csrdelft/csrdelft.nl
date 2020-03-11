<?php

namespace CsrDelft\entity\forum;

use CsrDelft\common\ContainerFacade;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\forum\ForumDelenRepository;
use CsrDelft\repository\forum\ForumDradenGelezenRepository;
use CsrDelft\repository\forum\ForumDradenVerbergenRepository;
use CsrDelft\repository\forum\ForumPostsRepository;
use CsrDelft\view\ChartTimeSeries;
use Doctrine\ORM\Mapping as ORM;

/**
 * ForumDraad.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een ForumDraad zit in een deelforum en bevat forumposts.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\forum\ForumDradenRepository")
 * @ORM\Table("forum_draden")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class ForumDraad {

	/**
	 * Primary key
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 */
	public $draad_id;
	/**
	 * Forum waaronder dit topic valt
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	public $forum_id;
	/**
	 * Forum waarmee dit topic gedeeld is
	 * @var int
	 * @ORM\Column(type="integer", nullable=true)
	 */
	public $gedeeld_met;
	/**
	 * Lidnummer van auteur
	 * @var string
	 * @ORM\Column(type="string", length=4)
	 */
	public $uid;
	/**
	 * Titel
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $titel;
	/**
	 * Datum en tijd van aanmaken
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	public $datum_tijd;
	/**
	 * Datum en tijd van laatst geplaatste of gewijzigde post
	 * @var \DateTime
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	public $laatst_gewijzigd;
	/**
	 * Id van de laatst geplaatste of gewijzigde post
	 * @var integer
	 * @ORM\Column(type="integer", nullable=true)
	 */
	public $laatste_post_id;
	/**
	 * Uid van de auteur van de laatst geplaatste of gewijzigde post
	 * @var string
	 * @ORM\Column(type="string", length=4, nullable=true)
	 */
	public $laatste_wijziging_uid;
	/**
	 * Gesloten (posten niet meer mogelijk)
	 * @var boolean
	 * @ORM\Column(type="boolean")
	 */
	public $gesloten;
	/**
	 * Verwijderd
	 * @var boolean
	 * @ORM\Column(type="boolean")
	 */
	public $verwijderd;
	/**
	 * Wacht op goedkeuring
	 * @var boolean
	 * @ORM\Column(type="boolean")
	 */
	public $wacht_goedkeuring;
	/**
	 * Altijd bovenaan weergeven
	 * @var boolean
	 * @ORM\Column(type="boolean")
	 */
	public $plakkerig;
	/**
	 * Belangrijk markering
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	public $belangrijk;
	/**
	 * Eerste post altijd bovenaan weergeven
	 * @var boolean
	 * @ORM\Column(type="boolean")
	 */
	public $eerste_post_plakkerig;
	/**
	 * Een post per pagina
	 * @var boolean
	 * @ORM\Column(type="boolean")
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
	 * @ORM\OneToMany(targetEntity="ForumDraadGelezen", mappedBy="draad")
	 */
	public $lezers;
	/**
	 * Verbergen voor gebruiker
	 * @var boolean
	 */
	private $verbergen;

	public function getForumDeel() {
		return ContainerFacade::getContainer()->get(ForumDelenRepository::class)->get($this->forum_id);
	}

	public function getGedeeldMet() {
		return ContainerFacade::getContainer()->get(ForumDelenRepository::class)->get($this->gedeeld_met);
	}

	public function isGedeeld() {
		return !empty($this->gedeeld_met);
	}

	public function magLezen() {
		if ($this->verwijderd && !$this->magModereren()) {
			return false;
		}
		if (!LoginModel::mag(P_LOGGED_IN) && $this->gesloten && $this->laatst_gewijzigd < date_create(instelling('forum', 'externen_geentoegang_gesloten'))) {
			return false;
		}
		return $this->getForumDeel()->magLezen() || ($this->isGedeeld() && $this->getGedeeldMet()->magLezen());
	}

	public function magPosten() {
		if ($this->verwijderd || $this->gesloten) {
			return false;
		}
		return $this->getForumDeel()->magPosten() || ($this->isGedeeld() && $this->getGedeeldMet()->magPosten());
	}

	public function magModereren() {
		return $this->getForumDeel()->magModereren() || ($this->isGedeeld() && $this->getGedeeldMet()->magModereren());
	}

	public function magStatistiekBekijken() {
		return $this->magModereren() || ($this->uid != LoginModel::UID_EXTERN && $this->uid === LoginModel::getUid());
	}

	public function magVerbergen() {
		return !$this->belangrijk && LoginModel::mag(P_LOGGED_IN);
	}

	public function magMeldingKrijgen() {
		return $this->magLezen();
	}

	public function isVerborgen() {
		if (!isset($this->verbergen)) {
			$forumDradenVerbergenRepository = ContainerFacade::getContainer()->get(ForumDradenVerbergenRepository::class);
			$this->verbergen = $forumDradenVerbergenRepository->getVerbergenVoorLid($this);
		}
		return $this->verbergen;
	}

	public function getAantalLezers() {
		return count($this->lezers);
	}

	/**
	 * FALSE if ongelezen!
	 *
	 * @return ForumDraadGelezen|false $gelezen
	 */
	public function getWanneerGelezen() {
		$forumDradenGelezenRepository = ContainerFacade::getContainer()->get(ForumDradenGelezenRepository::class);
		return $forumDradenGelezenRepository->getWanneerGelezenDoorLid($this);
	}

	public function isOngelezen() {
		$gelezen = $this->getWanneerGelezen();
		if ($gelezen) {
			if ($this->laatst_gewijzigd > $gelezen->datum_tijd) {
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
			$this->setForumPosts(ContainerFacade::getContainer()->get(ForumPostsRepository::class)->getForumPostsVoorDraad($this));
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
			$forumPostsRepository = ContainerFacade::getContainer()->get(ForumPostsRepository::class);
			$this->aantal_ongelezen_posts = $forumPostsRepository->getAantalOngelezenPosts($this);
		}
		return $this->aantal_ongelezen_posts;
	}

	public function getStats() {
		return ContainerFacade::getContainer()->get(ForumPostsRepository::class)->getStatsVoorDraad($this);
	}

	public function getStatsJson() {
		$formatter = new ChartTimeSeries(array($this->getStats()));
		return $formatter->getJson($formatter->getModel());
	}

}
