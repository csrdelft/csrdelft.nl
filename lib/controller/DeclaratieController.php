<?php

namespace CsrDelft\controller;

use CsrDelft\controller\framework\QueryParamTrait;
use CsrDelft\model\entity\Declaratie;
use CsrDelft\model\entity\DeclaratieRegel;
use CsrDelft\view\declaratie\DeclaratieFormulier;
use CsrDelft\view\declaratie\DeclaratieRegelTable;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 26/04/2019
 */
class DeclaratieController {
	use QueryParamTrait;

	public function aanmaken() {
		$declaratie = new Declaratie();
		$declaratieRegel = new DeclaratieRegel();
		$declaratieRegel->omschrijving = 'Een declaratieregel';
		$declaratie->declaratie_regels[] = $declaratieRegel;
		$declaratie->declaratie_regels[] = new DeclaratieRegel();

		$formulier = new DeclaratieFormulier($declaratie);

		if ($formulier->isPosted() && $formulier->validate()) {

		} else {
			return view('default', [
				'content' => $formulier
			]);
		}
	}
}
