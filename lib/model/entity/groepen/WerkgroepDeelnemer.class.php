<?php

require_once 'model/entity/groepen/KetzerDeelnemer.class.php';

/**
 * WerkgroepDeelnemer.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een deelnemer van een werkgroep.
 * 
 */
class WerkgroepDeelnemer extends KetzerDeelnemer {

	protected static $table_name = 'werkgroep_deelnemers';

}
