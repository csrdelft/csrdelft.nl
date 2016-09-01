<?php

/**
 * MededelingenModel.class.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 */
class CategorieModel extends PersistenceModel {

	const ORM = 'Categorie';
	const DIR = 'mededelingen/';

	protected static $instance;

	/**
	 * @param $categorie
	 * @return false|Categorie
	 */
	public static function get($categorie) {
		return static::instance()->retrieveByPrimaryKey(array($categorie));
	}

	/**
	 * @return Categorie[]
	 */
	public static function getAll() {
		return static::instance()->find();
	}

}
