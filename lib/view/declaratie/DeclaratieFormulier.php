<?php

namespace CsrDelft\view\declaratie;

use CsrDelft\model\entity\Declaratie;
use CsrDelft\model\entity\DeclaratieRegel;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\required\RequiredEmailField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\keuzevelden\required\RequiredDateField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\TableField;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 26/04/2019
 */
class DeclaratieFormulier extends Formulier {
	/**
	 * DeclaratieFormulier constructor.
	 * @param Declaratie $model
	 */
	public function __construct($model) {
		parent::__construct($model, '/declaratie/aanmaken', 'Nieuwe declaratie');

		$this->css_classes[] = 'container';

		$fields = [];

		$fields[] = new RequiredTextField('commissie', $model->commissie, 'Commissie of doel');
		$fields[] = new RequiredTextField('naam', $model->naam, 'Naam');
		$fields[] = new RequiredDateField('datum', $model->datum, 'Datum');
		$fields[] = new RequiredEmailField('email', $model->email, 'Email');
		$fields[] = new RequiredTextField('iban', $model->iban, 'IBAN');
		$fields[] = new RequiredTextField('opmerkingen', $model->opmerkingen, 'Opmerkingen');

		$fields[] = new TableField('declaratie_regel', $model->declaratie_regels, DeclaratieRegel::class);

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}
}
