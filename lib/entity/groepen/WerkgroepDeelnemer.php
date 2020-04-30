<?php

namespace CsrDelft\entity\groepen;


use Doctrine\ORM\Mapping as ORM;

/**
 * WerkgroepDeelnemer.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een deelnemer van een werkgroep.
 *
 * @ORM\Entity(repositoryClass="WerkgroepDeelnemersRepository")
 * @ORM\Table("werkgroep_deelnemers")
 */
class WerkgroepDeelnemer extends KetzerDeelnemer {

	protected static $table_name = 'werkgroep_deelnemers';

}
