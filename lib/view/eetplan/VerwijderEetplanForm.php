<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\common\Util\DateUtil;
use CsrDelft\entity\eetplan\Eetplan;
use CsrDelft\view\formulier\elementen\HtmlBbComment;
use CsrDelft\view\formulier\keuzevelden\required\RequiredSelectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;
use DateTimeInterface;

class VerwijderEetplanForm extends ModalForm
{
	/**
	 * @param Eetplan[] $avonden
	 */
	public function __construct($avonden)
	{
		parent::__construct(null, '/eetplan/verwijderen', 'Eetplan verwijderen');

		$fields = [];
		$fields[] = new HtmlBbComment(
			'[b]Let op, verwijderen van een eetplan kan niet ongedaan gemaakt worden.[/b]'
		);

		$avondenLijst = [];
		foreach ($avonden as $eetplan) {
			$avondenLijst[
				DateUtil::dateFormatIntl($eetplan->avond, DateUtil::DATE_FORMAT)
			] = DateUtil::dateFormatIntl($eetplan->avond, DateUtil::DATE_FORMAT);
		}

		$fields[] = new RequiredSelectField('avond', null, 'Avond', $avondenLijst);

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}
}
