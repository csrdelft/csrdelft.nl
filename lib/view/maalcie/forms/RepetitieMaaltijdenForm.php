<?php

namespace CsrDelft\view\maalcie\forms;

use CsrDelft\model\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\keuzevelden\DateField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * RepetitieMaaltijdenForm.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier voor nieuwe periodieke maaltijden.
 *
 */
class RepetitieMaaltijdenForm extends ModalForm {

	public function __construct(MaaltijdRepetitie $repetitie, $beginDatum = null, $eindDatum = null) {
		parent::__construct(null, maalcieUrl . '/aanmaken/' . $repetitie->mlt_repetitie_id);
		$this->titel = 'Periodieke maaltijden aanmaken';

		$fields = [];
		$fields[] = new HtmlComment('<p>Aanmaken <span class="dikgedrukt">' . $repetitie->getPeriodeInDagenText() . '</span> op <span class="dikgedrukt">' . $repetitie->getDagVanDeWeekText() . '</span> in de periode:</p>');
		$fields['begin'] = new DateField('begindatum', $beginDatum, 'Vanaf', date('Y') + 1, date('Y'));
		$fields['eind'] = new DateField('einddatum', $eindDatum, 'Tot en met', date('Y') + 1, date('Y'));

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}

	public function validate() {
		$valid = parent::validate();
		$fields = $this->getFields();
		if (strtotime($fields['eind']->getValue()) < strtotime($fields['begin']->getValue())) {
			$fields['eind']->error = 'Moet na begindatum liggen';
			$valid = false;
		}
		return $valid;
	}

}
