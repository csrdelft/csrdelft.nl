<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\groepen\AbstractGroepLid;
use Doctrine\ORM\Mapping as ORM;

/**
 * RechtenGroepLid.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een lid van een rechten-groep.
 *
 * @ORM\Entity(repositoryClass="RechtenGroepLedenRepository")
 * @ORM\Table("groep_leden")
 */
class RechtenGroepLid extends AbstractGroepLid {

	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'groep_leden';

}
