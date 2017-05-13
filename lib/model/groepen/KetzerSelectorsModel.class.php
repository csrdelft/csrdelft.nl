<?php
namespace CsrDelft\model\groepen;

use CsrDelft\model\AbstractGroepenModel;
use CsrDelft\model\entity\groepen\Ketzer;
use CsrDelft\model\entity\groepen\KetzerSelector;

class KetzerSelectorsModel extends AbstractGroepenModel {

	const ORM = KetzerSelector::class;

	protected static $instance;

	public function getSelectorsVoorKetzer(Ketzer $ketzer) {
		return $this->prefetch('ketzer_id = ?', array($ketzer->id));
	}

}
