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
 * @ORM\Table("woonoorden", indexes={
 *   @ORM\Index(name="familie", columns={"familie"}),
 *   @ORM\Index(name="status", columns={"status"}),
 *   @ORM\Index(name="begin_moment", columns={"begin_moment"})
 * })
 */
class Woonoord extends AbstractGroep implements HeeftSoort {
	/**
	 * Woonoord / Huis
	 * @var HuisStatus
	 * @ORM\Column(type="enumHuisStatus")
	 * @Serializer\Groups("datatable")
	 */
	public $soort;

	/**
	 * Doet mee met Eetplan
	 * @ORM\Column(type="boolean")
	 * @Serializer\Groups("datatable")
	 */
	public $eetplan;

	/**
	 * @var WoonoordBewoner[]
	 * @ORM\OneToMany(targetEntity="WoonoordBewoner", mappedBy="groep")
	 * @ORM\OrderBy({"lid_sinds"="ASC"})
	 */
	public $leden;

	public function getLeden() {
		return $this->leden;
	}

	public function getLidType() {
		return WoonoordBewoner::class;
	}

	public function getUrl() {
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
	public function mag($action, $soort = null) {
		switch ($action) {

			case AccessAction::Beheren:
			case AccessAction::Wijzigen:
				// Huidige bewoners mogen beheren
				if (LoginService::mag('woonoord:' . $this->familie)) {
					// HuisStatus wijzigen wordt geblokkeerd in GroepForm->validate()
					return true;
				}
				break;
		}
		return parent::mag($action);
	}

	public function getSoort() {
		return $this->soort;
	}

	public function setSoort($soort) {
		$this->soort = $soort;
	}
}
