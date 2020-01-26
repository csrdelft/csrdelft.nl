<?php

namespace CsrDelft\view\bibliotheek;

use CsrDelft\model\bibliotheek\BiebRubriekModel;
use CsrDelft\model\entity\bibliotheek\Boek;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\getalvelden\IntField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\invoervelden\TitelField;
use CsrDelft\view\formulier\keuzevelden\SelectField;
use CsrDelft\view\formulier\knoppen\SubmitKnop;

/**
 * Boek weergeven
 */
class BoekFormulier extends Formulier {

	public $formulier;
	public function __construct(Boek $boek) {
		parent::__construct($boek, $boek->id == null ? "/bibliotheek/boek" : "/bibliotheek/boek/$boek->id", '');
		if ($boek->id == null || $boek->magBewerken()) {
			$fields = [];
			$fields['titel'] = new TitelField('titel', $boek->getTitel(), "Titel:", 200);
			$fields['auteur'] = new TextField('auteur', $boek->getAuteur(), 'Auteur', 100);
			$fields['auteur']->suggestions[] = '/bibliotheek/autocomplete/auteur?q=';
			$fields['auteur']->placeholder = 'Achternaam, Voornaam V.L. van de';
			$fields['paginas'] = new IntField('paginas', $boek->getPaginas(), "Pagina's", 0, 10000);
			$fields['taal'] = new TextField('taal', $boek->getTaal(), 'Taal', 25);
			$fields['taal']->suggestions[] = '/bibliotheek/autocomplete/taal?q=';
			$fields['isbn'] = new TextField('isbn', $boek->getISBN(), 'ISBN', 15);
			$fields['isbn']->placeholder = 'Uniek nummer';
			$fields['uitgeverij'] = new TextField('uitgeverij', $boek->getUitgeverij(), 'Uitgeverij', 100);
			$fields['uitgeverij']->suggestions[] = '/bibliotheek/autocomplete/uitgeverij?q=';
			$fields['uitgavejaar'] = new IntField('uitgavejaar', $boek->getUitgavejaar(), 'Uitgavejaar', 0, 2100);
			$fields['categorie_id'] = new SelectField('categorie_id', $boek->getRubriek() ? $boek->getRubriek()->id : "", 'Rubriek', $this->getRubriekOptions());
			$fields['categorie_id']->required = true;
			$fields['code'] = new TextField('code', $boek->getCode(), 'Biebcode', 7);
			$fields['code']->required = true;
			$fields[] = new SubmitKnop();
			$this->addFields($fields);
			$this->css_classes[] = 'boekformulier';
		}
	}

	public function getRubriekOptions() : array {
		$ret = [];
		$rubrieken = BiebRubriekModel::instance()->find();
		foreach ($rubrieken as $rubriek) {
			$ret[$rubriek->id] = (string) $rubriek;
		}
		return $ret;
	}


}
