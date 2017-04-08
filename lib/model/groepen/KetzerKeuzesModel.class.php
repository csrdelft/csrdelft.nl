<?php

class KetzerKeuzesModel extends AbstractGroepenModel {

	const ORM = KetzerKeuze::class;

	protected static $instance;

	public function getKeuzesVoorOptie(KetzerOptie $optie) {
		return $this->prefetch('optie_id = ?', array($optie->optie_id));
	}

}
