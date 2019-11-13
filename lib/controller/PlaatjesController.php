<?php


namespace CsrDelft\controller;


use CsrDelft\common\CsrToegangException;
use CsrDelft\model\entity\Afbeelding;
use CsrDelft\model\entity\fotoalbum\Foto;
use CsrDelft\model\entity\fotoalbum\FotoAlbum;
use CsrDelft\model\entity\Plaatje;
use CsrDelft\view\plaatjes\PlaatjesUploadModalForm;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PlaatjesController {
	public function upload() {
		$form = new PlaatjesUploadModalForm();
		if ($form->isPosted()) {
			$plaatje = Plaatje::create($form->uploader);
			return view('forum/insert_plaatje', ['plaatje' => $plaatje]);
		} else {
			return $form;
		}
	}

	public function bekijken($id) {
		$plaatje = new Plaatje($id);
		$image = $plaatje->getAfbeelding();
		if (!$image->exists()) {
			throw new NotFoundHttpException();
		}
		$response = new BinaryFileResponse($image->getFullPath());
		$response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE);
		return $response;
	}
}
