<?php


namespace CsrDelft\view\plaatjes;


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
		parent::__construct(null, '', 'Plaatje uploaden');
		$this->uploader = new ImageField('image', 'Afbeelding');
		$this->addFields([$this->uploader]);
		$this->formKnoppen = new FormDefaultKnoppen();
	}
}
