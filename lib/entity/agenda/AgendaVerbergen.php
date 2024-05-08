<?php

namespace CsrDelft\entity\agenda;

use Doctrine\ORM\Mapping as ORM;

/**
 * AgendaVerbergen.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Items in de agenda kunnen worden verborgen per gebruiker.
 */
#[ORM\Table('agenda_verbergen')]
#[ORM\Entity(repositoryClass: \CsrDelft\repository\agenda\AgendaVerbergenRepository::class)]
class AgendaVerbergen
{
	/**
  * Lidnummer
  * Shared primary key
  * @var string
  */
 #[ORM\Id]
 #[ORM\Column(type: 'uid')]
 public $uid;
	/**
  * UUID of Agendeerbaar entity
  * Shared primary key
  * @var string
  */
 #[ORM\Column(type: 'stringkey')]
 #[ORM\Id]
 public $refuuid;
}
