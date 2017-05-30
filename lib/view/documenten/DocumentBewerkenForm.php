<?php

namespace CsrDelft\view\documenten;

use CsrDelft\model\documenten\DocumentCategorieModel;
use CsrDelft\model\entity\documenten\Document;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\RechtenField;
use CsrDelft\view\formulier\invoervelden\RequiredTextField;
use CsrDelft\view\formulier\keuzevelden\SelectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;

/**
 * Class DocumentBewerkenForm.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class DocumentBewerkenForm extends Formulier {

	public function __construct(Document $document) {
		parent::__construct($document, '/documenten/bewerken/' . $document->id, 'Document bewerken');

		$fields[] = new SelectField('categorie_id', $document->categorie_id, 'Categorie', DocumentCategorieModel::instance()->getCategorieNamen());
		$fields[] = new RequiredTextField('naam', $document->naam, 'Documentnaam');
		$fields['rechten'] = new RechtenField('leesrechten', $document->leesrechten, 'Leesrechten');
		$fields['rechten']->readonly = true;
		$fields[] = new FormDefaultKnoppen('/documenten/');

		$this->addFields($fields);
	}
}
