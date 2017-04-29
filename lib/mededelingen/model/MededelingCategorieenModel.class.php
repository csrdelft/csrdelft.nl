<?php
use CsrDelft\Orm\CachedPersistenceModel;

require_once 'mededelingen/model/entity/MededelingCategorie.class.php';

/**
 * MededelingCategorieenModel.class.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class MededelingCategorieenModel extends CachedPersistenceModel {

	const ORM = MededelingCategorie::class;
	const DIR = 'mededelingen/';

	protected static $instance;
	/**
	 * Store MededelingCategorie array as a whole in memcache
	 * @var boolean
	 */
	protected $memcache_prefetch = true;

	/**
	 * @param $categorie
	 * @return false|MededelingCategorie
	 */
	public static function get($categorie) {
		return static::instance()->retrieveByPrimaryKey(array($categorie));
	}

	/**
	 * @return MededelingCategorie[]
	 */
	public static function getAll() {
		return static::instance()->prefetch();
	}

}
