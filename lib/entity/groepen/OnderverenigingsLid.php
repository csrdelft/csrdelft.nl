<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\groepen\AbstractGroepLid;
use Doctrine\ORM\Mapping as ORM;

/**
 * OnderverenigingsLid.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een lid van een ondervereniging.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\leden\OnderverenigingsLedenModel")
 * @ORM\Table("ondervereniging_leden")
 */
class OnderverenigingsLid extends AbstractGroepLid {

	protected static $table_name = 'ondervereniging_leden';

}
