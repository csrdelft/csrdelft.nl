<?php

namespace CsrDelft\model\peilingen;

use CsrDelft\model\entity\peilingen\PeilingOptie;
use CsrDelft\Orm\PersistenceModel;

/**
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class PeilingOptiesModel extends PersistenceModel {
	const ORM = PeilingOptie::class;
}
