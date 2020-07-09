<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\service\security\LoginService;
use Doctrine\ORM\Mapping as ORM;

/**
 * RechtenGroep.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een groep beperkt voor rechten.
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\RechtenGroepenRepository")
 * @ORM\Table("groepen", indexes={
 *   @ORM\Index(name="begin_moment", columns={"begin_moment"}),
 *   @ORM\Index(name="familie", columns={"familie"}),
 *   @ORM\Index(name="status", columns={"status"}),
 * })
 */
class RechtenGroep extends AbstractGroep {
	/**
	 * Rechten benodigd voor aanmelden
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $rechten_aanmelden;

	/**
	 * @var RechtenGroepLid[]
	 * @ORM\OneToMany(targetEntity="RechtenGroepLid", mappedBy="groep")
	 * @ORM\OrderBy({"lid_sinds"="ASC"})
	 */
	public $leden;

	public function getLeden() {
		return $this->leden;
	}

	public function getLidType() {
		return RechtenGroepLid::class;
	}

	public function getUrl() {
		return '/groepen/overig/' . $this->id;
	}

	/**
	 * Has permission for action?
	 *
	 * @param AccessAction $action
	 * @param null $allowedAuthenticationMethods
	 * @return boolean
	 */
	public function mag($action, $allowedAuthenticationMethods = null) {
		switch ($action) {
			case AccessAction::Bekijken:
				break;
			case AccessAction::Aanmelden:
			case AccessAction::Bewerken:
			case AccessAction::Afmelden:
				if (!LoginService::mag($this->rechten_aanmelden)) {
					return false;
				}
				break;
		}
		return parent::mag($action, $allowedAuthenticationMethods);
	}

	/**
	 * Rechten voor de gehele klasse of soort groep?
	 *
	 * @param string $action
	 * @param null $allowedAuthenticationMethods
	 * @param null $soort
	 * @return boolean
	 */
	public static function magAlgemeen($action, $allowedAuthenticationMethods = null, $soort = null) {
		switch ($action) {
			case AccessAction::Aanmaken:
			case AccessAction::Aanmelden:
			case AccessAction::Bewerken:
			case AccessAction::Afmelden:
				return true;
		}
		return parent::magAlgemeen($action, $allowedAuthenticationMethods, $soort);
	}

}
