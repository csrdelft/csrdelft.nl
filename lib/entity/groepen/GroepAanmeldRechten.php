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
	 * @param AccessAction $action
	 * @param null $allowedAuthenticationMethods
	 * @return boolean
	 */
	public function mag($action, $allowedAuthenticationMethods = null)
	{
		$beschermdeActies = [
			AccessAction::Bekijken(),
			AccessAction::Aanmelden(),
			AccessAction::Bewerken(),
			AccessAction::Afmelden(),
		];

		if (in_array($action, $beschermdeActies) && !LoginService::mag($this->rechtenAanmelden)) {
			return false;
		}

		return parent::mag($action, $allowedAuthenticationMethods);
	}
}
