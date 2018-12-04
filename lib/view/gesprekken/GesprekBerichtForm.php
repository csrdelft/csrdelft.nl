<?php
/**
 * GesprekBerichtForm.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */

namespace CsrDelft\view\gesprekken;

use CsrDelft\model\entity\gesprekken\Gesprek;
use CsrDelft\view\formulier\InlineForm;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextareaField;

class GesprekBerichtForm extends InlineForm {

	public function __construct(Gesprek $gesprek, $dataTableId = true) {
		$field = new RequiredTextareaField('inhoud', null, null);
		$field->placeholder = 'Bericht';
		parent::__construct(null, '/gesprekken/zeg/' . $gesprek->gesprek_id, $field, false, false, $dataTableId);
		$this->css_classes[] = 'SubmitReset';
		$this->css_classes[] = 'noanim';
	}

}
