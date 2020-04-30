<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\groepen\AbstractGroepLid;
use Doctrine\ORM\Mapping as ORM;

/**
 * BestuursLid.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een lid van een bestuur.
 *
 * @ORM\Entity(repositoryClass="BestuursLedenRepository")
 * @ORM\Table("bestuurs_leden")
 */
class BestuursLid extends AbstractGroepLid {

	protected static $table_name = 'bestuurs_leden';

}
