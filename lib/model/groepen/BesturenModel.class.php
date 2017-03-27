<?php

class BesturenModel extends AbstractGroepenModel {

	const ORM = Bestuur::class;

	protected static $instance;

	public function nieuw() {
		$bestuur = parent::nieuw();
		$bestuur->bijbeltekst = '';
		return $bestuur;
	}

}
