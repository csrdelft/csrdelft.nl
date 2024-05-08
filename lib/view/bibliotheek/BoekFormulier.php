<?php

namespace CsrDelft\view\bibliotheek;

use CsrDelft\Component\Formulier\FormulierBuilder;
use CsrDelft\Component\Formulier\FormulierTypeInterface;
use CsrDelft\entity\bibliotheek\Boek;
use CsrDelft\repository\bibliotheek\BiebRubriekRepository;
use CsrDelft\view\formulier\getalvelden\IntField;
use CsrDelft\view\formulier\getalvelden\required\RequiredIntField;
use CsrDelft\view\formulier\invoervelden\AutocompleteField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\invoervelden\TitelField;
use CsrDelft\view\formulier\keuzevelden\SelectField;
use CsrDelft\view\formulier\knoppen\EmptyFormKnoppen;
use CsrDelft\view\formulier\knoppen\SubmitKnop;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Boek weergeven
 */
class BoekFormulier implements FormulierTypeInterface
{
	/**
	 * @var UrlGeneratorInterface
	 */
	private $urlGenerator;
	/**
	 * @var BiebRubriekRepository
	 */
	private $biebRubriekRepository;

	public function __construct(
		UrlGeneratorInterface $urlGenerator,
		BiebRubriekRepository $biebRubriekRepository
	) {
		$this->urlGenerator = $urlGenerator;
		$this->biebRubriekRepository = $biebRubriekRepository;
	}

	/**
	 * @param FormulierBuilder $builder
	 * @param Boek $data
	 * @param array $options
	 */
	public function createFormulier(
		FormulierBuilder $builder,
		$data,
		$options = []
	) {
		$builder->setAction(
			$this->urlGenerator->generate('csrdelft_bibliotheek_boek', [
				'boek' => $data->id,
			])
		);
		$builder->setTitel('');

		$fields = [];
		$fields['titel'] = new TitelField(
			'titel',
			$data->titel,
			'Titel:',
			$data->id == null,
			200
		);
		$fields['auteur'] = new AutocompleteField(
			'auteur',
			$data->auteur,
			'Auteur',
			100
		);
		$fields['auteur']->suggestions[] = '/bibliotheek/autocomplete/auteur?q=';
		$fields['auteur']->placeholder = 'Achternaam, Voornaam V.L. van de';
		$fields['paginas'] = new IntField(
			'paginas',
			$data->paginas,
			"Pagina's",
			0,
			10000
		);
		$fields['taal'] = new AutocompleteField('taal', $data->taal, 'Taal', 25);
		$fields['taal']->suggestions[] = '/bibliotheek/autocomplete/taal?q=';
		$fields['isbn'] = new TextField('isbn', $data->isbn, 'ISBN', 15);
		$fields['isbn']->placeholder = 'Uniek nummer';
		$fields['uitgeverij'] = new AutocompleteField(
			'uitgeverij',
			$data->uitgeverij,
			'Uitgeverij',
			100
		);
		$fields['uitgeverij']->suggestions[] =
			'/bibliotheek/autocomplete/uitgeverij?q=';
		$fields['uitgavejaar'] = new RequiredIntField(
			'uitgavejaar',
			$data->uitgavejaar,
			'Uitgavejaar',
			0,
			2100
		);
		$fields['categorie_id'] = new SelectField(
			'categorie_id',
			$data->getRubriek() ? $data->getRubriek()->id : '',
			'Rubriek',
			$this->getRubriekOptions()
		);
		$fields['categorie_id']->required = true;
		$fields['code'] = new TextField('code', $data->code, 'Biebcode', 7);
		$fields['code']->required = true;

		$builder->addFields($fields);
		$knoppen = new EmptyFormKnoppen();
		$knoppen->addKnop(new SubmitKnop());
		$builder->setFormKnoppen($knoppen);

		$builder->addCssClass('boekformulier');
	}

	private function getRubriekOptions(): array
	{
		$ret = [];
		$rubrieken = $this->biebRubriekRepository->findAll();
		foreach ($rubrieken as $rubriek) {
			$ret[$rubriek->id] = (string) $rubriek;
		}
		return $ret;
	}
}
