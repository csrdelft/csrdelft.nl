<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\repository\groepen\KringenRepository;
use CsrDelft\repository\groepen\leden\VerticaleLedenRepository;
use CsrDelft\Orm\Entity\T;
use Doctrine\ORM\Mapping as ORM;

/**
 * Verticale.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\VerticalenRepository")
 * @ORM\Table("verticalen")
 */
class Verticale extends AbstractGroep {

	const LEDEN = VerticaleLedenRepository::class;

	/**
	 * Primary key
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $letter;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = [
		'letter' => [T::Char]
	];
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'verticalen';

	public function getUrl() {
		return '/groepen/verticalen/' . $this->letter;
	}

	public function getKringen() {
		return ContainerFacade::getContainer()->get(KringenRepository::class)->getKringenVoorVerticale($this);
	}

	/**
	 * Limit functionality: leden generated
	 * @param string $action
	 * @param null $allowedAuthenticationMethods
	 * @return bool
	 */
	public function mag($action, $allowedAuthenticationMethods = null) {
		switch ($action) {

			case AccessAction::Bekijken:
			case AccessAction::Aanmaken:
			case AccessAction::Wijzigen:
				return parent::mag($action, $allowedAuthenticationMethods);
		}
		return false;
	}

	/**
	 * Limit functionality: leden generated
	 * @param string $action
	 * @param null $allowedAuthenticationMethods
	 * @return bool
	 */
	public static function magAlgemeen($action, $allowedAuthenticationMethods = null) {
		switch ($action) {

			case AccessAction::Bekijken:
			case AccessAction::Aanmaken:
			case AccessAction::Wijzigen:
				return parent::magAlgemeen($action, $allowedAuthenticationMethods);
		}
		return false;
	}

}
