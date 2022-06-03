<?php

namespace CsrDelft\view\fotoalbum;

use CsrDelft\entity\fotoalbum\FotoAlbum;
use CsrDelft\view\formulier\invoervelden\required\RequiredFileNameField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

class FotoAlbumToevoegenForm extends ModalForm
{

	public function __construct(FotoAlbum $album)
	{
		parent::__construct($album, join_paths('/fotoalbum/toevoegen', $album->subdir));
		$this->titel = 'Fotoalbum toevoegen in: ' . $album->dirname;
		$this->css_classes[] = 'redirect';

		$fields = [];
		$fields[] = new RequiredFileNameField('subalbum', null, 'Naam');
		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen('/fotoalbum', false);
	}

}
