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

	/**
	 * @var CiviCategorieModel
	 */
	protected static $instance;

	/**
	 * @param $id
	 * @return CiviCategorie|false
	 */
	public static function get($id) {
		return static::instance()->retrieveByPrimaryKey([$id]);
	}
}
