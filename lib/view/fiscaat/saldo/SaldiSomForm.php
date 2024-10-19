<?php

namespace CsrDelft\view\fiscaat\saldo;

use CsrDelft\repository\fiscaat\CiviSaldoRepository;
use CsrDelft\view\formulier\InlineForm;
use CsrDelft\view\formulier\keuzevelden\DateTimeField;
use DateTime;

/**
 * @author J. Rijsdijk <jorairijsdijk@gmail.com>
 * @since 25/10/2017
 *
 * Maakt het mogelijk om een datum en tijd te selecteren en de saldisommen op dat moment op te vragen.
 */
class SaldiSomForm extends InlineForm
{
	public function __construct(
		CiviSaldoRepository $civiSaldoRepository,
		DateTime $date = null
	) {
		$field = new DateTimeField(
			'moment',
			$date instanceof DateTime
				? $date->format('Y-m-d H:i:s')
				: date('Y-m-d H:i:s'),
			'Saldi som op',
			(int) date('Y')
		);

		parent::__construct(
			$civiSaldoRepository,
			'/fiscaat/saldo/som',
			$field,
			true,
			true
		);
	}
}
