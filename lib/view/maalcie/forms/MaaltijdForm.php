<?php

namespace CsrDelft\view\maalcie\forms;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\fiscaat\CiviProduct;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\view\formulier\getalvelden\required\RequiredIntField;
use CsrDelft\view\formulier\invoervelden\ProsemirrorField;
use CsrDelft\view\formulier\invoervelden\DoctrineEntityField;
use CsrDelft\view\formulier\invoervelden\RechtenField;
use CsrDelft\view\formulier\invoervelden\required\RequiredDoctrineEntityField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\keuzevelden\required\RequiredDateField;
use CsrDelft\view\formulier\keuzevelden\required\RequiredTimeField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\knoppen\FormulierKnop;
use CsrDelft\view\formulier\ModalForm;

/**
 * MaaltijdForm.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier voor een nieuwe of te bewerken maaltijd.
 *
 */
class MaaltijdForm extends ModalForm {

	/**
	 * MaaltijdForm constructor.
	 *
	 * @param Maaltijd $maaltijd
	 * @param string $action
	 *
	 * @throws CsrGebruikerException
	 */
	public function __construct(Maaltijd $maaltijd, $action) {
		parent::__construct($maaltijd, '/maaltijden/beheer/' . $action, false, true);

		if ($maaltijd->maaltijd_id < 0) {
			throw new CsrGebruikerException(sprintf('Ongeldig maaltijd id "%s".', $maaltijd->maaltijd_id));
		}
		if ($maaltijd->maaltijd_id == 0) {
			$this->titel = 'Maaltijd aanmaken';
		} else {
			$this->titel = 'Maaltijd wijzigen';
			$this->css_classes[] = 'PreventUnchanged';
		}

		$fields = [];
		$fields['mrid'] = new DoctrineEntityField('repetitie', $maaltijd->repetitie, null, MaaltijdRepetitie::class, '');
		$fields['mrid']->hidden = true;
		$fields[] = new RequiredTextField('titel', $maaltijd->titel, 'Titel', 255, 5);
		$fields[] = new RequiredDateField('datum', $maaltijd->datum, 'Datum', date('Y') + 2, date('Y') - 2);
		$fields[] = new RequiredTimeField('tijd', $maaltijd->tijd, 'Tijd', 15);
		$fields[] = new RequiredDoctrineEntityField('product', $maaltijd->product, 'Product', CiviProduct::class, '/fiscaat/producten/suggesties?q=');
		$fields[] = new FormulierKnop('/fiscaat/producten', 'redirect', 'Nieuw product', 'Nieuw product aanmaken', '');
		$fields[] = new RequiredIntField('aanmeld_limiet', $maaltijd->aanmeld_limiet, 'Aanmeldlimiet', 0, 200);
		$fields[] = new RechtenField('aanmeld_filter', $maaltijd->aanmeld_filter, 'Aanmeldrestrictie');
		$fields[] = new ProsemirrorField('omschrijving', $maaltijd->omschrijving, 'Omschrijving');

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}

}
