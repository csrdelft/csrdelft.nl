<?php
/**
 * CommissieLedenModel.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */

namespace CsrDelft\model\groepen\leden;

use CsrDelft\model\AbstractGroepLedenModel;
use CsrDelft\model\entity\groepen\CommissieLid;

class CommissieLedenModel extends AbstractGroepLedenModel {

	const ORM = CommissieLid::class;

	protected static $instance;

}