<?php
/**
 * KringLedenModel.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 06/05/2017
 */

namespace CsrDelft\model\groepen\leden;

use CsrDelft\model\AbstractGroepLedenModel;
use CsrDelft\model\entity\groepen\KringLid;

class KringLedenModel extends AbstractGroepLedenModel {

	const ORM = KringLid::class;

	protected static $instance;

}