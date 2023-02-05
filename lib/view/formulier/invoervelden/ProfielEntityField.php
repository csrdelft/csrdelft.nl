<?php

namespace CsrDelft\view\formulier\invoervelden;

use CsrDelft\common\Util\ArrayUtil;
use CsrDelft\entity\profiel\Profiel;

class ProfielEntityField extends DoctrineEntityField
{
	public function __construct($name, $value, $description, $zoekin)
	{
		parent::__construct(
			$name,
			$value,
			$description,
			Profiel::class,
			'/tools/naamsuggesties?zoekin=' . $zoekin . '&q='
		);

		$this->suggestieIdField = 'uid';
	}

	public function validate()
	{
		if (
			is_array($this->blacklist) &&
			ArrayUtil::in_array_i($this->value, $this->blacklist)
		) {
			$this->error =
				'Dit profiel mag niet gekozen worden: ' .
				htmlspecialchars($this->getFormattedValue()->getNaam());
			return false;
		}

		return parent::validate();
	}
}
