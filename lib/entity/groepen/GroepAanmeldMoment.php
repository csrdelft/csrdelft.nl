<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\entity\security\enum\AccessAction;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

trait GroepAanmeldMoment
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
	 * @param AccessAction $action
	 * @return boolean
	 */
	public function magAanmeldMoment($action)
	{
		$nu = date_create_immutable();

		if (
			AccessAction::isAanmelden($action) &&
			($nu > $this->aanmeldenTot || $nu < $this->aanmeldenVanaf)
		) {
			// Controleer aanmeldperiode
			return false;
		} elseif (AccessAction::isBewerken($action) && $nu > $this->bewerkenTot) {
			// Controleer bewerkperiode
			return false;
		} elseif (AccessAction::isAfmelden($action) && $nu > $this->afmeldenTot) {
			// Controleer afmeldperiode
			return false;
		} else {
			return true;
		}
	}
}
