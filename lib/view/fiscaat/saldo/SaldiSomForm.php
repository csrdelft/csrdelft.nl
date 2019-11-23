<?php

namespace CsrDelft\view\fiscaat\saldo;

use CsrDelft\model\fiscaat\CiviSaldoModel;
use CsrDelft\view\formulier\InlineForm;
use CsrDelft\view\formulier\keuzevelden\DateTimeField;
use DateTime;

/**
 * @author J. Rijsdijk <jorairijsdijk@gmail.com>
 * @date 25/10/2017
 *
 * Maakt het mogelijk om een datum en tijd te selecteren en de saldisommen op dat moment op te vragen.
 */
class SaldiSomForm extends InlineForm {
	public function __construct(CiviSaldoModel $model, DateTime $date = null) {
		$field = new DateTimeField("moment", $date ? $date->format("Y-m-d H:i:s") : date("Y-m-d H:i:s"), "Saldi som op", (int)date("Y"));

		parent::__construct($model, '/fiscaat/saldo/som', $field, true, true);
	}
}
