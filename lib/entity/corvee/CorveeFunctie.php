<?php

namespace CsrDelft\entity\corvee;

use CsrDelft\entity\ISelectEntity;
use CsrDelft\view\formulier\DisplayEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * CorveeFunctie.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 *
 * Een CorveeFunctie instantie beschrijft een functie die een lid kan uitvoeren als taak en of hiervoor een kwalificatie nodig is.
 * Zo ja, dan moet een lid op moment van toewijzen van de taak over deze kwalificatie beschikken (lid.id moet voorkomen in tabel crv_kwalificaties).
 *
 * Bijvoorbeeld:
 *  - Tafelpraeses
 *  - Kwalikok (kwalificatie benodigd!)
 *  - Afwasser
 *  - Keuken/Afzuigkap/Frituur schoonmaker
 *  - Klusser
 *
 * Standaard punten wordt standaard overgenomen, maar kan worden overschreven per corveetaak.
 *
 *
 * Zie ook CorveeKwalificatie.class.php en CorveeTaak.class.php
 */
#[ORM\Table('crv_functies')]
#[ORM\Entity(repositoryClass: \CsrDelft\repository\corvee\CorveeFunctiesRepository::class)]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
class CorveeFunctie implements ISelectEntity, DisplayEntity
{
	# ID om functie van kwalikok op te halen, wijzigen als ID van Kwalikok wijzigt
	const KWALIKOK_FUNCTIE_ID = 7;

	/**
  * Primary key
  * @var int
  */
 #[ORM\Column(type: 'integer')]
 #[ORM\Id]
 #[ORM\GeneratedValue]
 public $functie_id;
	/**
  * Naam
  * @var string
  */
 #[ORM\Column(type: 'string')]
 public $naam;
	/**
  * Afkorting
  * @var string
  */
 #[ORM\Column(type: 'string')]
 public $afkorting;
	/**
  * E-mailbericht
  * @var string
  */
 #[ORM\Column(type: 'text')]
 public $email_bericht;
	/**
  * Standaard aantal corveepunten
  * @var int
  */
 #[ORM\Column(type: 'integer')]
 public $standaard_punten;
	/**
  * Is een kwalificatie benodigd
  * @var boolean
  */
 #[ORM\Column(type: 'boolean')]
 public $kwalificatie_benodigd;
	/**
  * Geeft deze functie speciale rechten
  * om maaltijden te mogen sluiten
  * @var boolean
  */
 #[ORM\Column(type: 'boolean')]
 public $maaltijden_sluiten;
	/**
  * Kwalificaties
  * @var CorveeKwalificatie[]
  */
 #[ORM\OneToMany(targetEntity: \CorveeKwalificatie::class, mappedBy: 'corveeFunctie')]
 public $kwalificaties;

	public function hasKwalificaties()
	{
		return sizeof($this->kwalificaties) > 0;
	}

	public function getValue()
	{
		return $this->naam;
	}

	public function getId()
	{
		return $this->functie_id;
	}

	public function getWeergave(): string
	{
		return $this->naam ?? '';
	}
}
