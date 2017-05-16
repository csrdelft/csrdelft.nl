<?php
namespace CsrDelft\view\maalcie\forms;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\keuzevelden\DateField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * BoekjaarSluitenForm.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier voor het sluiten van het MaalCie-boekjaar.
 *
 */
class BoekjaarSluitenForm extends ModalForm {

	public function __construct($beginDatum = null, $eindDatum = null) {
		parent::__construct(null, maalcieUrl . '/sluitboekjaar');
		$this->titel = 'Boekjaar sluiten';

		$fields[] = new HtmlComment('<p class="error">Dit is een onomkeerbare stap!</p>');
		$fields['begin'] = new DateField('begindatum', $beginDatum, 'Vanaf', date('Y') + 1, date('Y') - 2);
		$fields['eind'] = new DateField('einddatum', $eindDatum, 'Tot en met', date('Y') + 1, date('Y') - 2);
		$fields[] = new FormDefaultKnoppen();

		$this->addFields($fields);
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
