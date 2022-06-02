<?php


namespace CsrDelft\view\courant;


use CsrDelft\Component\Formulier\FormulierBuilder;
use CsrDelft\Component\Formulier\FormulierTypeInterface;
use CsrDelft\entity\courant\CourantBericht;
use CsrDelft\entity\courant\CourantCategorie;
use CsrDelft\repository\instellingen\InstellingenRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\invoervelden\HiddenField;
use CsrDelft\view\formulier\invoervelden\required\RequiredProsemirrorField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\keuzevelden\required\RequiredEnumSelectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;

class CourantBerichtFormulier implements FormulierTypeInterface
{
	/**
	 * @var InstellingenRepository
	 */
	private $instellingenRepository;

	/**
	 * CourantFormulier constructor.
	 * @param InstellingenRepository $instellingenRepository
	 */
	public function __construct(InstellingenRepository $instellingenRepository)
	{
		$this->instellingenRepository = $instellingenRepository;
	}

	/**
	 * @param FormulierBuilder $builder
	 * @param CourantBericht $data
	 * @param array $options
	 */
	public function createFormulier(FormulierBuilder $builder, $data, $options = [])
	{
		$builder->setTitel('Courant bericht');

		$fields = [];

		$fields[] = new RequiredTextField('titel', $data->titel, 'Titel');
		$fields['cat'] = new RequiredEnumSelectField('cat', $data->cat, 'Categorie', CourantCategorie::class);
		$fields['cat']->title = '
		Selecteer hier een categorie. Uw invoer is enkel een voorstel.
		<em>Aankondigingen over kamers te huur komen in <strong>overig</strong> terecht! C.S.R. is bedoeld voor
			activiteiten van C.S.R.-commissies en andere verenigingsactiviteiten.</em>';
		$fields['bb'] = new RequiredProsemirrorField('bericht', $data->bericht, 'Bericht');
		$fields['bb']->extern = true;

		$sponsorlink = $this->instellingenRepository->getValue('courant', 'sponsor');

		if (LoginService::mag(P_MAIL_COMPOSE)) {
			$fields[] = new HtmlComment(<<<HTML
<div>
	<input type="button" value="Importeer agenda" onclick="window.courant.importAgenda();" class="btn btn-primary" />
	<input type="button" value="Importeer sponsor" onclick="window.courant.importSponsor('${sponsorlink}')" class="btn btn-primary" />
</div>
HTML
			);
		}
		$fields[] = new HiddenField('volgorde', $data->volgorde, '');

		$builder->addFields($fields);
		$builder->setFormKnoppen(new FormDefaultKnoppen("/courant"));
	}
}
