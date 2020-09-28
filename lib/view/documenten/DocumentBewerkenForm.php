<?php

namespace CsrDelft\view\documenten;

use CsrDelft\Component\Formulier\FormulierBuilder;
use CsrDelft\Component\Formulier\FormulierTypeInterface;
use CsrDelft\entity\documenten\Document;
use CsrDelft\entity\documenten\DocumentCategorie;
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
class DocumentBewerkenForm implements FormulierTypeInterface {

	/**
	 * @var UrlGeneratorInterface
	 */
	private $urlGenerator;

	public function __construct(UrlGeneratorInterface $urlGenerator) {
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * @param FormulierBuilder $builder
	 * @param Document $data
	 * @param array $options
	 */
	public function createFormulier(FormulierBuilder $builder, $data, $options = []) {
		$builder->setTitel('Document bewerken');
		$fields = [];
		$fields[] = new EntitySelectField('categorie', $data->categorie, 'Categorie', DocumentCategorie::class);
		$fields[] = new RequiredTextField('naam', $data->naam, 'Documentnaam');
		$fields['rechten'] = new RechtenField('leesrechten', $data->leesrechten, 'Leesrechten');
		$fields['rechten']->readonly = true;
		$fields[] = new FormDefaultKnoppen($this->urlGenerator->generate('csrdelft_documenten_recenttonen'));

		$builder->addFields($fields);
	}
}
