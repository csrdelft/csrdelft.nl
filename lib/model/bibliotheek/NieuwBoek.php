<?php

namespace CsrDelft\model\bibliotheek;

use CsrDelft\common\MijnSqli;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\formulier\elementen\Subkopje;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\keuzevelden\SelectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;

class NieuwBoek extends BiebBoek {

	public function __construct() {
		parent::__construct(array());
		$this->id = 0;
		//zetten we de defaultwaarden voor het nieuwe boek.
		$this->rubriek = new BiebRubriek(108);
		if ($this->isBASFCie()) {
			$this->biebboek = 'ja';
		}
		$this->createBoekformulier();
	}

	public function createBoekformulier() {
		//Iedereen die bieb mag bekijken mag nieuwe boeken toevoegen
		if ($this->magBekijken()) {
			$nieuwboekformulier['boekgeg'] = new Subkopje('Boekgegevens:');
			$nieuwboekformulier = $nieuwboekformulier + $this->getCommonFields();
			if ($this->isBASFCie()) {
				$nieuwboekformulier['biebboek'] = new SelectField('biebboek', $this->biebboek, 'Is een biebboek?', array('ja' => 'C.S.R. boek', 'nee' => 'Eigen boek'));
			}
			$nieuwboekformulier[] = new FormDefaultKnoppen('/bibliotheek/');

			$this->formulier = new Formulier(null, '/bibliotheek/nieuwboek/0');
			$this->formulier->addFields($nieuwboekformulier);
		}
	}

	/**
	 * waarden uit nieuw boek formulier opslaan
	 *
	 * @return bool
	 */
	public function saveFormulier() {
		$this->setValuesFromFormulier();
		//object Boek opslaan
		return $this->save();
	}

	/**
	 * Voeg het object Boek toe aan de db
	 *
	 * @return bool
	 */
	public function save() {

		$db = MijnSqli::instance();
		$qSave = "
			INSERT INTO biebboek (
				titel, auteur, categorie_id, uitgavejaar, uitgeverij, paginas, taal, isbn, code
			) VALUES (
				'" . $db->escape($this->getTitel()) . "',
				'" . $db->escape($this->getAuteur()) . "',
				" . (int)$this->getRubriek()->getId() . ",
				" . (int)$this->getUitgavejaar() . ",
				'" . $db->escape($this->getUitgeverij()) . "',
				" . (int)$this->getPaginas() . ",
				'" . $db->escape($this->getTaal()) . "',
				'" . $db->escape($this->getISBN()) . "',
				'" . $db->escape($this->getCode()) . "'
			);";
		if ($db->query($qSave)) {
			//id ook opslaan in object Boek.
			$this->id = $db->insert_id();
			if ($this->biebboek == 'ja') {
				$eigenaar = 'x222'; //C.S.R.Bieb is eigenaar
			} else {
				$eigenaar = LoginModel::getUid();
			}
			return $this->addExemplaar($eigenaar);
		}
		$this->error .= 'Fout in query, mysql gaf terug: ' . $db->error() . ' Boek::save()';
		return false;
	}

}
