<?php

namespace CsrDelft\model\fiscaat;

use CsrDelft\model\entity\fiscaat\CiviCategorie;
use CsrDelft\Orm\PersistenceModel;

/**
 * Class CiviCategorieModel
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class CiviCategorieModel extends PersistenceModel {
	const ORM = CiviCategorie::class;
	const DIR = 'fiscaat/';

	protected static $instance;
}
