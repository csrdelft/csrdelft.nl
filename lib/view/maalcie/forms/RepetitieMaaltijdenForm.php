<?php

namespace CsrDelft\view\maalcie\forms;

use CsrDelft\entity\maalcie\RepetitieMaaltijdMaken;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\keuzevelden\DateObjectField;
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
class RepetitieMaaltijdenForm extends ModalForm
{
	public function __construct(RepetitieMaaltijdMaken $repetitie)
	{
		parent::__construct(
			$repetitie,
			'/maaltijden/beheer/aanmaken/' . $repetitie->mlt_repetitie_id
		);
		$this->titel = 'Periodieke maaltijden aanmaken';

		$fields = [];
		$fields[] = new HtmlComment(
			'<p>Aanmaken <span class="dikgedrukt">' .
				$repetitie->periode .
				'</span> op <span class="dikgedrukt">' .
				$repetitie->dag .
				'</span> in de periode:</p>'
		);
		$fields['begin'] = new DateObjectField(
			'begin_moment',
			$repetitie->begin_moment,
			'Vanaf',
			date('Y') + 1,
			date('Y')
		);
		$fields['eind'] = new DateObjectField(
			'eind_moment',
			$repetitie->eind_moment,
			'Tot en met',
			date('Y') + 1,
			date('Y')
		);

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}

	public function validate()
	{
		$valid = parent::validate();
		$fields = $this->getFields();
		if (
			strtotime((string) $fields['eind']->getValue()) <
			strtotime((string) $fields['begin']->getValue())
		) {
			$fields['eind']->error = 'Moet na begindatum liggen';
			$valid = false;
		}
		return $valid;
	}
}
