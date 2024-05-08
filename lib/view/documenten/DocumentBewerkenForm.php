<?php

namespace CsrDelft\view\documenten;

use CsrDelft\Component\Formulier\FormulierBuilder;
use CsrDelft\Component\Formulier\FormulierTypeInterface;
use CsrDelft\entity\documenten\Document;
use CsrDelft\entity\documenten\DocumentCategorie;
use CsrDelft\repository\documenten\DocumentCategorieRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\formulier\invoervelden\RechtenField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\keuzevelden\EntitySelectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class DocumentBewerkenForm.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class DocumentBewerkenForm implements FormulierTypeInterface
{
	/**
	 * @var UrlGeneratorInterface
	 */
	private $urlGenerator;
	/**
	 * @var DocumentCategorieRepository
	 */
	private $documentCategorieRepository;
	/**
	 * @var LoginService
	 */
	private $loginService;

	public function __construct(
		UrlGeneratorInterface $urlGenerator,
		LoginService $loginService,
		DocumentCategorieRepository $documentCategorieRepository
	) {
		$this->urlGenerator = $urlGenerator;
		$this->documentCategorieRepository = $documentCategorieRepository;
		$this->loginService = $loginService;
	}

	/**
	 * @param FormulierBuilder $builder
	 * @param Document $data
	 * @param array $options
	 */
	public function createFormulier(
		FormulierBuilder $builder,
		$data,
		$options = []
	) {
		$builder->setTitel('Document bewerken');
		$fields = [];
		$fields['categorie'] = new EntitySelectField(
			'categorie',
			$data->categorie,
			'Categorie',
			DocumentCategorie::class
		);
		$toegestaneCategorien = $this->documentCategorieRepository->findMetSchijfrechtenVoorLid();
		$fields['categorie']->setOptions($toegestaneCategorien);
		if (count($toegestaneCategorien) == 1) {
			$fields['categorie']->hidden = true;
		}
		$fields[] = new RequiredTextField('naam', $data->naam, 'Documentnaam');
		$fields['rechten'] = new RechtenField(
			'leesrechten',
			$data->leesrechten,
			'Leesrechten'
		);
		$fields['rechten']->readonly = true;

		$builder->addFields($fields);

		$builder->setFormKnoppen(
			new FormDefaultKnoppen(
				$this->urlGenerator->generate('csrdelft_documenten_recenttonen')
			)
		);
	}
}
