<?php

namespace CsrDelft\view\maalcie\forms;

use CsrDelft\entity\fiscaat\CiviProduct;
use CsrDelft\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\view\formulier\getalvelden\IntField;
use CsrDelft\view\formulier\invoervelden\RechtenField;
use CsrDelft\view\formulier\invoervelden\required\RequiredDoctrineEntityField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\keuzevelden\CheckboxField;
use CsrDelft\view\formulier\keuzevelden\JaNeeField;
use CsrDelft\view\formulier\keuzevelden\TimeObjectField;
use CsrDelft\view\formulier\keuzevelden\WeekdagField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\knoppen\FormulierKnop;
use CsrDelft\view\formulier\ModalForm;

/**
 * MaaltijdRepetitieForm.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier voor een nieuwe of te bewerken maaltijd-repetitie.
 *
 * @method MaaltijdRepetitie getModel()
 */
class MaaltijdRepetitieForm extends ModalForm
{

	/**
	 * MaaltijdRepetitieForm constructor.
	 * @param $model MaaltijdRepetitie
	 */
	public function __construct($model, $verplaats = null)
	{
		parent::__construct($model, '/maaltijden/repetities/opslaan/' . $model->mlt_repetitie_id);

		if ($model->mlt_repetitie_id === null) {
			$this->titel = 'Maaltijdrepetitie aanmaken';
		} else {
			$this->titel = 'Maaltijdrepetitie wijzigen';
			$this->css_classes[] = 'PreventUnchanged';
		}

		$fields = [];
		$fields[] = new RequiredTextField('standaard_titel', $model->standaard_titel, 'Standaard titel', 255);
		$fields[] = new TimeObjectField('standaard_tijd', $model->standaard_tijd, 'Standaard tijd', 15);
		$fields['dag'] = new WeekdagField('dag_vd_week', $model->dag_vd_week, 'Dag v/d week');
		$fields['dag']->title = 'Als de periode ongelijk is aan 7 is dit de start-dag bij het aanmaken van periodieke maaltijden';
		$fields[] = new IntField('periode_in_dagen', $model->periode_in_dagen, 'Periode (in dagen)', 0, 183);
		$fields['abo'] = new JaNeeField('abonneerbaar', $model->abonneerbaar, 'Abonneerbaar');
		if ($model->mlt_repetitie_id !== 0) {
			$fields['abo']->onchange = "if (!this.checked && $(this).attr('origvalue') == 1) if (!confirm('Alle abonnementen zullen worden verwijderd!')) this.checked = true;";
		}
		$fields[] = new RequiredDoctrineEntityField('product', $model->product, 'Product', CiviProduct::class, '/fiscaat/producten/suggesties?q=');
		$fields[] = new IntField('standaard_limiet', $model->standaard_limiet, 'Standaard limiet', 0, 200);
		$fields[] = new RechtenField('abonnement_filter', $model->abonnement_filter, 'Aanmeldrestrictie');

		$bijwerken = new FormulierKnop('/maaltijden/repetities/bijwerken/' . $model->mlt_repetitie_id, 'submit', 'Alles bijwerken', 'Opslaan & alle maaltijden bijwerken', 'disk_multiple');

		if ($model->mlt_repetitie_id !== 0) {
			$fields['ver'] = new CheckboxField('verplaats_dag', $verplaats, 'Verplaatsen');
			$fields['ver']->title = 'Verplaats naar dag v/d week bij bijwerken';
			$fields['ver']->onchange = <<<JS
var btn = $('#{$bijwerken->getId()}');
if (this.checked) {
	btn.html(btn.html().replace('bijwerken', 'bijwerken en verplaatsen'));
} else {
	btn.html(btn.html().replace(' en verplaatsen', ''));
}
JS;
		}
		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
		$this->formKnoppen->addKnop($bijwerken, false, true);

	}

}
