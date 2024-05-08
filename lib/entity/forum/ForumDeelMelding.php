<?php

namespace CsrDelft\entity\forum;

use Doctrine\ORM\Mapping as ORM;

/**
 * Leden kunnen meldingen krijgen voor een forumdeel
 */
#[ORM\Table('forum_delen_meldingen')]
#[ORM\Entity(repositoryClass: \CsrDelft\repository\forum\ForumDelenMeldingRepository::class)]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
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
 #[ORM\JoinColumn(name: 'forum_id', referencedColumnName: 'forum_id')]
 #[ORM\ManyToOne(targetEntity: \ForumDeel::class, inversedBy: 'meldingen')]
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
