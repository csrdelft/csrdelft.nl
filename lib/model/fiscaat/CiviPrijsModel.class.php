<?php

namespace CsrDelft\model\fiscaat;

use CsrDelft\model\entity\fiscaat\CiviPrijs;
use CsrDelft\Orm\PersistenceModel;

/**
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class CiviPrijsModel extends PersistenceModel {
	/**
	 * ORM class.
	 */
	const ORM = CiviPrijs::class;
}
