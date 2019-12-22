<?php

namespace CsrDelft\view\documenten;

use CsrDelft\entity\documenten\Document;
use CsrDelft\model\entity\Map;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\RechtenField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\keuzevelden\SelectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\uploadvelden\FileField;
use CsrDelft\view\formulier\uploadvelden\required\RequiredFileField;

/**
 * Class DocumentForm.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @property Document $model
 */
class DocumentToevoegenForm extends Formulier {

	private $uploader;

	public function __construct($categorieNamen) {
		parent::__construct(new Document(), '/documenten/toevoegen', 'Document toevoegen');

		$map = new Map();
		$map->path = PUBLIC_FTP . 'documenten/';
		$map->dirname = basename($map->path);

		if (!$this->isPosted()) {
			$this->model->categorie_id = filter_input(INPUT_GET, 'catID', FILTER_VALIDATE_INT);
		}

		$fields[] = new SelectField('categorie_id', $this->model->categorie_id, 'Categorie', $categorieNamen);
		$fields[] = new RequiredTextField('naam', $this->model->naam, 'Documentnaam');
		$fields[] = $this->uploader = new RequiredFileField('document', 'Document', $this->model, $map);
		$fields['rechten'] = new RechtenField('leesrechten', $this->model->leesrechten, 'Leesrechten');
		$fields['rechten']->readonly = true;
		$fields[] = new FormDefaultKnoppen('/documenten');

		$this->addFields($fields);
	}

	/**
	 * @return FileField
	 */
	public function getUploader() {
		return $this->uploader;
	}

}
