<?php

use CsrDelft\Orm\PersistenceModel;

require_once 'model/entity/fiscaat/CiviCategorie.class.php';

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
