<?php


namespace CsrDelft\controller;


use CsrDelft\repository\ForumPlaatjeRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\plaatjes\PlaatjesUploadModalForm;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ForumPlaatjesController {
	/** @var ForumPlaatjeRepository  */
	private $forumPlaatjeRepository;

	public function __construct(ForumPlaatjeRepository $forumPlaatjeRepository) {
		$this->forumPlaatjeRepository = $forumPlaatjeRepository;
	}

	public function upload() {
		$form = new PlaatjesUploadModalForm();
		if ($form->isPosted()) {
			$plaatje = $this->forumPlaatjeRepository->fromUploader($form->uploader, LoginService::getUid());
			return view('forum.insert_plaatje', ['plaatje' => $plaatje]);
		} else {
			return $form;
		}
	}

	public function bekijken($id, $resized=false) {
		$plaatje = $this->forumPlaatjeRepository->getByKey($id);
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
