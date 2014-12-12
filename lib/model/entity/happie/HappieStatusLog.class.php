<?php

require_once 'model/entity/ChangeLogEntry.class.php';

/**
 * HappieStatusLog.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class HappieStatusLog extends ChangeLogEntry {

	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'happie_status_log';

}
