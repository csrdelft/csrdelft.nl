<?php

require_once 'model/entity/LidStatus.enum.php';

/**
 * LichtingsLid.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een lid van een lichting.
 * 
 */
class LichtingsLid extends AbstractGroepLid {

	protected static $table_name = 'lichting_leden';

}
