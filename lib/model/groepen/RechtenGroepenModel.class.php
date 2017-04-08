<?php

class RechtenGroepenModel extends AbstractGroepenModel {

	const ORM = RechtenGroep::class;

	protected static $instance;

	public function nieuw() {
		$groep = parent::nieuw();
		$groep->rechten_aanmelden = 'P_LOGGED_IN';
		return $groep;
	}

}
