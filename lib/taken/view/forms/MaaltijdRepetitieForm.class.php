<?php

require_once 'verticale.class.php';

/**
 * MaaltijdRepetitieForm.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier voor een nieuwe of te bewerken maaltijd-repetitie.
 * 
 */
class MaaltijdRepetitieForm extends TemplateView {

	private $_form;
	private $_mrid;

	public function __construct($mrid, $dag = null, $periode = null, $titel = null, $tijd = null, $prijs = null, $abo = null, $limiet = null, $filter = null, $verplaats = null) {
		parent::__construct();
		$this->_mrid = $mrid;

		$suggesties = array();
		$suggesties[] = 'geslacht:m';
		$suggesties[] = 'geslacht:v';
		$verticalen = \Verticale::getNamen();
		foreach ($verticalen as $naam) {
			$suggesties[] = 'verticale:' . $naam;
		}
		$jong = \Lichting::getJongsteLichting();
		for ($jaar = $jong; $jaar > $jong - 9; $jaar--) {
			$suggesties[] = 'lichting:' . $jaar;
		}

		$fields[] = new RequiredTextField('standaard_titel', $titel, 'Standaard titel', 255);
		$fields[] = new TijdField('standaard_tijd', $tijd, 'Standaard tijd', 15);
		$fields['dag'] = new WeekdagField('dag_vd_week', $dag, 'Dag v/d week');
		$fields['dag']->title = 'Als de periode ongelijk is aan 7 is dit de start-dag bij het aanmaken van periodieke maaltijden';
		$fields[] = new IntField('periode_in_dagen', $periode, 'Periode (in dagen)', 0, 183);
		$fields['abo'] = new VinkField('abonneerbaar', $abo, 'Abonneerbaar');
		if ($this->_mrid !== 0) {
			$fields['abo']->setOnChangeScript("if (!this.checked) alert('Alle abonnementen zullen worden verwijderd!');");
		}
		$fields[] = new FloatField('standaard_prijs', $prijs, 'Standaard prijs (€)', 0, 50);
		$fields[] = new IntField('standaard_limiet', $limiet, 'Standaard limiet', 0, 200);
		$fields['filter'] = new TextField('abonnement_filter', $filter, 'Aanmeldrestrictie', 255);
		$fields['filter']->setSuggestions($suggesties);
		$fields['filter']->title = 'Plaats een ! vooraan om van de restrictie een uitsluiting te maken.';
		$fields['filter']->required = false;
		if ($this->_mrid !== 0) {
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
		$fields['src'] = new SubmitResetCancel();
		$fields['src']->extraText = 'Alles bijwerken';
		$fields['src']->extraTitle = 'Opslaan & alle maaltijden bijwerken';
		$fields['src']->extraIcon = 'disk_multiple';
		$fields['src']->extraUrl = Instellingen::get('taken', 'url') . '/bijwerken/' . $mrid;


		$this->_form = new Formulier(null, 'taken-maaltijd-repetitie-form', Instellingen::get('taken', 'url') . '/opslaan/' . $mrid, $fields);
	}

	public function getTitel() {
		if ($this->_mrid === 0) {
			return 'Maaltijdrepetitie aanmaken';
		}
		return 'Maaltijdrepetitie wijzigen';
	}

	public function view() {
		$this->_form->addCssClass('popup');
		$this->smarty->assign('form', $this->_form);
		if ($this->_mrid === 0) {
			$this->smarty->assign('nocheck', true);
		} elseif ($this->_mrid > 0) {
			$this->smarty->assign('bijwerken', Instellingen::get('taken', 'url') . '/bijwerken/' . $this->_mrid);
		}
		$this->smarty->display('taken/popup_form.tpl');
	}

	public function validate() {
		if (!is_int($this->_mrid) || $this->_mrid < 0) {
			return false;
		}
		$fields = $this->_form->getFields();
		$filter = $fields['filter']->getValue();
		if (!empty($filter)) {
			if (preg_match('/\s/', $filter)) {
				$fields['filter']->error = 'Mag geen spaties bevatten';
				return false;
			}
			$filter = explode(':', $filter);
			if (sizeof($filter) !== 2 || empty($filter[0]) || empty($filter[1])) {
				$fields['filter']->error = 'Ongeldige restrictie';
				return false;
			}
		}
		return $this->_form->validate();
	}

	public function getValues() {
		return $this->_form->getValues(); // escapes HTML
	}

}

?>