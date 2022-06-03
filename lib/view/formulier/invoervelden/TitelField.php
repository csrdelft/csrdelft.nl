<?php

namespace CsrDelft\view\formulier\invoervelden;

use CsrDelft\common\ContainerFacade;
use CsrDelft\repository\bibliotheek\BoekRepository;

/**
 */
class TitelField extends TextField
{
	public $required = true;
	private $nieuw;

	public function __construct($name, $value, $description, $nieuw, $max_len = 255, $min_len = 0, $model = null)
	{
		parent::__construct($name, $value, $description, $max_len, $min_len, $model);
		$this->nieuw = $nieuw;
	}

	public function validate()
	{
		if (!parent::validate()) {
			return false;
		}
		$boekRepository = ContainerFacade::getContainer()->get(BoekRepository::class);
		if ($this->nieuw && $boekRepository->existsTitel($this->value)) {
			$this->error = 'Titel bestaat al.';
		}
		return $this->error == '';
	}

}
