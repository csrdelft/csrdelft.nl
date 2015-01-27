<?php

require_once 'model/entity/groepen/KetzerDeelnemer.class.php';

/**
 * ActiviteitDeelnemer.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een deelnemer van een activiteit.
 * 
 */
class ActiviteitDeelnemer extends KetzerDeelnemer {

	protected static $table_name = 'activiteit_deelnemers';

}
