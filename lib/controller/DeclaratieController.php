<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\repository\declaratie\DeclaratieBonRepository;
use CsrDelft\view\formulier\uploadvelden\UploadFileField;
use Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class DeclaratieController extends AbstractController {
	/**
	 * @return Response
	 * @Route("/declaratie/nieuw", name="declaratie_nieuw", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function nieuw() {
		$lid = $this->getProfiel();
		return $this->render('declaratie/nieuw.html.twig', [
			'iban' => $lid->bankrekening,
			'tenaamstelling' => $lid->getNaam('voorletters')
		]);
	}

	/**
	 * @param string $filename
	 * @param Filesystem $filesystem
	 * @return Response
	 * @Route("/declaratie/download/{filename}", name="declaratie_download", methods={"GET"}, requirements={"filename"="[a-f0-9]+.[a-z]+"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function download(string $filename, Filesystem $filesystem) {
		$filename = DECLARATIE_PATH . $filename;
		if (!$filesystem->exists($filename)) {
			throw new NotFoundHttpException();
		}

		$response = new BinaryFileResponse($filename);
		$response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE);
		return $response;
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @Route("/declaratie/upload", name="declaratie_upload", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 * @throws Exception
	 */
	public function upload(Request $request, DeclaratieBonRepository $bonRepository) {
		$key = bin2hex(random_bytes(16));

		/** @var File $file */
		$file = $request->files->get('bon');
		if (!$file) {
			throw new BadRequestHttpException('Geen bestand geselecteerd');
		}

		$allowedMimes = [
			'application/pdf',
			'application/x-pdf',
			'text/pdf',
			'image/jpeg',
			'image/png',
		];
		$uploadFileField = new UploadFileField('bon', $allowedMimes);
		if (!$uploadFileField->validate()) {
			throw new BadRequestHttpException($uploadFileField->error);
		}

		$filename = $key . '.' . $file->guessExtension();
		$uploadFileField->opslaan(DECLARATIE_PATH, $filename);

		$bon = $bonRepository->generate($filename, $this->getProfiel());
		return $this->json([
			'file' => $this->generateUrl('declaratie_download', ['filename' => $filename]),
			'id' => $bon->getId(),
		]);
	}
}
