<?php

namespace CsrDelft\model;

use CsrDelft\model\entity\Declaratie;
use CsrDelft\Orm\PersistenceModel;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 26/04/2019
 */
class DeclaratieModel extends PersistenceModel {
	const ORM = Declaratie::class;
}
