<?php

namespace CsrDelft\view\fiscaat\pin;

use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 24/02/2018
 */
class PinBestellingVeranderenForm extends ModalForm {
	public function __construct($model) {
		parent::__construct($model, '/fiscaat/pin/update', 'Update bestelling.', true);

		$fields[] = new HtmlComment('Het bedrag van de bestelling is niet correct. Druk op opslaan om de bestelling te veranderen naar het goede bedrag.');
		$fields['id'] = new TextField('id', $model->id, 'id');
		$fields['id']->hidden = true;

		$fields['btn'] = new FormDefaultKnoppen(null, false);

		$this->addFields($fields);
	}
}
