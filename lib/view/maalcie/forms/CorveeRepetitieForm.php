<?php
namespace CsrDelft\view\maalcie\forms;
use CsrDelft\model\entity\maalcie\CorveeRepetitie;
use CsrDelft\model\maalcie\FunctiesModel;
use CsrDelft\model\maalcie\MaaltijdRepetitiesModel;
use CsrDelft\view\formulier\getalvelden\IntField;
use CsrDelft\view\formulier\keuzevelden\CheckboxField;
use CsrDelft\view\formulier\keuzevelden\JaNeeField;
use CsrDelft\view\formulier\keuzevelden\SelectField;
use CsrDelft\view\formulier\keuzevelden\WeekdagField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\knoppen\FormulierKnop;
use CsrDelft\view\formulier\ModalForm;

/**
 * CorveeRepetitieForm.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier voor een nieuwe of te bewerken corvee-repetitie.
 *
 */
class CorveeRepetitieForm extends ModalForm {

	public function __construct(CorveeRepetitie $repetitie) {
		parent::__construct($repetitie, maalcieUrl . '/opslaan/' . $repetitie->crv_repetitie_id);

		if ($repetitie->crv_repetitie_id === 0) {
			$this->titel = 'Corveerepetitie aanmaken';
		} else {
			$this->titel = 'Corveerepetitie wijzigen';
			$this->css_classes[] = 'PreventUnchanged';
		}

		$functieNamen = FunctiesModel::instance()->getAlleFuncties(); // grouped by functie_id
		$functiePunten = 'var punten=[];';
		foreach ($functieNamen as $functie) {
			$functieNamen[$functie->functie_id] = $functie->naam;
			$functiePunten .= 'punten[' . $functie->functie_id . ']=' . $functie->standaard_punten . ';';
			if ($repetitie->standaard_punten === null) {
				$repetitie->standaard_punten = $functie->standaard_punten;
			}
		}

		$mlt_repetities = MaaltijdRepetitiesModel::instance()->getAlleRepetities();
		$repetitieNamen = array('' => '');
		foreach ($mlt_repetities as $rep) {
			$repetitieNamen[$rep->mlt_repetitie_id] = $rep->standaard_titel;
		}

		$fields['fid'] = new SelectField('functie_id', $repetitie->functie_id, 'Functie', $functieNamen);
		$fields['fid']->onchange = $functiePunten . "$('#field_standaard_punten').val(punten[this.value]);";
		$fields[] = new WeekdagField('dag_vd_week', $repetitie->dag_vd_week, 'Dag v/d week');
		$fields['dag'] = new IntField('periode_in_dagen', $repetitie->periode_in_dagen, 'Periode (in dagen)', 0, 183);
		$fields['dag']->title = 'Als de periode ongelijk is aan 7 is dit de start-dag bij het aanmaken van periodiek corvee';
		$fields['vrk'] = new JaNeeField('voorkeurbaar', $repetitie->voorkeurbaar, 'Voorkeurbaar');
		if ($repetitie->crv_repetitie_id !== 0) {
			$fields['vrk']->onchange = "if (!this.checked && $(this).attr('origvalue') == 1) if (!confirm('Alle voorkeuren zullen worden verwijderd!')) this.checked = true;";
		}
		$fields[] = new SelectField('mlt_repetitie_id', $repetitie->mlt_repetitie_id, 'Maaltijdrepetitie', $repetitieNamen);
		$fields[] = new IntField('standaard_punten', $repetitie->standaard_punten, 'Standaard punten', 0, 10);
		$fields[] = new IntField('standaard_aantal', $repetitie->standaard_aantal, 'Aantal corveeërs', 1, 10);

		$bijwerken = new FormulierKnop(maalcieUrl . '/bijwerken/' . $repetitie->crv_repetitie_id, 'submit', 'Alles bijwerken', 'Opslaan & alle taken bijwerken', 'disk_multiple');

		if ($repetitie->crv_repetitie_id !== 0) {
			$fields['ver'] = new CheckboxField('verplaats_dag', false, 'Verplaatsen');
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
		$fields['btn'] = new FormDefaultKnoppen();
		$fields['btn']->addKnop($bijwerken, false, true);

		$this->addFields($fields);
	}

}
