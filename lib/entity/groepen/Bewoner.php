<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\groepen\AbstractGroepLid;
use Doctrine\ORM\Mapping as ORM;

/**
 * Bewoner.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een bewoner van een woonoord / huis.
 *
 * @ORM\Entity(repositoryClass="BewonersRepository")
 * @ORM\Table("bewoners")
 */
class Bewoner extends AbstractGroepLid {

	protected static $table_name = 'bewoners';

}
