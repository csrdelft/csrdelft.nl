<?php


namespace CsrDelft\view\plaatjes;


use CsrDelft\model\entity\LidToestemming;
use CsrDelft\model\entity\ForumPlaatje;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;
use CsrDelft\view\formulier\uploadvelden\ImageField;

class PlaatjesUploadModalForm extends ModalForm
{
	public $uploader;

	/**
	 * PlaatjesUploadModalForm constructor.
	 */
	public function __construct()
	{
		parent::__construct(null, '/forum/plaatjes/upload', 'Plaatje uploaden');
		$this->uploader = new ImageField('image', 'Afbeelding');
		$this->addFields([$this->uploader]);
		$this->formKnoppen = new FormDefaultKnoppen();
	}
}
