<?php

namespace CsrDelft\entity\commissievoorkeuren;

use Doctrine\Common\Collections\ArrayCollection;

class CommissieVoorkeuren
{
	/**
	 * @var VoorkeurVoorkeur[]|ArrayCollection
	 */
	public $voorkeuren;
	/**
	 * @var string
	 */
	public $opmerking = null;

	public function __construct()
	{
		$this->voorkeuren = new ArrayCollection();
	}

	public function getOpmerking()
	{
		return $this->opmerking;
	}

	public function getVoorkeuren()
	{
		return $this->voorkeuren;
	}

}
