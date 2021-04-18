<?php


namespace CsrDelft\entity\groepen;


use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\service\security\LoginService;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

trait GroepAanmeldRechten
{
	/**
	 * Rechten benodigd voor aanmelden
	 * @var string|null
	 * @ORM\Column(type="string", nullable=true)
	 * @Serializer\Groups("datatable")
	 */
	public $rechtenAanmelden;

	/**
	 * Has permission for action?
	 *
	 * @param string $action
	 * @param null $allowedAuthenticationMethods
	 * @return boolean
	 */
	public function mag($action, $allowedAuthenticationMethods = null) {
		if ($action == AccessAction::Aanmelden || $action == AccessAction::Bewerken || $action == AccessAction::Afmelden) {
			if (!LoginService::mag($this->rechtenAanmelden)) {
				return false;
			}
		}
		return parent::mag($action, $allowedAuthenticationMethods);
	}
}
