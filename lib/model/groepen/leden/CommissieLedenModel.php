<?php

namespace CsrDelft\model\groepen\leden;

use CsrDelft\model\AbstractGroepLedenModel;
use CsrDelft\model\entity\groepen\CommissieLid;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */
class CommissieLedenModel extends AbstractGroepLedenModel {
	const ORM = CommissieLid::class;
}
