<?php


namespace CsrDelft\view\forum;


use CsrDelft\model\entity\forum\ForumCategorie;
use CsrDelft\model\entity\forum\ForumDeel;
use CsrDelft\model\entity\forum\ForumDraad;
use CsrDelft\model\groepen\LichtingenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\BBCodeField;
use CsrDelft\view\formulier\invoervelden\HiddenField;
use CsrDelft\view\formulier\invoervelden\required\RequiredEmailField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\invoervelden\SpamTrapField;
use CsrDelft\view\formulier\knoppen\EmptyFormKnoppen;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\knoppen\FormulierKnop;
use CsrDelft\view\formulier\knoppen\SubmitKnop;

class ForumPostForm extends Formulier {
	private $deel;

	/**
	 * @param NieuwForumPost $model
	 * @param string $action
	 * @param ForumCategorie[] $delen
	 * @param ForumDeel|null $deel
	 * @param ForumDraad|null $draad
	 */
	public function __construct($model, $action, $delen, $deel = null, $draad = null) {
		parent::__construct($model, $action, false, false);

		$this->css_classes[] = 'w-100';

		$jaar = LichtingenModel::getHuidigeJaargang();

		$fields = [];

		$publiekNoticeClass = $deel && !$deel->isOpenbaar() ? "" : "verborgen";

		$fields[] = new HtmlComment("
			<div id=\"public-melding\" class=\"alert alert-danger $publiekNoticeClass\">
				<strong>Openbaar forum</strong>
				Voor iedereen leesbaar, doorzoekbaar door zoekmachines.<br/>
				Zet [prive] en [/prive] om uw persoonlijke contactgegevens in het bericht.
			</div>
		");
		if (!LoginModel::mag(P_LOGGED_IN)) {
			$fields[] = new HtmlComment("
				<div class=\"alert alert-info\">
					Hier kunt u een bericht toevoegen aan het forum. Het zal echter niet direct zichtbaar worden, maar
					&eacute;&eacute;rst door de PubCie worden goedgekeurd.
					Het vermelden van <em>uw e-mailadres</em> is verplicht.
				</div>
			");
		}
		if (!$deel) {
			$fields[] = new ForumDeelSelectieField('forum_id', $model->forum_id, 'Forum deel', $delen);
		}
		if (!LoginModel::mag(P_LOGGED_IN)) {
			$fields[] = new RequiredEmailField('email', $model->email, 'Emailadres');
		}
		if (!$draad) {
			$fields[] = new RequiredTextField('titel', $model->titel, 'Titel');
		}
		$fields[] = new BBCodeField('forumBericht', $model->forumBericht, '');
		$fields[] = new SpamTrapField('firstname');

		$this->addFields($fields);
		$this->formKnoppen = new EmptyFormKnoppen();
		if (LoginModel::mag(P_LOGGED_IN)) {
			$this->formKnoppen->addKnop(new FormulierKnop("/fotoalbum/uploaden/$jaar/Posters", "btn-secondary", "Poster opladen", "Nieuwe poster opladen", "", "_blank"));
			$this->formKnoppen->addKnop(new FormulierKnop("/groepen/activiteiten/nieuw", "post popup btn-secondary", "Ketzer maken", "Nieuwe ketzer maken", ""));

			if ($deel) {
				$url = "/forum/concept/$deel->forum_id";

				if ($draad) {
					$url .= "/$draad->draad_id";
				}

				$this->formKnoppen->addKnop(new FormulierKnop($url, "set-concept", "Concept opslaan", "Concept opslaan", ""));
			}
		}
		$this->formKnoppen->addKnop(new SubmitKnop());
		$this->deel = $deel;
	}

}
