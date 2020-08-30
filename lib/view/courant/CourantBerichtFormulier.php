<?php


namespace CsrDelft\view\courant;


use CsrDelft\entity\courant\CourantBericht;
use CsrDelft\entity\courant\CourantCategorie;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\HiddenField;
use CsrDelft\view\formulier\invoervelden\required\RequiredBBCodeField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\keuzevelden\required\RequiredEnumSelectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;

class CourantBerichtFormulier extends Formulier {
	/**
	 * CourantFormulier constructor.
	 * @param CourantBericht $model
	 * @param $action
	 */
	public function __construct($model, $action) {
		parent::__construct($model, $action, 'Courant bericht');

		$fields = [];

		$fields[] = new RequiredTextField('titel', $model->titel, 'Titel');
		$fields['cat'] = new RequiredEnumSelectField('cat', $model->cat, 'Categorie', CourantCategorie::class);
		$fields['cat']->title = '
		Selecteer hier een categorie. Uw invoer is enkel een voorstel.
		<em>Aankondigingen over kamers te huur komen in <strong>overig</strong> terecht! C.S.R. is bedoeld voor
			activiteiten van C.S.R.-commissies en andere verenigingsactiviteiten.</em>';
		$fields['bb'] = new RequiredBBCodeField('bericht', $model->bericht, 'Bericht');

		$bbId = $fields['bb']->getId();
		$sponsorlink = 'https://www.csrdelft.nl/plaetjes/banners/' . instelling('courant', 'sponsor');

		if (LoginService::mag(P_MAIL_COMPOSE)) {
			$fields[] = new HtmlComment(<<<HTML
<div>
	<input type="button" value="Importeer agenda" onclick="window.courant.importAgenda('${bbId}');" class="btn btn-primary" />
	<input type="button" value="Importeer sponsor" onclick="document.getElementById('${bbId}').value += '[img]${sponsorlink}[/img]'" class="btn btn-primary" />
</div>
HTML
);
		}
		$fields[] = new HiddenField('volgorde', $model->volgorde, '');

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen('/courant');
	}

}
