<?php

/**
 * CorveeRepetitieForm.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier voor een nieuwe of te bewerken corvee-repetitie.
 * 
 */
class CorveeRepetitieForm extends ModalForm {

	public function __construct($crid, $mrid = null, $dag = null, $periode = null, $fid = null, $punten = null, $aantal = null, $voorkeur = null, $verplaats = null) {
		parent::__construct(null, 'maalcie-corvee-repetitie-form', maalcieUrl . '/opslaan/' . $crid);

		if (!is_int($crid) || $crid < 0) {
			throw new Exception('invalid crid');
		}
		if ($crid === 0) {
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
			if ($punten === null) {
				$punten = $functie->standaard_punten;
			}
		}

		$mlt_repetities = MaaltijdRepetitiesModel::getAlleRepetities();
		$repetitieNamen = array('' => '');
		foreach ($mlt_repetities as $rep) {
			$repetitieNamen[$rep->getMaaltijdRepetitieId()] = $rep->getStandaardTitel();
		}

		$fields['fid'] = new SelectField('functie_id', $fid, 'Functie', $functieNamen);
		$fields['fid']->onchange = $functiePunten . "$('#field_standaard_punten').val(punten[this.value]);";
		$fields[] = new WeekdagField('dag_vd_week', $dag, 'Dag v/d week');
		$fields['dag'] = new IntField('periode_in_dagen', $periode, 'Periode (in dagen)', 0, 183);
		$fields['dag']->title = 'Als de periode ongelijk is aan 7 is dit de start-dag bij het aanmaken van periodiek corvee';
		$fields['vrk'] = new VinkField('voorkeurbaar', $voorkeur, 'Voorkeurbaar');
		if ($crid !== 0) {
			$fields['vrk']->onchange = "if (!this.checked && $(this).attr('origvalue') == 1) if (!confirm('Alle voorkeuren zullen worden verwijderd!')) this.checked = true;";
		}
		$fields['mrid'] = new SelectField('mlt_repetitie_id', $mrid, 'Maaltijdrepetitie', $repetitieNamen);
		$fields['mrid']->empty_null = true;
		$fields[] = new IntField('standaard_punten', $punten, 'Standaard punten', 0, 10);
		$fields[] = new IntField('standaard_aantal', $aantal, 'Aantal corveeërs', 1, 10);
		if ($crid !== 0) {
			$fields['ver'] = new VinkField('verplaats_dag', $verplaats, 'Verplaatsen');
			$fields['ver']->title = 'Verplaats naar dag v/d week bij bijwerken';
			$fields['ver']->onchange = <<<JS
var txt = $('#extraButton').html();
if (this.checked) {
	txt = txt.replace('bijwerken', 'bijwerken en verplaatsen');
} else {
	txt = txt.replace(' en verplaatsen', '');
}
$('#extraButton').html(txt);
JS;
		}
		$fields['btn'] = new FormDefaultKnoppen();

		$bijwerken = new FormulierKnop(maalcieUrl . '/bijwerken/' . $crid, 'submit', 'Alles bijwerken', 'Opslaan & alle taken bijwerken', '/famfamfam/disk_multiple.png');
		$fields['btn']->addKnop($bijwerken, true);

		$this->addFields($fields);
	}

}
