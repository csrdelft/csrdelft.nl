<?php

namespace CsrDelft\view\fiscaat\pin;
use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\pin\PinTransactieMatch;
use CsrDelft\repository\fiscaat\CiviSaldoRepository;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\invoervelden\TextareaField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\keuzevelden\JaNeeField;
use CsrDelft\view\formulier\knoppen\CancelKnop;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 24/02/2018
 */
class PinBestellingCrediterenForm extends ModalForm {
	/**
	 * @param PinTransactieMatch|null $pinTransactieMatch
	 */
	public function __construct($pinTransactieMatch = null) {
		parent::__construct($pinTransactieMatch, '/fiscaat/pin/verwijder', 'Crediteer bestelling', true);
		$fields = [];

		if (!$pinTransactieMatch) {
			$commentOud = '';
			$internOud = '';
			$commentNieuw = '';
		} else {
			$commentOud = $pinTransactieMatch->bestelling->comment ?: 'Teruggedraaid op ' . date_format_intl(date_create_immutable(), DATE_FORMAT);
			$internOud = $pinTransactieMatch->notitie ?: '';
			$commentNieuw = 'Terugdraaien pinbetaling ' . PinTransactieMatch::renderMoment($pinTransactieMatch->bestelling->moment, false);

			$account = ContainerFacade::getContainer()->get(CiviSaldoRepository::class)->getSaldo($pinTransactieMatch->bestelling->uid, true);
			if (!$account) {
				$fields[] = new HtmlComment('Dit account is verwijderd, dus deze bestelling kan niet gecrediteerd worden.');
				$this->addFields($fields);
				$this->formKnoppen = new CancelKnop();
				return;
			}
		}

		$fields[] = new HtmlComment('Er is geen transactie gevonden voor deze bestelling. Met dit formulier maak je een creditbestelling aan waarmee het CiviSaldo teruggedraaid wordt.');
		$fields['commentOud'] = new TextField('commentOud', $commentOud, 'Externe notitie originele bestelling');
		$fields['internOud'] = new TextareaField('internOud', $internOud, 'Interne notitie originele bestelling');
		$fields['commentNieuw'] = new TextField('commentNieuw', $commentNieuw, 'Externe notitie creditbestelling');
		$fields['internNieuw'] = new TextareaField('internNieuw', '', 'Interne notitie creditbestelling');
		$fields['stuurMail'] = new JaNeeField('stuurMail', true, 'Stuur mail naar lid');
		$fields['pinTransactieId'] = new TextField('pinTransactieId', $pinTransactieMatch ? $pinTransactieMatch->id : null, 'Id');
		$fields['pinTransactieId']->hidden = true;

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen(null, false);
	}
}
