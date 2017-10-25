<?php

namespace CsrDelft\model\entity;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * Class GoogleToken.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class GoogleToken extends PersistentEntity {
	public $uid;
	public $token;

	protected static $persistent_attributes = [
		'uid' => [T::UID],
		'token' => [T::String],
	];
	protected static $table_name = 'GoogleToken';
	protected static $primary_key = ['uid'];
}
