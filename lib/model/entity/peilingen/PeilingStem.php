<?php

namespace CsrDelft\model\entity\peilingen;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class PeilingStem extends PersistentEntity {

	/**
	 * Shared primary key
	 * Foreign key
	 * @var int
	 */
	public $peiling_id;
	/**
	 * Lidnummer
	 * Shared primary key
	 * Foreign key
	 * @var string
	 */
	public $uid;

	/**
	 * @var int
	 */
	public $aantal;

	protected static $persistent_attributes = [
		'peiling_id' => [T::Integer],
		'uid' => [T::UID],
		'aantal' => [T::Integer],
	];

	protected static $primary_key = ['peiling_id', 'uid'];

	protected static $table_name = 'peiling_stemmen';

}
