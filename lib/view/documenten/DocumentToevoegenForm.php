<?php

namespace CsrDelft\view\documenten;

use CsrDelft\common\ContainerFacade;
use CsrDelft\Component\Formulier\FormulierBuilder;
use CsrDelft\Component\Formulier\FormulierTypeInterface;
use CsrDelft\entity\documenten\Document;
use CsrDelft\entity\documenten\DocumentCategorie;
use CsrDelft\model\entity\Map;
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
class DocumentToevoegenForm implements FormulierTypeInterface {

	private $uploader;
	/**
	 * @var UrlGeneratorInterface
	 */
	private $urlGenerator;

	public function __construct(UrlGeneratorInterface $urlGenerator) {
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * @return FileField
	 */
	public function getUploader() {
		return $this->uploader;
	}

	public function createFormulier(FormulierBuilder $builder, $data, $options = []) {
		//parent::__construct(new Document(), '/documenten/toevoegen', 'Document toevoegen');
		$builder->setTitel('Document toevoegen');

		$map = new Map();
		$map->path = PUBLIC_FTP . 'documenten/';
		$map->dirname = basename($map->path);

		$fields[] = new EntitySelectField('categorie', $data->categorie, 'Categorie', DocumentCategorie::class);
		$fields[] = new RequiredTextField('naam', $data->naam, 'Documentnaam');
		$fields['uploader'] = $this->uploader = new RequiredFileField('document', 'Document', $data, $map);
		$fields['rechten'] = new RechtenField('leesrechten', $data->leesrechten, 'Leesrechten');
		$fields['rechten']->readonly = true;
		$fields[] = new FormDefaultKnoppen($this->urlGenerator->generate('csrdelft_documenten_recenttonen'));

		$builder->addFields($fields);
	}
}
