<?php
/**
 * Created by PhpStorm.
 * User: gerbe
 * Date: 07/01/2019
 * Time: 22:10
 */

namespace CsrDelft\model;


trait HasForeignKeys {
	public static function getForeignKeys() {
		return static::$foreign_keys;
	}
}
