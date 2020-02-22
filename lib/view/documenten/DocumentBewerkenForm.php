<?php

namespace CsrDelft\view\documenten;

use CsrDelft\entity\documenten\Document;
use CsrDelft\entity\documenten\DocumentCategorie;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\RechtenField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\keuzevelden\EntitySelectField;
use CsrDelft\view\formulier\keuzevelden\SelectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;

/**
 * Class DocumentBewerkenForm.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class DocumentBewerkenForm extends Formulier {

	public function __construct(Document $document, $categorieNamen) {
		parent::__construct($document, '/documenten/bewerken/' . $document->id, 'Document bewerken');
		$fields = [];
		$fields[] = new EntitySelectField('categorie', $document->categorie, 'Categorie', DocumentCategorie::class);
		$fields[] = new RequiredTextField('naam', $document->naam, 'Documentnaam');
		$fields['rechten'] = new RechtenField('leesrechten', $document->leesrechten, 'Leesrechten');
		$fields['rechten']->readonly = true;
		$fields[] = new FormDefaultKnoppen('/documenten');

		$this->addFields($fields);
	}
}
