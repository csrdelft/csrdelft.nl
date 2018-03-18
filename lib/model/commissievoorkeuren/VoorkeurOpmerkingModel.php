<?php

namespace CsrDelft\model\commissievoorkeuren;

use CsrDelft\model\entity\commissievoorkeuren\VoorkeurOpmerking;
use CsrDelft\model\entity\Profiel;
use CsrDelft\Orm\PersistenceModel;

class VoorkeurOpmerkingModel extends PersistenceModel
{

	const ORM = VoorkeurOpmerking::class;

	/**
	 * @param Profiel $profiel
	 * @return VoorkeurOpmerking
	 */
	public function getOpmerkingVoorLid(Profiel $profiel)
	{
		return $this->retrieveByUUID($profiel->uid);
	}

	/**
	 * @param VoorkeurOpmerking $opmerking
	 * @param string $text
	 */
	public function setLidOpmerking(VoorkeurOpmerking $opmerking, string $text)
	{
		$opmerking->lidOpmerking = $text;
		$this->update($opmerking);
	}

	/**
	 * @param VoorkeurOpmerking $opmerking
	 * @param string $text
	 */
	public function setPraesesOpmerking(VoorkeurOpmerking $opmerking, string $text)
	{
		$opmerking->praesesOpmerking = $text;
		$this->update($opmerking);
	}

}
