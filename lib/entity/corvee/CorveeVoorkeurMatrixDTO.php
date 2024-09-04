<?php

namespace CsrDelft\entity\corvee;

class CorveeVoorkeurMatrixDTO
{
	/**
	 * @var bool
	 */
	public $voorkeur;

	/**
	 * @param string $profiel
	 * @param int $repetitie
	 */
	public function __construct(
		public $uid,
		public $crv_repetitie_id,
		$voorkeur = null
	) {
		$this->voorkeur = $voorkeur != null;
	}
}
