<?php


namespace CsrDelft\entity\corvee;


class CorveeVoorkeurMatrixDTO
{
	/**
	 * @var string
	 */
	public $uid;
	/**
	 * @var integer
	 */
	public $crv_repetitie_id;
	/**
	 * @var bool
	 */
	public $voorkeur;

	public function __construct($profiel, $repetitie, $voorkeur = null)
	{
		$this->uid = $profiel;
		$this->crv_repetitie_id = $repetitie;
		$this->voorkeur = $voorkeur != null;
	}
}
