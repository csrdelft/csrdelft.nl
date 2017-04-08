<?php

class CommissiesModel extends AbstractGroepenModel {

	const ORM = Commissie::class;

	protected static $instance;

	public function nieuw($soort = null) {
		if (!in_array($soort, CommissieSoort::getTypeOptions())) {
			$soort = CommissieSoort::Commissie;
		}
		$commissie = parent::nieuw();
		$commissie->soort = $soort;
		return $commissie;
	}

}
