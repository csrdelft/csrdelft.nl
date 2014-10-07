<?php

require_once 'MVC/model/framework/Persistence.interface.php';

/**
 * Sparse.interface.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Object that may not have all its persistent attributes set.
 * 
 */
interface Sparse {

	/**
	 * Attributes that are always retrieved.
	 * 
	 * @return array
	 */
	public static function getNonSparseAttributes();

	/**
	 * Are there any attributes not yet retrieved?
	 * Required tracking of retrieved attributes to discern invalid values.
	 * 
	 * @return boolean
	 */
	public function isSparse();
}
