<?php

namespace CsrDelft\view\documenten;

use CsrDelft\common\ContainerFacade;
use CsrDelft\Component\Formulier\FormulierBuilder;
use CsrDelft\Component\Formulier\FormulierTypeInterface;
use CsrDelft\entity\documenten\Document;
use CsrDelft\entity\documenten\DocumentCategorie;
use CsrDelft\model\entity\Map;
use CsrDelft\repository\documenten\DocumentCategorieRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\formulier\elementen\HtmlBbComment;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\RechtenField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\keuzevelden\EntitySelectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\uploadvelden\FileField;
use CsrDelft\view\formulier\uploadvelden\required\RequiredFileField;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class DocumentForm.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class DocumentToevoegenForm implements FormulierTypeInterface
{
	private $uploader;
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
		DocumentCategorieRepository $documentCategorieRepository,
		LoginService $loginService
	) {
		$this->urlGenerator = $urlGenerator;
		$this->documentCategorieRepository = $documentCategorieRepository;
		$this->loginService = $loginService;
	}

	/**
	 * @return FileField
	 */
	public function getUploader()
	{
		return $this->uploader;
	}

	public function createFormulier(
		FormulierBuilder $builder,
		$data,
		$options = []
	) {
		$builder->setTitel('Document toevoegen');

		$map = new Map();
		$map->path = PUBLIC_FTP . 'documenten/';
		$map->dirname = basename($map->path);

		$fields[] = new HtmlBbComment(
			<<<TAG
[p]Documenten die hier geüpload worden moeten relevant zijn voor de hele vereniging of een groot deel van de vereniging
en staan hier om bewaard te worden. Documenten in de 'Categorieloos' categorie worden door een beheerder naar de goede
categorie verplaatst of ze worden verwijderd. Neem contact op met de PubCie als je twijfelt of je document hier thuis
hoort, of als je er zeker van wil zijn dat je document in de goede categorie terecht komt.[/p]
[p]Documenten voor een kleiner publiek, zoals onderverenigingen of commissieleden, kun je ook kwijt op de wiki.
Documenten met een tijdelijke aard zul je zelf ergens op een externe plek neer moeten zetten.
[p]Zorg ervoor dat de naam van je document genoeg context geeft over de inhoud, zodat er op gezocht kan worden.[/p]
TAG
		);

		$fields['categorie'] = new EntitySelectField(
			'categorie',
			$data->categorie,
			'Categorie',
			DocumentCategorie::class
		);
		$fields['categorie']->setOptions(
			$this->documentCategorieRepository->findMetSchijfrechtenVoorLid()
		);
		$fields[] = new RequiredTextField('naam', $data->naam, 'Documentnaam');
		$fields['uploader'] = $this->uploader = new RequiredFileField(
			'document',
			'Document',
			$data,
			$map
		);
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
