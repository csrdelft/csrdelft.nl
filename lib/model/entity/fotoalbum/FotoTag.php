<?php

namespace CsrDelft\model\entity\fotoalbum;

use CsrDelft\model\entity\KeywordTag;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\Orm\Entity\T;


/**
 * FotoTag.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class FotoTag extends KeywordTag {

	/**
	 * X-coord
	 * @var float
	 */
	public $x;
	/**
	 * Y-coord
	 * @var float
	 */
	public $y;
	/**
	 * Size
	 * @var float
	 */
	public $size;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'x' => array(T::Float),
		'y' => array(T::Float),
		'size' => array(T::Float)
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'foto_tags';

	public function jsonSerialize() {
		$array = parent::jsonSerialize();
		$array['name'] = ProfielRepository::getNaam($this->keyword, 'user');
		return $array;
	}

}
