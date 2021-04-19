<?php


namespace CsrDelft\entity\groepen;

use CsrDelft\entity\security\enum\AccessAction;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

trait GroepAanmeldLimiet
{
	/**
	 * Maximaal aantal groepsleden
	 * @var string
	 * @ORM\Column(type="integer", nullable=true)
	 * @Serializer\Groups("datatable")
	 */
	public $aanmeldLimiet;

	public function mag(AccessAction $action, $allowedAuthenticationMethods = null) {
		// Controleer maximum leden
		if (AccessAction::isAanmelden($action)) {
			return !isset($this->aanmeldLimiet) || $this->aantalLeden() < $this->aanmeldLimiet;
		}

		return parent::mag($action, $allowedAuthenticationMethods);
	}
}
