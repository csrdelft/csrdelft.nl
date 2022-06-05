<?php

namespace CsrDelft\view\roodschopper;

use CsrDelft\service\Roodschopper;
use CsrDelft\view\formulier\elementen\HtmlBbComment;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\getalvelden\required\RequiredBedragField;
use CsrDelft\view\formulier\invoervelden\EmailField;
use CsrDelft\view\formulier\invoervelden\required\RequiredEmailField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextareaField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\keuzevelden\CheckboxField;
use CsrDelft\view\formulier\keuzevelden\SelectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 11/04/2019
 */
class RoodschopperForm extends Formulier
{
	/**
	 * RoodschopperForm constructor.
	 * @param Roodschopper $model
	 */
	public function __construct($model)
	{
		parent::__construct($model, '/tools/roodschopper', 'Roodschopper');

		$fields = [];

		$fields[] = new HtmlBbComment(
			'Je kunt op Opslaan blijven klikken om het resultaat beneden te zien, de mails worden pas verstuurd als het Verzenden vinkje is gezet.'
		);
		$fields['from'] = new RequiredEmailField(
			'from',
			$model->from,
			'Afzenderadres'
		);
		$fields['from']->title = 'Als afzenderadres gebruiken.';
		$fields['bcc'] = new EmailField('bcc', $model->bcc, 'BCC naar');
		$fields['bcc']->title = 'Alle verzonden mails BCCen naar dit adres.';
		$fields[] = new RequiredBedragField(
			'saldogrens',
			$model->saldogrens,
			'Saldogrens',
			'€',
			-100000,
			0
		);
		$fields[] = new SelectField('doelgroep', $model->doelgroep, 'Doelgroep', [
			'leden' => 'Leden',
			'oudleden' => 'Oudleden en nobodies',
		]);
		$fields['uitsluiten'] = new TextField(
			'uitsluiten',
			$model->uitsluiten,
			'Geen email naar'
		);
		$fields['uitsluiten']->title = 'uid\'s gescheiden door comma\'s';
		$fields[] = new RequiredTextField(
			'onderwerp',
			$model->onderwerp,
			'Onderwerp'
		);
		$fields['bericht'] = new RequiredTextareaField(
			'bericht',
			$model->bericht,
			'Mailbericht'
		);
		$fields['bericht']->title =
			'Variabelen: LID = Naam van lid, SALDO = Saldo van lid';

		$fields['verzenden'] = new CheckboxField(
			'verzenden',
			$model->verzenden,
			'Verzenden'
		);
		$fields['verzenden']->title =
			'Vink dit veld aan om daadwerkelijk mails te verzenden';

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}
}
