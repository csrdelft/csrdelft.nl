<?php

namespace CsrDelft\model\groepen;

use CsrDelft\model\AbstractGroepenModel;
use CsrDelft\model\entity\groepen\KetzerKeuze;
use CsrDelft\model\entity\groepen\KetzerOptie;

class KetzerKeuzesModel extends AbstractGroepenModel {

	const ORM = KetzerKeuze::class;

	public function getKeuzesVoorOptie(KetzerOptie $optie) {
		return $this->prefetch('optie_id = ?', array($optie->optie_id));
	}

}
