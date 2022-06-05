<?php

namespace CsrDelft\view\maalcie\forms;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\corvee\CorveeTaak;
use CsrDelft\view\formulier\invoervelden\LidField;
use CsrDelft\view\formulier\invoervelden\LidObjectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;
use Twig\Environment;

/**
 * ToewijzenForm.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier om een corveetaak toe te wijzen aan een lid.
 *
 */
class ToewijzenForm extends ModalForm
{
	public function __construct(
		CorveeTaak $taak,
		Environment $twig,
		array $suggesties
	) {
		parent::__construct(null, '/corvee/beheer/toewijzen/' . $taak->taak_id);

		if (!is_numeric($taak->taak_id) || $taak->taak_id <= 0) {
			throw new CsrGebruikerException(
				sprintf('Ongeldig taak id "%s".', $taak->taak_id)
			);
		}
		$this->titel = 'Taak toewijzen aan lid';
		$this->css_classes[] = 'PreventUnchanged';

		$fields = [];
		$fields[] = new LidObjectField('profiel', $taak->profiel, 'Naam', 'leden');
		$fields[] = new SuggestieLijst($suggesties, $twig, $taak);

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}
}
