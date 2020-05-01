<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\Enum;
use CsrDelft\model\entity\interfaces\HeeftSoort;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\repository\groepen\leden\BewonersRepository;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\T;
use Doctrine\ORM\Mapping as ORM;


/**
 * Woonoord.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een woonoord is waar C.S.R.-ers bij elkaar wonen.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\WoonoordenRepository")
 * @ORM\Table("woonoorden")
 */
class Woonoord extends AbstractGroep implements HeeftSoort {

	const LEDEN = BewonersRepository::class;

	/**
	 * Woonoord / Huis
	 * @var HuisStatus
	 * @ORM\Column(type="enumhuisstatus")
	 */
	public $soort;

	/**
	 * Doet mee met Eetplan
	 * @ORM\Column(type="boolean")
	 */
	public $eetplan;

	/**
	 * @var Bewoner[]
	 * @ORM\OneToMany(targetEntity="Bewoner", mappedBy="groep")
	 */
	public $leden;

	public function getLeden() {
		return $this->leden;
	}

	public function getLidType() {
		return Bewoner::class;
	}

	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = [
		'soort' => [T::Enumeration, false, HuisStatus::class],
		'eetplan' => [T::Boolean]
	];

	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'woonoorden';

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
				if (LoginModel::mag('woonoord:' . $this->familie)) {
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
