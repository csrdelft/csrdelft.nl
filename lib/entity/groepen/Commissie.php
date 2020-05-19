<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\groepen\interfaces\HeeftSoort;
use CsrDelft\model\entity\security\AccessAction;
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
 * @ORM\Table("commissies")
 */
class Commissie extends AbstractGroep implements HeeftSoort {
	public function __construct() {
		$this->leden = new ArrayCollection();
	}

	/**
	 * @var CommissieLid[]
	 * @ORM\OneToMany(targetEntity="CommissieLid", mappedBy="groep")
	 */
	public $leden;

	public function getLeden() {
		return $this->leden;
	}

	public function getLidType() {
		return CommissieLid::class;
	}

	/**
	 * (Bestuurs-)Commissie / SjaarCie
	 * @var CommissieSoort
	 * @ORM\Column(type="string")
	 * @Serializer\Groups("datatable")
	 */
	public $soort;

	public function getUrl() {
		return '/groepen/commissies/' . $this->id;
	}

	/**
	 * Rechten voor de gehele klasse of soort groep?
	 *
	 * @param AccessAction $action
	 * @param null $allowedAuthenticationMethods
	 * @param string $soort
	 * @return boolean
	 */
	public static function magAlgemeen($action, $allowedAuthenticationMethods=null, $soort = null) {
		switch ($soort) {

			case CommissieSoort::SjaarCie():
				if (LoginService::mag('commissie:NovCie')) {
					return true;
				}
				break;
		}
		return parent::magAlgemeen($action, $allowedAuthenticationMethods, $soort);
	}

	public function getSoort() {
		return $this->soort;
	}

	public function setSoort($soort) {
		$this->soort = $soort;
	}
}
