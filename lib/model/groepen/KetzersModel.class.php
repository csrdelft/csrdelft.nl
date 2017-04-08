<?php

class KetzersModel extends AbstractGroepenModel {

	const ORM = Ketzer::class;

	protected static $instance;

	public function nieuw() {
		$ketzer = parent::nieuw();
		$ketzer->aanmeld_limiet = null;
		$ketzer->aanmelden_vanaf = getDateTime();
		$ketzer->aanmelden_tot = null;
		$ketzer->bewerken_tot = null;
		$ketzer->afmelden_tot = null;
		return $ketzer;
	}

}
