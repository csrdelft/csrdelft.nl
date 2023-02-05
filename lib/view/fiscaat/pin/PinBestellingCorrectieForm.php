<?php

namespace CsrDelft\view\fiscaat\pin;

use CsrDelft\common\Util\DateUtil;
use CsrDelft\entity\pin\PinTransactieMatch;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\invoervelden\TextareaField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\keuzevelden\JaNeeField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;
use DateTimeInterface;

abstract class PinBestellingCorrectieForm extends ModalForm
{
	protected $actie;
	protected $modalTitel;
	protected $voltooidDeelwoord;
	protected $commentNieuw;
	protected $bestellingType;
	protected $uitleg;

	/**
	 * @param PinTransactieMatch|null $pinTransactieMatch
	 */
	public function __construct($pinTransactieMatch = null)
	{
		parent::__construct(
			$pinTransactieMatch,
			$this->actie,
			$this->modalTitel,
			true
		);
		$fields = [];

		if (!$pinTransactieMatch) {
			$commentOud = '';
			$internOud = '';
			$commentNieuw = '';
		} else {
			$commentOud =
				$pinTransactieMatch->bestelling->comment ?:
				$this->voltooidDeelwoord .
					' op ' .
					DateUtil::dateFormatIntl(
						date_create_immutable(),
						DateUtil::DATE_FORMAT
					);
			$internOud = $pinTransactieMatch->notitie ?: '';
			$commentNieuw =
				$this->commentNieuw .
				' pinbetaling ' .
				PinTransactieMatch::renderMoment(
					$pinTransactieMatch->bestelling->moment,
					false
				);
		}

		$fields[] = new HtmlComment($this->uitleg);
		$fields['commentOud'] = new TextField(
			'commentOud',
			$commentOud,
			'Externe notitie originele bestelling'
		);
		$fields['internOud'] = new TextareaField(
			'internOud',
			$internOud,
			'Interne notitie originele bestelling'
		);
		$fields['commentNieuw'] = new TextField(
			'commentNieuw',
			$commentNieuw,
			'Externe notitie ' . $this->bestellingType
		);
		$fields['internNieuw'] = new TextareaField(
			'internNieuw',
			'',
			'Interne notitie ' . $this->bestellingType
		);
		$fields['stuurMail'] = new JaNeeField(
			'stuurMail',
			true,
			'Stuur mail naar lid'
		);
		$fields['pinTransactieId'] = new TextField(
			'pinTransactieId',
			$pinTransactieMatch ? $pinTransactieMatch->id : null,
			'Id'
		);
		$fields['pinTransactieId']->hidden = true;

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen(null, false);
	}
}
