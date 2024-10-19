<?php

namespace CsrDelft\entity;

use CsrDelft\repository\ChangeLogRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * ChangeLogEntry.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
#[ORM\Entity(repositoryClass: ChangeLogRepository::class)]
#[ORM\Table('changelog')]
class ChangeLogEntry
{
	/**
	 * Primary key
	 * @var int
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	public $id;
	/**
	 * The moment it changed
	 * @var DateTimeImmutable
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'datetime')]
	public $moment;
	/**
	 * The thing that changed
	 * @var string
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'string')]
	public $subject;
	/**
	 * The property that changed
	 * @var string
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'string')]
	public $property;
	/**
	 * The value before
	 * @var string
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'text', nullable: true)]
	public $old_value;
	/**
	 * The value after
	 * @var string
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'text', nullable: true)]
	public $new_value;
	/**
	 * Lidnummer of who did it
	 * @var string
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'uid')]
	public $uid;
}
