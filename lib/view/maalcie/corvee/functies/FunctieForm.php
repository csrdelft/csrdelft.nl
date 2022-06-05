<?php

namespace CsrDelft\view\maalcie\corvee\functies;

use CsrDelft\entity\corvee\CorveeFunctie;
use CsrDelft\view\formulier\getalvelden\IntField;
use CsrDelft\view\formulier\invoervelden\TextareaField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\keuzevelden\JaNeeField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * Formulier voor een nieuwe of te bewerken corveefunctie.
 */
class FunctieForm extends ModalForm
{
	public function __construct(CorveeFunctie $functie, $actie)
	{
		parent::__construct(
			$functie,
			'/corvee/functies/' .
				$actie .
				($functie->functie_id ? '/' . $functie->functie_id : '')
		);
		$this->titel = 'Corveefunctie ' . $actie;
		if ($actie === 'bewerken') {
			$this->css_classes[] = 'PreventUnchanged';
		}

		$fields = [];
		$fields[] = new TextField('naam', $functie->naam, 'Functienaam', 25);

		$fields['afk'] = new TextField(
			'afkorting',
			$functie->afkorting,
			'Afkorting',
			3
		);
		$fields['afk']->title = 'Afkorting van de functie';

		$fields['eml'] = new TextareaField(
			'email_bericht',
			$functie->email_bericht,
			'E-mailbericht',
			9
		);
		$fields['eml']->title =
			'Tekst in email bericht over deze functie aan de corveeer';

		$fields['ptn'] = new IntField(
			'standaard_punten',
			$functie->standaard_punten,
			'Standaard punten',
			0,
			10
		);
		$fields['ptn']->title =
			'Aantal corveepunten dat standaard voor deze functie gegeven wordt';

		$fields['k'] = new JaNeeField(
			'kwalificatie_benodigd',
			$functie->kwalificatie_benodigd,
			'Kwalificatie benodigd'
		);
		$fields['k']->title =
			'Is er een kwalificatie benodigd om deze functie uit te mogen voeren';

		$fields['m'] = new JaNeeField(
			'maaltijden_sluiten',
			$functie->maaltijden_sluiten,
			'Maaltijden sluiten'
		);
		$fields['m']->title =
			'Geeft deze functie speciale rechten om maaltijden te mogen sluiten';

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}
}
