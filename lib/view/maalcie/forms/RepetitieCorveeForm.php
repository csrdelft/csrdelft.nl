<?php

namespace CsrDelft\view\maalcie\forms;

use CsrDelft\entity\corvee\CorveeRepetitie;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\getalvelden\IntField;
use CsrDelft\view\formulier\keuzevelden\DateObjectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * RepetitieCorveeForm.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier voor nieuw periodiek corvee.
 *
 */
class RepetitieCorveeForm extends ModalForm
{
	public function __construct(
		CorveeRepetitie $repetitie,
		$beginDatum = null,
		$eindDatum = null,
		$mid = null
	) {
		parent::__construct(
			null,
			'/corvee/beheer/aanmaken/' . $repetitie->crv_repetitie_id
		);
		$this->titel = 'Periodiek corvee aanmaken';

		$fields = [];
		$fields[] = new HtmlComment(
			'<p>Aanmaken <span class="dikgedrukt">' .
				$repetitie->getPeriodeInDagenText() .
				'</span> op <span class="dikgedrukt">' .
				$repetitie->getDagVanDeWeekText() .
				'</span> in de periode:</p>'
		);
		$fields['begin'] = new DateObjectField(
			'begindatum',
			$beginDatum,
			'Vanaf',
			date('Y') + 1,
			date('Y')
		);
		$fields['eind'] = new DateObjectField(
			'einddatum',
			$eindDatum,
			'Tot en met',
			date('Y') + 1,
			date('Y')
		);
		$fields['mid'] = new IntField('maaltijd_id', $mid, null);
		$fields['mid']->readonly = true;
		$fields['mid']->hidden = true;

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}

	public function validate(): bool
	{
		$valid = parent::validate();
		$fields = $this->getFields();
		if (
			strtotime($fields['eind']->getValue()) <
			strtotime($fields['begin']->getValue())
		) {
			$fields['eind']->error = 'Moet na begindatum liggen';
			$valid = false;
		}
		return $valid;
	}
}
