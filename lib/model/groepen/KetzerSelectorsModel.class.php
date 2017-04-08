<?php

class KetzerSelectorsModel extends AbstractGroepenModel {

	const ORM = KetzerSelector::class;

	protected static $instance;

	public function getSelectorsVoorKetzer(Ketzer $ketzer) {
		return $this->prefetch('ketzer_id = ?', array($ketzer->id));
	}

}
