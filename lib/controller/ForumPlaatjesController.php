<?php


namespace CsrDelft\controller;


use CsrDelft\common\CsrToegangException;
use CsrDelft\model\entity\Afbeelding;
use CsrDelft\model\entity\fotoalbum\Foto;
use CsrDelft\model\entity\fotoalbum\FotoAlbum;
use CsrDelft\model\entity\ForumPlaatje;
use CsrDelft\model\ForumPlaatjeModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\plaatjes\PlaatjesUploadModalForm;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ForumPlaatjesController {
	/** @var ForumPlaatjeModel  */
	private $forumPlaatjeModel;

	public function __construct(ForumPlaatjeModel $forumPlaatjeModel) {
		$this->forumPlaatjeModel = $forumPlaatjeModel;
	}

	public function upload() {
		$form = new PlaatjesUploadModalForm();
		if ($form->isPosted()) {
			$plaatje = $this->forumPlaatjeModel::fromUploader($form->uploader, LoginModel::getUid());
			return view('forum.insert_plaatje', ['plaatje' => $plaatje]);
		} else {
			return $form;
		}
	}

	public function bekijken($id, $resized=false) {
		$plaatje = $this->forumPlaatjeModel->getByKey($id);
		if (!$plaatje) {
			throw new NotFoundHttpException();
		}
		$image = $plaatje->getAfbeelding($resized);
		if (!$image->exists()) {
			throw new NotFoundHttpException();
		}
		$response = new BinaryFileResponse($image->getFullPath());
		$response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE);
		return $response;
	}
}
