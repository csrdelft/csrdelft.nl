<?php

namespace CsrDelft\entity\groepen;


use Doctrine\ORM\Mapping as ORM;

/**
 * ActiviteitDeelnemer.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een deelnemer van een activiteit.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\leden\ActiviteitDeelnemersRepository")
 * @ORM\Table("activiteit_deelnemers")
 */
class ActiviteitDeelnemer extends KetzerDeelnemer {

	protected static $table_name = 'activiteit_deelnemers';

}
