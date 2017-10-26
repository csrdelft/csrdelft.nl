<?php

namespace CsrDelft\view\fiscaat\saldo;

use CsrDelft\model\fiscaat\CiviSaldoModel;
use CsrDelft\view\formulier\InlineForm;
use CsrDelft\view\formulier\keuzevelden\DateTimeField;
use DateTime;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 *
 * Maak het mogelijk om een lid te registreren, wordt uiteindelijk samengetrokken met het aanmaken van een lid.
 */
class SaldiSomForm extends InlineForm {
	public function __construct(CiviSaldoModel $model, DateTime $date = null) {
		$field = new DateTimeField("moment", $date ? $date->format("Y-m-d G:i:s") : date("Y-m-d G:i:s"), "Saldi som op", (int)date("Y"));

		parent::__construct($model, '/fiscaat/saldo/som', $field, true, true);
	}
}
