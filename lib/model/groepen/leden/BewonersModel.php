<?php
/**
 * BewonersModel.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 06/05/2017
 */

namespace CsrDelft\model\groepen\leden;

use CsrDelft\model\AbstractGroepLedenModel;
use CsrDelft\model\entity\groepen\Bewoner;

class BewonersModel extends AbstractGroepLedenModel {

	const ORM = Bewoner::class;

	protected static $instance;

}