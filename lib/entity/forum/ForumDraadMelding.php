<?php

namespace CsrDelft\entity\forum;

use CsrDelft\repository\forum\ForumDradenMeldingRepository;
use ForumDraad;
use Doctrine\ORM\Mapping as ORM;

/**
 * ForumDraadMelding.class.php
 * Leden kunnen meldingen krijgen voor een forumdraad
 */
#[ORM\Entity(repositoryClass: ForumDradenMeldingRepository::class)]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
#[ORM\Table('forum_draden_volgen')]
class ForumDraadMelding
{
	/**
	 * Shared primary key
	 * Foreign key
	 * @var int
	 */
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	public $draad_id;

	/**
	 * @var ForumDraad
	 */
	#[ORM\ManyToOne(targetEntity: ForumDraad::class, inversedBy: 'meldingen')]
	#[ORM\JoinColumn(name: 'draad_id', referencedColumnName: 'draad_id')]
	public $draad;
	/**
	 * Lidnummer
	 * Shared primary key
	 * Foreign key
	 * @var string
	 */
	#[ORM\Column(type: 'uid')]
	#[ORM\Id]
	public $uid;
	/**
	 * Volgniveau
	 * @var ForumDraadMeldingNiveau
	 */
	#[ORM\Column(type: 'enumForumDraadMeldingNiveau')]
	public $niveau;
}
