<?php

class KetzerOptiesModel extends AbstractGroepenModel {

	const ORM = KetzerOptie::class;

	protected static $instance;

	public function getOptiesVoorSelect(KetzerSelector $select) {
		return $this->prefetch('select_id = ?', array($select->select_id));
	}

}
