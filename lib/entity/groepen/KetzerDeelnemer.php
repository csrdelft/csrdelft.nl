<?php

namespace CsrDelft\entity\groepen;

use Doctrine\ORM\Mapping as ORM;

/**
 * KetzerDeelnemer.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een deelnemer van een ketzer.
 *
 * @ORM\Entity(repositoryClass="KetzerDeelnemersRepository")
 * @ORM\Table("ketzer_deelnemers")
 */
class KetzerDeelnemer extends AbstractGroepLid {

	protected static $table_name = 'ketzer_deelnemers';

}
