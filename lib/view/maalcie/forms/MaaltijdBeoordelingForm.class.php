<?php

/**
 * MaaltijdBeoordelingForm.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier voor het invoeren van een beoordeling van een maaltijd met sterren.
 * 
 */
class MaaltijdKwantiteitBeoordelingForm extends InlineForm {

	public function __construct(MaaltijdBeoordeling $b) {

		$field = new SterrenField('kwanti', $b->kwantiteit, null, 1, 4, false, $b->kwantiteit !== null);
		$field->hints = array('ruim onvoldoende', 'onvoldoende', 'voldoende', 'ruim voldoende');
		$field->click_submit = true;

		parent::__construct(null, maalcieUrl . '/beoordeling/' . $b->maaltijd_id, $field, false);
		$this->css_classes[] = 'confirm noanim';
	}

}

class MaaltijdKwaliteitBeoordelingForm extends InlineForm {

	public function __construct(MaaltijdBeoordeling $b) {

		$field = new SterrenField('kwali', $b->kwaliteit, null, 1, 4, false, $b->kwaliteit !== null);
		$field->hints = array('ruim onvoldoende', 'onvoldoende', 'voldoende', 'ruim voldoende');
		$field->click_submit = true;

		parent::__construct(null, maalcieUrl . '/beoordeling/' . $b->maaltijd_id, $field, false);
		$this->css_classes[] = 'confirm noanim';
	}

}
