<?php

namespace CsrDelft\model\entity\documenten;

use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * Class DocumentCategorie.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class DocumentCategorie extends PersistentEntity {
	public $id;
	public $naam;
	public $zichtbaar;
	public $leesrechten;

	public function magBekijken() {
		return LoginModel::mag($this->leesrechten);
	}

	protected static $persistent_attributes = [
		'id' => [T::Integer, false, 'auto_increment'],
		'naam' => [T::String],
		'zichtbaar' => [T::Boolean],
		'leesrechten' => [T::String],
	];
	protected static $table_name = 'DocumentCategorie';
	protected static $primary_key = ['id'];
}
