<?php

namespace CsrDelft\entity\forum;

use CsrDelft\repository\forum\ForumDelenMeldingRepository;
use ForumDeel;
use Doctrine\ORM\Mapping as ORM;

/**
 * Leden kunnen meldingen krijgen voor een forumdeel
 */
#[ORM\Entity(repositoryClass: ForumDelenMeldingRepository::class)]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
#[ORM\Table('forum_delen_meldingen')]
class ForumDeelMelding
{
	/**
	 * Shared primary key
	 * Foreign key
	 * @var int
	 */
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	public $forum_id;

	/**
	 * @var ForumDeel
	 */
	#[ORM\ManyToOne(targetEntity: ForumDeel::class, inversedBy: 'meldingen')]
	#[ORM\JoinColumn(name: 'forum_id', referencedColumnName: 'forum_id')]
	public $deel;

	/**
	 * Lidnummer
	 * Shared primary key
	 * Foreign key
	 * @var string
	 */
	#[ORM\Column(type: 'uid')]
	#[ORM\Id]
	public $uid;
}
