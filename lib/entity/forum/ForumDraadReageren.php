<?php

namespace CsrDelft\entity\forum;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Concept berichten opslaan per draadje.
 * Bijhouden als iemand bezig is een reactie te schrijven.
 */
#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\forum\ForumDradenReagerenRepository::class
	)
]
#[ORM\Table('forum_draden_reageren')]
class ForumDraadReageren
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
	 * Shared primary key
	 * Foreign key
	 * @var int
	 */
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	public $draad_id;
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
	 * Datum en tijd van start reageren
	 * @var DateTimeImmutable
	 */
	#[ORM\Column(type: 'datetime_immutable')]
	public $datum_tijd;
	/**
	 * Opgeslagen concept bericht
	 * @var string
	 */
	#[ORM\Column(type: 'text', nullable: true)]
	public $concept;
	/**
	 * Concept titel
	 * @var string
	 */
	#[ORM\Column(type: 'string', nullable: true)]
	public $titel;
}
