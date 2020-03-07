<?php

namespace CsrDelft\entity\forum;

use Doctrine\ORM\Mapping as ORM;

/**
 * Leden kunnen meldingen krijgen voor een forumdeel
 * @ORM\Entity(repositoryClass="CsrDelft\repository\forum\ForumDelenMeldingRepository")
 * @ORM\Table("forum_delen_meldingen")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class ForumDeelMelding {
	/**
	 * Shared primary key
	 * Foreign key
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 */
	public $forum_id;

	/**
	 * Lidnummer
	 * Shared primary key
	 * Foreign key
	 * @var string
	 * @ORM\Column(type="string", length=4)
	 * @ORM\Id()
	 */
	public $uid;
}
