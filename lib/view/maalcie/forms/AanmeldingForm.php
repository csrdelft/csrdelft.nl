<?php

namespace CsrDelft\view\maalcie\forms;

use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\maalcie\MaaltijdAanmeldingDTO;
use CsrDelft\view\formulier\getalvelden\required\RequiredIntField;
use CsrDelft\view\formulier\invoervelden\required\RequiredLidField;
use CsrDelft\view\formulier\invoervelden\required\RequiredProfielEntityField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * AanmeldingForm.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier voor een nieuwe of te verwijderen maaltijd-aanmelding.
 *
 */
class AanmeldingForm extends ModalForm
{
	/**
	 * AanmeldingForm constructor.
	 * @param MaaltijdAanmeldingDTO $aanmeldingDTO
	 * @param boolean $nieuw
	 */
	public function __construct(MaaltijdAanmeldingDTO $aanmeldingDTO, bool $nieuw)
	{
		parent::__construct(
			$aanmeldingDTO,
			'/maaltijden/beheer/' . ($nieuw ? 'aanmelden' : 'afmelden'),
			true,
			true
		);

		if ($nieuw) {
			$this->titel = 'Aanmelding toevoegen/aanpassen';
		} else {
			$this->titel = 'Aanmelding verwijderen (inclusief gasten)';
		}
		$this->css_classes[] = 'PreventUnchanged';

		$fields = [];
		$fields[] = new RequiredProfielEntityField(
			'voor_lid',
			$aanmeldingDTO->voor_lid,
			'Naam of lidnummer',
			'leden'
		);
		if ($nieuw) {
			$fields[] = new RequiredIntField(
				'aantal_gasten',
				$aanmeldingDTO->aantal_gasten,
				'Aantal gasten',
				0,
				200
			);
		}

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}
}
