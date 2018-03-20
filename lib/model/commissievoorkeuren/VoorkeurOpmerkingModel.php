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
		$result = $this->retrieveByPrimaryKey([$profiel->uid]);
		return $result === false ? new VoorkeurOpmerking() : $result;
	}


}
