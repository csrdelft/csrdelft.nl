<?php


namespace CsrDelft\entity\groepen;


use CsrDelft\entity\security\enum\AccessAction;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

trait GroepAanmelden
{
	/**
	 * Datum en tijd aanmeldperiode begin
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 * @Serializer\Groups("datatable")
	 */
	public $aanmeldenVanaf;
	/**
	 * Datum en tijd aanmeldperiode einde
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 * @Serializer\Groups({"datatable", "vue"})
	 */
	public $aanmeldenTot;
	/**
	 * Datum en tijd aanmelding bewerken toegestaan
	 * @var DateTimeImmutable|null
	 * @ORM\Column(type="datetime", nullable=true)
	 * @Serializer\Groups("datatable")
	 */
	public $bewerkenTot;
	/**
	 * Datum en tijd afmelden toegestaan
	 * @var DateTimeImmutable|null
	 * @ORM\Column(type="datetime", nullable=true)
	 * @Serializer\Groups("datatable")
	 */
	public $afmeldenTot;

	/**
	 * Controleer of aanmeldLimiet en bewerken/afmelden tot gehaald wordt.
	 *
	 * @param string $action
	 * @param null $allowedAuthenticationMethods
	 * @return boolean
	 */
	public function mag($action, $allowedAuthenticationMethods = null) {
		$nu = date_create_immutable();

		switch ($action) {
			case AccessAction::Aanmelden:
				// Controleer aanmeldperiode
				return $nu <= $this->aanmeldenTot && $nu >= $this->aanmeldenVanaf;

			case AccessAction::Bewerken:
				// Controleer bewerkperiode
				return $nu <= $this->bewerkenTot;

			case AccessAction::Afmelden:
				// Controleer afmeldperiode
				return $nu <= $this->afmeldenTot;
		}

		return parent::mag($action, $allowedAuthenticationMethods);
	}
}
