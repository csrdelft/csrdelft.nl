<?php

namespace CsrDelft\entity\maalcie;

use CsrDelft\repository\maalcie\MaaltijdBeoordelingenRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een MaaltijdBeoordeling instantie beschrijft een beoordeling door een lid van een maaltijd.
 * Op basis hiervan worden statistieken bepaald waarbij de beoordelingen genormaliseerd worden.
 */
#[ORM\Table('mlt_beoordelingen')]
#[ORM\Entity(repositoryClass: MaaltijdBeoordelingenRepository::class)]
class MaaltijdBeoordeling
{
	/**
  * Shared primary key
  * Foreign key
  * @var int
  */
 #[ORM\Column(type: 'integer')]
 #[ORM\Id]
 public $maaltijd_id;
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
  * Kwantiteit beoordeling
  * @var float
  */
 #[ORM\Column(type: 'float', nullable: true)]
 public $kwantiteit;
	/**
  * Kwaliteit beoordeling
  * @var float
  */
 #[ORM\Column(type: 'float', nullable: true)]
 public $kwaliteit;
}
