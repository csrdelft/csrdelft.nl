<?php

namespace CsrDelft\entity\forum;

use CsrDelft\Orm\Entity\T;
use Doctrine\ORM\Mapping as ORM;

/**
 * ForumDraadMelding.class.php
 * Leden kunnen meldingen krijgen voor een forumdraad
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\forum\ForumDradenMeldingRepository")
 * @ORM\Table("forum_draden_volgen")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class ForumDraadMelding {
	/**
	 * Shared primary key
	 * Foreign key
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 */
	public $draad_id;

	/**
	 * @var ForumDraad
	 * @ORM\ManyToOne(targetEntity="ForumDraad", inversedBy="meldingen")
	 * @ORM\JoinColumn(name="draad_id", referencedColumnName="draad_id")
	 */
	public $draad;
	/**
	 * Lidnummer
	 * Shared primary key
	 * Foreign key
	 * @var string
	 * @ORM\Column(type="string", length=4)
	 * @ORM\Id()
	 */
	public $uid;
	/**
	 * Volgniveau
	 * @see ForumDraadMeldingNiveau
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $niveau = 'altijd';

}
