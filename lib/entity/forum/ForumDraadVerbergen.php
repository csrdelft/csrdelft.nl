<?php

namespace CsrDelft\entity\forum;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een ForumDraad kan worden verborgen door een lid.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\forum\ForumDradenVerbergenRepository")
 * @ORM\Table("forum_draden_verbergen")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class ForumDraadVerbergen {
	/**
	 * Shared primary key
	 * Foreign key
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 */
	public $draad_id;
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