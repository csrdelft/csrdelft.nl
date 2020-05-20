<?php

namespace CsrDelft\entity\forum;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Eisen;
use CsrDelft\repository\forum\ForumPostsRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\bbcode\CsrBB;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

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
	 * @ORM\Column(type="uid")
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
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $datum_tijd;
	/**
	 * Datum en tijd van laatst geplaatste of gewijzigde post
	 * @var DateTimeImmutable
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
	 * @var ForumPost
	 * @ORM\OneToOne(targetEntity="ForumPost")
	 * @ORM\JoinColumn(name="laatste_post_id", referencedColumnName="post_id")
	 */
	public $laatste_post;
	/**
	 * Uid van de auteur van de laatst geplaatste of gewijzigde post
	 * @var string
	 * @ORM\Column(type="uid", nullable=true)
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
	 * Lijst van lezers (wanneer)
	 * @var PersistentCollection|ForumDraadGelezen[]
	 * @ORM\OneToMany(targetEntity="ForumDraadGelezen", mappedBy="draad")
	 */
	public $lezers;
	/**
	 * @var ForumDeel
	 * @ORM\ManyToOne(targetEntity="ForumDeel")
	 * @ORM\JoinColumn(name="forum_id", referencedColumnName="forum_id")
	 */
	public $deel;
	/**
	 * @var ForumDeel
	 * @ORM\ManyToOne(targetEntity="ForumDeel")
	 * @ORM\JoinColumn(name="gedeeld_met", referencedColumnName="forum_id", nullable=true)
	 */
	public $gedeeld_met_deel;
	/**
	 * ForumPosts
	 * @var PersistentCollection|ForumPost[]
	 * @ORM\OneToMany(targetEntity="ForumPost", mappedBy="draad")
	 */
	private $forum_posts;
	/**
	 * Aantal ongelezen posts
	 * @var int
	 */
	private $aantal_ongelezen_posts;
	/**
	 * @var PersistentCollection|ForumDraadVerbergen[]
	 * @ORM\OneToMany(targetEntity="ForumDraadVerbergen", mappedBy="draad")
	 */
	private $verbergen;
	/**
	 * @var PersistentCollection|ForumDraadMelding[]
	 * @ORM\OneToMany(targetEntity="ForumDraadMelding", mappedBy="draad")
	 */
	private $meldingen;

	public function __construct() {
		$this->verbergen = new ArrayCollection();
		$this->meldingen = new ArrayCollection();
		$this->forum_posts = new ArrayCollection();
	}

	public function magPosten() {
		if ($this->verwijderd || $this->gesloten) {
			return false;
		}
		return $this->deel->magPosten() || ($this->isGedeeld() && $this->gedeeld_met_deel->magPosten());
	}

	public function isGedeeld() {
		return !empty($this->gedeeld_met);
	}

	public function magStatistiekBekijken() {
		return $this->magModereren() || ($this->uid != LoginService::UID_EXTERN && $this->uid === LoginService::getUid());
	}

	public function magModereren() {
		return $this->deel->magModereren() || ($this->isGedeeld() && $this->gedeeld_met_deel->magModereren());
	}

	public function magVerbergen() {
		return !$this->belangrijk && LoginService::mag(P_LOGGED_IN);
	}

	public function magMeldingKrijgen() {
		return $this->magLezen();
	}

	public function magLezen() {
		if ($this->verwijderd && !$this->magModereren()) {
			return false;
		}
		if (!LoginService::mag(P_LOGGED_IN) && $this->gesloten && $this->laatst_gewijzigd < date_create_immutable(instelling('forum', 'externen_geentoegang_gesloten'))) {
			return false;
		}
		return $this->deel->magLezen() || ($this->isGedeeld() && $this->gedeeld_met_deel->magLezen());
	}

	public function isVerborgen() {
		return $this->verbergen->matching(Eisen::voorIngelogdeGebruiker())->first() != null;
	}

	public function getAantalLezers() {
		return count($this->lezers);
	}

	public function isOngelezen() {
		if ($gelezen = $this->getWanneerGelezen()) {
			if ($this->laatst_gewijzigd > $gelezen->datum_tijd) {
				return true;
			}
			return false;
		}
		return true;
	}

	/**
	 * null if ongelezen!
	 *
	 * @return ForumDraadGelezen|null $gelezen
	 */
	public function getWanneerGelezen() {
		return $this->lezers->matching(Eisen::voorIngelogdeGebruiker())->first();
	}

	public function hasForumPosts() {
		return !empty($this->forum_posts);
	}

	/**
	 * Lazy loading by foreign key.
	 *
	 * @return ForumPost[]
	 */
	public function getForumPosts() {
		return $this->forum_posts;
	}

	/**
	 * @return string
	 */
	public function getLaatstePostSamenvatting() {
		$laatste = $this->laatste_post;
		$parseMail = CsrBB::parseMail($laatste->tekst);
		return truncate($parseMail, 100);
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

	public function getMeldingsNiveau() {
		if (!$this->magLezen()) {
			return false;
		}

		/** @var ForumDraadMelding $melding */
		if ($melding = $this->meldingen->matching(Eisen::voorIngelogdeGebruiker())->first()) {
			return $melding->niveau;
		}

		return false;
	}
}
