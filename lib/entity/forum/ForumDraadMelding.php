<?php

namespace CsrDelft\entity\forum;

use Doctrine\ORM\Mapping as ORM;

/**
 * ForumDraadMelding.class.php
 * Leden kunnen meldingen krijgen voor een forumdraad
 */
#[ORM\Table('forum_draden_volgen')]
#[ORM\Entity(repositoryClass: \CsrDelft\repository\forum\ForumDradenMeldingRepository::class)]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
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
 #[ORM\JoinColumn(name: 'draad_id', referencedColumnName: 'draad_id')]
 #[ORM\ManyToOne(targetEntity: \ForumDraad::class, inversedBy: 'meldingen')]
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
