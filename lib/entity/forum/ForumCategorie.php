<?php

namespace CsrDelft\entity\forum;

use CsrDelft\service\security\LoginService;
use CsrDelft\view\formulier\DisplayEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ForumCategorie.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een forum categorie bevat deelfora.
 */
#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\forum\ForumCategorieRepository::class
	)
]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
#[ORM\Table('forum_categorien')]
#[ORM\Index(name: 'volgorde', columns: ['volgorde'])]
class ForumCategorie implements DisplayEntity
{
	/**
	 * Primary key
	 * @var int
	 */
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	public $categorie_id;
	/**
	 * Titel
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $titel;
	/**
	 * Rechten benodigd voor bekijken
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $rechten_lezen;
	/**
	 * Weergave volgorde
	 * @var int
	 */
	#[ORM\Column(type: 'integer')]
	public $volgorde;
	/**
	 * Forumdelen
	 * @var ForumDeel[]
	 */
	#[ORM\OneToMany(targetEntity: \ForumDeel::class, mappedBy: 'categorie')]
	#[ORM\OrderBy(['volgorde' => 'ASC'])]
	public $forum_delen;

	public function __construct()
	{
		$this->forum_delen = new ArrayCollection();
	}

	public function magLezen()
	{
		return LoginService::mag($this->rechten_lezen);
	}

	/**
	 * Public for search results and all sorts of prefetching.
	 *
	 * @param array $forum_delen
	 */
	public function setForumDelen(array $forum_delen)
	{
		$this->forum_delen = $forum_delen;
	}

	public function getId()
	{
		return $this->categorie_id;
	}

	public function getWeergave(): string
	{
		return $this->titel ?? '';
	}
}
