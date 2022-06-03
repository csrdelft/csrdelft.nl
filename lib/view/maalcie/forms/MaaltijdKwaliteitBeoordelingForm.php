<?php

namespace CsrDelft\view\maalcie\forms;

use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\maalcie\MaaltijdBeoordeling;
use CsrDelft\view\formulier\InlineForm;
use CsrDelft\view\formulier\keuzevelden\SterrenField;


class MaaltijdKwaliteitBeoordelingForm extends InlineForm {

	public function __construct(Maaltijd $maaltijd, MaaltijdBeoordeling $beoordeling) {

		$field = new SterrenField('kwaliteit', $beoordeling->kwaliteit, null, 4);
		$field->hints = array('ruim onvoldoende', 'onvoldoende', 'voldoende', 'ruim voldoende');
		$field->click_submit = true;
		$field->readonly = $maaltijd->getBeginMoment() < strtotime(instelling('maaltijden', 'beoordeling_periode'));

		parent::__construct($beoordeling, '/maaltijden/ketzer/beoordeling/' . $beoordeling->maaltijd_id, $field, false);
		$this->css_classes[] = 'noanim';
	}

}
