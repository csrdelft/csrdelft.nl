<?php

namespace CsrDelft\controller\forum;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\repository\ForumPlaatjeRepository;
use CsrDelft\view\plaatjes\PlaatjesUploadModalForm;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ForumPlaatjesController extends AbstractController
{
	/** @var ForumPlaatjeRepository  */
	private $forumPlaatjeRepository;

	public function __construct(ForumPlaatjeRepository $forumPlaatjeRepository)
	{
		$this->forumPlaatjeRepository = $forumPlaatjeRepository;
	}

	/**
  * @Auth(P_LOGGED_IN)
  * @return JsonResponse
  * @throws ORMException
  * @throws OptimisticLockException
  */
 #[Route(path: '/forum/plaatjes/upload_json', methods: ['POST'])]
 public function uploadJson(): JsonResponse
	{
		$form = new PlaatjesUploadModalForm();
		if ($form->isPosted()) {
			$plaatje = $this->forumPlaatjeRepository->fromUploader(
				$form->uploader,
				$this->getUid()
			);
			return new JsonResponse([
				'key' => $plaatje->access_key,
				'src' => $this->generateUrl('csrdelft_forum_forumplaatjes_bekijken', [
					'id' => $plaatje->access_key,
					'resized' => true,
				]),
			]);
		} else {
			throw new BadRequestHttpException('Niet gepost');
		}
	}

	/**
  * @param $id
  * @param bool $resized
  * @return BinaryFileResponse
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/forum/plaatjes/bekijken/{id}', methods: ['GET'], requirements: ['id' => '[a-zA-Z0-9]*'])]
 #[Route(path: '/forum/plaatjes/bekijken/{id}/resized', methods: ['GET'], requirements: ['id' => '[a-zA-Z0-9]*'], defaults: ['resized' => true])]
 public function bekijken($id, $resized = false): BinaryFileResponse
	{
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
