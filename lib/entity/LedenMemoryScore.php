<?php

namespace CsrDelft\entity;

use CsrDelft\Component\DataTable\DataTableEntry;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * LedenMemoryScore.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\LedenMemoryScoresRepository::class
	)
]
#[ORM\Table('memory_scores')]
class LedenMemoryScore implements DataTableEntry
{
	/**
	 * Id
	 * @var int
	 */
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	public $id;
	/**
	 * Seconden
	 * @var int
	 */
	#[ORM\Column(type: 'integer')]
	public $tijd;
	/**
	 * Aantal beurten
	 * @var int
	 */
	#[ORM\Column(type: 'integer')]
	public $beurten;
	/**
	 * Aantal goed
	 * @var int
	 */
	#[ORM\Column(type: 'integer')]
	public $goed;
	/**
	 * UUID
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $groep;
	/**
	 * Eerlijk verkregen score
	 * @var boolean
	 */
	#[ORM\Column(type: 'boolean')]
	public $eerlijk;
	/**
	 * Door lidnummer
	 * Foreign key
	 * @var string
	 */
	#[ORM\Column(type: 'uid')]
	public $door_uid;
	/**
	 * Behaald op datum en tijd
	 * @var DateTimeImmutable
	 */
	#[ORM\Column(type: 'datetime')]
	public $wanneer;
}
