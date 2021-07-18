<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\entity\declaratie\Declaratie;
use CsrDelft\repository\declaratie\DeclaratieBonRepository;
use CsrDelft\repository\declaratie\DeclaratieCategorieRepository;
use CsrDelft\repository\declaratie\DeclaratieRegelRepository;
use CsrDelft\repository\declaratie\DeclaratieRepository;
use CsrDelft\view\formulier\uploadvelden\UploadFileField;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
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
	 * @param DeclaratieBonRepository $bonRepository
	 * @return Response
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/declaratie/upload", name="declaratie_upload", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
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

	/**
	 * @param string[] $messages
	 * @param int $id
	 * @return JsonResponse
	 */
	private function ajaxResponse(array $messages, int $id = null): JsonResponse
	{
		return $this->json([
			'success' => !empty($messages),
			'id' => $id,
			'messages' => $messages,
		]);
	}

	/**
	 * @param Request $request
	 * @param DeclaratieRepository $declaratieRepository
	 * @param DeclaratieBonRepository $bonRepository
	 * @param DeclaratieRegelRepository $regelRepository
	 * @param DeclaratieCategorieRepository $categorieRepository
	 * @param EntityManagerInterface $entityManager
	 * @return Response
	 * @Route("/declaratie/opslaan", name="declaratie_opslaan", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function opslaan(Request $request,
													DeclaratieRepository $declaratieRepository,
													DeclaratieBonRepository $bonRepository,
													DeclaratieRegelRepository $regelRepository,
													DeclaratieCategorieRepository $categorieRepository,
													EntityManagerInterface $entityManager) {
		$data = $request->request->get('declaratie');
		if (!empty($data)) {
			$data = new ParameterBag($data);
		}
		$messages = [];

		// Laad declaratie of maak nieuwe
		$declaratie = null;
		if ($data->getInt('id', 0)) {
			$declaratie = $declaratieRepository->find(intval($data->id));
		}

		if ($declaratie) {
			if ($declaratie->getIndiener()->uid !== $this->getUid()
			    || $declaratie->isIngediend()) {
				return $this->ajaxResponse(['Je mag deze declaratie niet aanpassen']);
			}
		} else {
			$declaratie = new Declaratie();
			$declaratie->setIndiener($this->getProfiel());
			$entityManager->persist($declaratie);
		}

		// Declaratie-eigenschappen
		$categorie = $categorieRepository->find($data->getInt('categorie'));
		if (!$categorie) {
			return $this->ajaxResponse(['Selecteer een categorie voor deze declaratie']);
		}

		if ($data->get('omschrijving')) {
			$declaratie->setOmschrijving($data->get('omschrijving'));
		}

		if ($data->get('betaalwijze') === 'C.S.R.-pas') {
			$declaratie->setCsrPas(true);
			$declaratie->setNaam($data->get('tnv'));
		} else {
			$declaratie->setCsrPas(false);
			if ($data->getBoolean('eigenRekening') === true) {
				$declaratie->setRekening($declaratie->getIndiener()->bankrekening);
				$declaratie->setNaam($declaratie->getIndiener()->getNaam('voorletters'));
			} else {
				$declaratie->setRekening($data->get('rekening'));
				$declaratie->setNaam($data->get('tnv'));
			}
		}

		$declaratie->setOpmerkingen($data->get('opmerkingen', ''));
		$declaratie->setTotaal(0);
		$entityManager->flush();

		// Voeg bonnen toe
		if (is_array($data->get('bonnen'))) {
			foreach ($data->get('bonnen') as $bon) {
				$bonData = new ParameterBag($bon);
				$bon = $bonRepository->find($bonData->getInt('id'));
				if ($bon->getMaker()->uid !== $this->getUid()
					|| ($bon->getDeclaratie()->getId() !== null && $bon->getDeclaratie()->getId() !== $declaratie->getId())) {
					$messages[] = 'Een van de bonnen kan niet gebruikt worden in deze declaratie';
					continue;
				}

				// Voeg regels toe



			}
		} else {
			$messages[] = 'Voeg bonnen toe aan de declaratie';
		}


		// Verwijder ontkoppelde bonnen

		// Sla declaratie op
		if (empty($messages)) {
			$declaratie->setIngediend($data->get('status') === 'ingediend');
		}

		$entityManager->flush();
		return $this->ajaxResponse($messages, $declaratie->getId());
	}
}
