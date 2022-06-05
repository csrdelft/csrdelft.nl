<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\groepen\enum\HuisStatus;
use CsrDelft\entity\groepen\interfaces\HeeftSoort;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\service\security\LoginService;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Woonoord.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een woonoord is waar C.S.R.-ers bij elkaar wonen.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\WoonoordenRepository")
 */
class Woonoord extends Groep implements HeeftSoort
{
	use GroepMoment;

	/**
	 * Woonoord / Huis
	 * @var HuisStatus
	 * @ORM\Column(type="enumHuisStatus")
	 * @Serializer\Groups("datatable")
	 */
	public $huisStatus;

	/**
	 * Doet mee met Eetplan
	 * @ORM\Column(type="boolean")
	 * @Serializer\Groups("datatable")
	 */
	public $eetplan;

	public function getUrl()
	{
		return '/groepen/woonoorden/' . $this->id;
	}

	/**
	 * Has permission for action?
	 *
	 * @param AccessAction $action
	 * @param string $soort
	 *
	 * @return boolean
	 */
	public function mag($action, $soort = null)
	{
		switch ($action) {
			case AccessAction::Beheren():
			case AccessAction::Wijzigen():
				// Huidige bewoners mogen beheren
				if (LoginService::mag('woonoord:' . $this->familie)) {
					// HuisStatus wijzigen wordt geblokkeerd in GroepForm->validate()
					return true;
				}
				break;
		}
		return parent::mag($action);
	}

	public function getSoort()
	{
		return $this->huisStatus;
	}

	public function setSoort($soort)
	{
		$this->huisStatus = $soort;
	}
}
