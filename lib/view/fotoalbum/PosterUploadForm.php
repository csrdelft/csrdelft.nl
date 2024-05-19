<?php

namespace CsrDelft\view\fotoalbum;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\fotoalbum\FotoAlbum;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\required\RequiredFileNameField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\uploadvelden\required\RequiredImageField;

class PosterUploadForm extends Formulier
{
	public function __construct(FotoAlbum $album)
	{
		parent::__construct($album, '/fotoalbum/uploaden/' . $album->subdir);
		$this->titel = 'Poster toevoegen in: ' . $album->getParentName();

		$fields = [];
		$fields[] = new HtmlComment('Alleen jpeg afbeeldingen.<br/><br/>');
		$fields[] = new RequiredFileNameField(
			'posternaam',
			null,
			'Posternaam',
			50,
			5
		);
		$fields[] = new RequiredImageField('afbeelding', 'Poster', null, null, [
			'image/jpeg',
		]);
		$fields[] = new FormDefaultKnoppen('/fotoalbum', false);
		$fields[] = new HtmlComment(
			'<br /><span class="cursief">Maak nooit inbreuk op de auteursrechten of het recht op privacy van anderen.</span>'
		);
		$this->addFields($fields);
	}

	public function getBreadcrumbs(): string
	{
		return '<ul class="breadcrumb">' .
			FotoAlbumBreadcrumbs::getBreadcrumbs($this->model, false, true) .
			'</ul>';
	}

	public function __toString(): string
	{
		$html = '';
		$html .= parent::__toString();
		// Uitleg foto's toevoegen
		$body = new CmsPaginaView(
			ContainerFacade::getContainer()
				->get(CmsPaginaRepository::class)
				->find('fotostoevoegen')
		);
		$html .= $body->__toString();
		return $html;
	}
}
