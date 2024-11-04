<?php

namespace CsrDelft\entity\forum;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Eisen;
use CsrDelft\repository\forum\ForumDradenRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een deelforum zit in een forumcategorie bevat ForumDraden.
 */
#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\forum\ForumDelenRepository::class
	)
]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
#[ORM\Table('forum_delen')]
#[ORM\Index(name: 'volgorde', columns: ['volgorde'])]
class ForumDeel
{
	/**
	 * Primary key
	 * @var int
	 */
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	public $forum_id;
	/**
	 * Dit forum valt onder deze categorie
	 * @var int
	 */
	#[ORM\Column(type: 'integer')]
	public $categorie_id;
	/**
	 * Titel
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $titel;
	/**
	 * Omschrijving
	 * @var string
	 */
	#[ORM\Column(type: 'text')]
	public $omschrijving;
	/**
	 * Rechten benodigd voor lezen
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $rechten_lezen;
	/**
	 * Rechten benodigd voor posten
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $rechten_posten;
	/**
	 * Rechten benodigd voor modereren
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $rechten_modereren;
	/**
	 * Weergave volgorde
	 * @var int
	 */
	#[ORM\Column(type: 'integer')]
	public $volgorde;
	/**
	 * @var ForumCategorie
	 */
	#[
		ORM\ManyToOne(
			targetEntity: \ForumCategorie::class,
			inversedBy: 'forum_delen'
		)
	]
	#[ORM\JoinColumn(name: 'categorie_id', referencedColumnName: 'categorie_id')]
	public $categorie;
	/**
	 * @var PersistentCollection|ForumDeelMelding[]
	 */
	#[ORM\OneToMany(targetEntity: \ForumDeelMelding::class, mappedBy: 'deel')]
	public $meldingen;
	/**
	 * Forumdraden
	 * @var ForumDraad[]
	 */
	private $forum_draden;

	public function __construct()
	{
		$this->meldingen = new ArrayCollection();
	}

	public function magLezen($rss = false): bool
	{
		return LoginService::mag(P_FORUM_READ) &&
			LoginService::mag($this->rechten_lezen) &&
			$this->categorie->magLezen();
	}

	public function magPosten(): bool
	{
		return LoginService::mag($this->rechten_posten);
	}

	public function magModereren(): bool
	{
		return LoginService::mag($this->rechten_modereren);
	}

	public function magMeldingKrijgen(): bool
	{
		return $this->magLezen();
	}

	/**
	 * Public for search results and all sorts of prefetching.
	 *
	 * @param array $forum_draden
	 */
	public function setForumDraden($forum_draden): void
	{
		$this->forum_draden = $forum_draden;
	}
}
