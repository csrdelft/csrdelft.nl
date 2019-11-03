<?php

namespace CsrDelft\model\fiscaat;

use CsrDelft\model\entity\fiscaat\CiviSaldoLog;
use CsrDelft\Orm\PersistenceModel;

/**
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class CiviSaldoLogModel extends PersistenceModel {
	/**
	 * ORM class.
	 */
	const ORM = CiviSaldoLog::class;

	/**
	 * @param string $type
	 * @param string $data
	 */
	public function log($type, $data) {
		$logEntry = new CiviSaldoLog();
		// Don't use filter_input for $_SERVER when PHP runs through FastCGI:
		// https://bugs.php.net/bug.php?id=49184
		$logEntry->ip = isset($_SERVER['REMOTE_ADDR']) ? filter_var($_SERVER['REMOTE_ADDR']) : '';
		$logEntry->type = $type;
		$logEntry->data = json_encode($data);
		$this->create($logEntry);
	}
}
