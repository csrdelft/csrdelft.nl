<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\common\Util\FlashUtil;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\keuzevelden\required\RequiredDateField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

class NieuwEetplanForm extends ModalForm
{
	public function __construct()
	{
		parent::__construct(null, '/eetplan/nieuw', 'Nieuw eetplan toevoegen');

		$fields[] = new HtmlComment(FlashUtil::getFlashUsingContainerFacade());
		$fields[] = new RequiredDateField(
			'avond',
			date(DATE_ISO8601),
			'Avond',
			(int) date('Y') + 1,
			(int) date('Y') - 1
		);

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}
}
