<?php

namespace CsrDelft\model;

use CsrDelft\model\entity\DeclaratieRegel;
use CsrDelft\Orm\PersistenceModel;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 26/04/2019
 */
class DeclaratieRegelModel extends PersistenceModel {
	const ORM = DeclaratieRegel::class;
}
