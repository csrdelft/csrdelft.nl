<?php

namespace CsrDelft\view\fiscaat\saldo;

use CsrDelft\entity\fiscaat\CiviSaldo;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\getalvelden\required\RequiredBedragField;
use CsrDelft\view\formulier\getalvelden\required\RequiredIntField;
use CsrDelft\view\formulier\invoervelden\LidField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 07/05/2017
 *
 * Maak het mogelijk om een lid te registreren, wordt uiteindelijk samengetrokken met het aanmaken van een lid.
 */
class LidRegistratieForm extends ModalForm
{
	/** @var TextField */
	private $naamField;
	/** @var TextField */
	private $uidField;

	/**
	 * @param CiviSaldo $model
	 */
	public function __construct(CiviSaldo $model)
	{
		parent::__construct($model, '/fiscaat/saldo/registreren', false, true);

		$fields = [];
		$fields[] = new HtmlComment("<p>Geef een naam en/of een lid op. Als er geen lid wordt opgegeven wordt een nieuwe uid gegenereerd.</p>");
		$fields['naam'] = $this->naamField = new TextField('naam', $model->naam, 'Bijnaam');
		$fields['uid'] = $this->uidField = new LidField('uid', $model->uid, 'Lid');
		$fields[] = new RequiredBedragField('saldo', $model->saldo ?? 0, 'Initieel saldo');

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}

	public function validate()
	{
		if (!parent::validate()) {
			return false;
		}

		if (is_null($this->naamField->getValue()) && is_null($this->uidField->getValue())) {
			$this->error = 'Vul in ieder geval een uid of een naam in.';
			$this->css_classes[] = 'metFouten';

			return false;
		}

		return true;
	}
}
