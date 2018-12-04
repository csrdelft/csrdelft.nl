<?php
/**
 * GesprekDeelnemerToevoegenForm.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */

namespace CsrDelft\view\gesprekken;

use CsrDelft\model\entity\gesprekken\Gesprek;
use CsrDelft\view\formulier\invoervelden\required\RequiredLidField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

class GesprekDeelnemerToevoegenForm extends ModalForm {

	public function __construct(Gesprek $gesprek) {
		parent::__construct(null, '/gesprekken/toevoegen/' . $gesprek->gesprek_id, 'Deelnemer toevoegen', true);

		$fields = [];
		$fields['to'] = new RequiredLidField('to', null, 'Naam of lidnummer');
		$fields['to']->blacklist = array_keys(group_by_distinct('uid', $gesprek->getDeelnemers()));

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen(null, false);
	}

}
