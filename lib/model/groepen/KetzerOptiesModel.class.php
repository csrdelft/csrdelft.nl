<?php

namespace CsrDelft\model\groepen;

use CsrDelft\model\AbstractGroepenModel;
use CsrDelft\model\entity\groepen\KetzerOptie;
use CsrDelft\model\entity\groepen\KetzerSelector;

class KetzerOptiesModel extends AbstractGroepenModel {

	const ORM = KetzerOptie::class;

	public function getOptiesVoorSelect(KetzerSelector $select) {
		return $this->prefetch('select_id = ?', array($select->select_id));
	}

}
