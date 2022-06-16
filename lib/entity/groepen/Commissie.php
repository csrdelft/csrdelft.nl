<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\Enum;
use CsrDelft\entity\groepen\enum\CommissieSoort;
use CsrDelft\entity\groepen\interfaces\HeeftMoment;
use CsrDelft\entity\groepen\interfaces\HeeftSoort;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\service\security\LoginService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Commissie.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een commissie is een groep waarvan de groepsleden een specifieke functie (kunnen) hebben.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\CommissiesRepository")
 */
class Commissie extends Groep implements HeeftSoort, HeeftMoment
{
	use GroepMoment;
	/**
	 * (Bestuurs-)Commissie / SjaarCie
	 * @var CommissieSoort
	 * @ORM\Column(type="enumCommissieSoort")
	 * @Serializer\Groups("datatable")
	 */
	public $commissieSoort;

	public function getUrl()
	{
		return '/groepen/commissies/' . $this->id;
	}

	/**
	 * Rechten voor de gehele klasse of soort groep?
	 *
	 * @param AccessAction $action
	 * @param Enum $soort
	 * @return boolean
	 */
	public static function magAlgemeen($action, $soort = null)
	{
		switch ($soort) {
			case CommissieSoort::SjaarCie():
				if (LoginService::mag('commissie:NovCie')) {
					return true;
				}
				break;
		}
		return parent::magAlgemeen($action, $soort);
	}

	public function getSoort()
	{
		return $this->commissieSoort;
	}

	public function setSoort($soort)
	{
		$this->commissieSoort = $soort;
	}
}
