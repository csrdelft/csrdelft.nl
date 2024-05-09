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
#[ORM\Table('changelog')]
#[ORM\Entity(repositoryClass: ChangeLogRepository::class)]
class ChangeLogEntry
{
	/**
  * Primary key
  * @var int
  */
 #[ORM\Column(type: 'integer')]
 #[ORM\Id]
 #[ORM\GeneratedValue]
 #[Serializer\Groups('datatable')]
 public $id;
	/**
  * The moment it changed
  * @var DateTimeImmutable
  */
 #[ORM\Column(type: 'datetime_immutable')]
 #[Serializer\Groups('datatable')]
 public $moment;
	/**
  * The thing that changed
  * @var string
  */
 #[ORM\Column(type: 'string')]
 #[Serializer\Groups('datatable')]
 public $subject;
	/**
  * The property that changed
  * @var string
  */
 #[ORM\Column(type: 'string')]
 #[Serializer\Groups('datatable')]
 public $property;
	/**
  * The value before
  * @var string
  */
 #[ORM\Column(type: 'text', nullable: true)]
 #[Serializer\Groups('datatable')]
 public $old_value;
	/**
  * The value after
  * @var string
  */
 #[ORM\Column(type: 'text', nullable: true)]
 #[Serializer\Groups('datatable')]
 public $new_value;
	/**
  * Lidnummer of who did it
  * @var string
  */
 #[ORM\Column(type: 'uid')]
 #[Serializer\Groups('datatable')]
 public $uid;
}
