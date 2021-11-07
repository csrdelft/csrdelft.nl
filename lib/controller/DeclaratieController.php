<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\entity\declaratie\Declaratie;
use CsrDelft\entity\declaratie\DeclaratieRegel;
use CsrDelft\entity\declaratie\DeclaratieWachtrij;
use CsrDelft\repository\declaratie\DeclaratieBonRepository;
use CsrDelft\repository\declaratie\DeclaratieCategorieRepository;
use CsrDelft\repository\declaratie\DeclaratieRepository;
use CsrDelft\repository\declaratie\DeclaratieWachtrijRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\ProfielService;
use CsrDelft\service\security\LoginService;
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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DeclaratieController extends AbstractController
{
	/**
	 * @Route("/declaratie/mijn/{uid}", name="declaraties_mijn")
	 * @Auth(P_LOGGED_IN)
	 */
	public function lijstMijn(DeclaratieRepository $declaratieRepository, string $uid = null): Response {
		$profiel = $uid ? ProfielRepository::get($uid) : LoginService::getProfiel();
		if (!$profiel) {
			throw $this->createNotFoundException();
		}

		return $this->render('declaratie/lijst.html.twig', [
			'titel' => $uid ? 'Declaraties van ' . $profiel->getNaam() : 'Mijn declaraties',
			'declaraties' => $declaratieRepository->mijnDeclaraties($profiel),
			'personal' => true,
		]);
	}

	/**
	 * @Route("/declaratie/wachtrij", name="declaraties_wachtrijen")
	 * @Auth(P_LOGGED_IN)
	 */
	public function wachtrijen(DeclaratieWachtrijRepository $declaratieWachtrijRepository): Response {
		$wachtrijen = $declaratieWachtrijRepository->mijnWachtrijen();
		$wachtrijCounts = [];
		foreach ($wachtrijen as $wachtrij) {
			$wachtrijCounts[$wachtrij->getId()]['concept'] = count($declaratieWachtrijRepository->filterDeclaraties($wachtrij, ['concept']));
			$wachtrijCounts[$wachtrij->getId()]['beoordelen'] = count($declaratieWachtrijRepository->filterDeclaraties($wachtrij, ['ingediend']));
			$wachtrijCounts[$wachtrij->getId()]['uitbetalen'] = count($declaratieWachtrijRepository->filterDeclaraties($wachtrij, ['uitbetaald']));
		}

		return $this->render('declaratie/wachtrijen.html.twig', [
			'wachtrijen' => $wachtrijen,
			'counts' => $wachtrijCounts,
		]);
	}

	/**
	 * @Route("/declaratie/wachtrij/{wachtrij}", name="declaraties_wachtrij")
	 * @Auth(P_LOGGED_IN)
	 */
	public function lijstWachtrij(DeclaratieWachtrij $wachtrij): Response {


		return $this->render('declaratie/lijst.html.twig', [
			'titel' => 'Wachtrij' . $wachtrij->getNaam(),
			'declaraties' => $declaratieRepository->mijnDeclaraties($profiel),
			'personal' => false,
		]);
	}

	/**
	 * @param DeclaratieCategorieRepository $categorieRepository
	 * @return Response
	 * @Route("/declaratie/nieuw", name="declaratie_nieuw", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function nieuw(DeclaratieCategorieRepository $categorieRepository): Response
	{
		$lid = $this->getProfiel();
		$categorieLijst = $categorieRepository->findTuples();
		return $this->render('declaratie/detail.html.twig', [
			'categorieLijst' => $categorieLijst,
			'iban' => $lid->bankrekening,
			'tenaamstelling' => $lid->getNaam('voorletters'),
			'email' => $lid->getPrimaryEmail(),
			'declaratie' => false,
		]);
	}

	/**
	 * @param Declaratie $declaratie
	 * @param DeclaratieCategorieRepository $categorieRepository
	 * @param UrlGeneratorInterface $generator
	 * @return Response
	 * @Route("/declaratie/{declaratie}", name="declaratie_detail", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function detail(Declaratie $declaratie, DeclaratieCategorieRepository $categorieRepository, UrlGeneratorInterface $generator): Response
	{
		if (!$declaratie->magBekijken()) {
			throw $this->createAccessDeniedException();
		}

		$lid = $declaratie->getIndiener();
		$categorieLijst = $categorieRepository->findTuples();
		return $this->render('declaratie/detail.html.twig', [
			'categorieLijst' => $categorieLijst,
			'iban' => $lid->bankrekening,
			'tenaamstelling' => $lid->getNaam('voorletters'),
			'email' => $lid->getPrimaryEmail(),
			'declaratie' => $declaratie->naarObject($generator),
		]);
	}

	/**
	 * @param string $path
	 * @param Filesystem $filesystem
	 * @param DeclaratieBonRepository $bonRepository
	 * @return Response
	 * @Route("/declaratie/download/{path}", name="declaratie_download", methods={"GET"}, requirements={"filename"="[a-f0-9]+.[a-z]+"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function download(string $path, Filesystem $filesystem, DeclaratieBonRepository $bonRepository)
	{
		$filename = DECLARATIE_PATH . $path;
		if (!$filesystem->exists($filename)) {
			throw $this->createAccessDeniedException();
		}

		$bon = $bonRepository->findOneBy(['bestand' => $path]);
		if (!$bon || !$bon->magBekijken()) {
			throw $this->createAccessDeniedException();
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
	public function upload(Request $request, DeclaratieBonRepository $bonRepository)
	{
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
			'file' => $this->generateUrl('declaratie_download', ['path' => $filename]),
			'id' => $bon->getId(),
		]);
	}

	/**
	 * @param array $messages
	 * @param Declaratie|null $declaratie
	 * @return JsonResponse
	 */
	private function ajaxResponse(array $messages, Declaratie $declaratie = null): JsonResponse
	{
		return $this->json([
			'success' => empty($messages),
			'id' => $declaratie ? $declaratie->getId() : null,
			'status' => $declaratie ? $declaratie->getStatus() : 'concept',
			'statusData' => $declaratie ? $declaratie->naarStatusData() : null,
			'messages' => $messages,
		]);
	}

	/**
	 * @param Request $request
	 * @param DeclaratieRepository $declaratieRepository
	 * @param DeclaratieBonRepository $bonRepository
	 * @param DeclaratieCategorieRepository $categorieRepository
	 * @param EntityManagerInterface $entityManager
	 * @return Response
	 * @Route("/declaratie/opslaan", name="declaratie_opslaan", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function opslaan(Request $request,
													DeclaratieRepository $declaratieRepository,
													DeclaratieBonRepository $bonRepository,
													DeclaratieCategorieRepository $categorieRepository,
													EntityManagerInterface $entityManager)
	{
		$data = $request->request->get('declaratie');
		if (!empty($data)) {
			$data = new ParameterBag($data);
		}
		$messages = [];

		// Laad declaratie of maak nieuwe
		if ($data->getInt('id')) {
			$declaratie = $declaratieRepository->find($data->getInt('id'));
			if (!$declaratie || !$declaratie->magBewerken()) {
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

		$declaratie->fromParameters($data);
		$declaratie->setTotaal(0);
		if ($declaratie->getId() === null) {
			$declaratie->setCategorie($categorie);
		}
		$entityManager->flush();

		// Voeg bonnen toe
		foreach ($declaratie->getBonnen() as $bon) {
			$declaratie->removeBon($bon);
		}

		if (is_array($data->get('bonnen'))) {
			foreach ($data->get('bonnen') as $rawBon) {
				$bonData = new ParameterBag($rawBon);
				$bon = $bonRepository->find($bonData->getInt('id'));
				if (!($bon->magBekijken() || $declaratie->magBeoordelen())
					|| ($bon->getDeclaratie() !== null && $bon->getDeclaratie()->getId() !== $declaratie->getId())) {
					$messages[] = 'Een van de bonnen kan niet gebruikt worden in deze declaratie';
					continue;
				}

				if (!$bon->getDeclaratie()) {
					$declaratie->addBon($bon);
				}

				$bon->fromParameters($bonData);

				// Haal bestaande regels op
				$regels = $bon->getRegels();

				// Voeg regels toe
				$index = 0;
				if (is_array($bonData->get('regels'))) {
					foreach ($bonData->get('regels') as $rawRegel) {
						$regelData = new ParameterBag($rawRegel);
						if (isset($regels[$index])) {
							$regel = $regels[$index];
						} else {
							$regel = new DeclaratieRegel();
							$bon->addRegel($regel);
							$entityManager->persist($regel);
						}

						$regel->fromParameters($regelData);
						$index++;
					}
				}

				// Haal niet-gebruikte regels weg
				for ($i = $index; $i < count($regels); $i++) {
					$entityManager->remove($regels[$i]);
				}
			}
		}

		// Sla declaratie op
		$declaratie->setTotaal($declaratie->getBedragIncl());
		$declaratie->setCategorie($categorie);
		$entityManager->flush();

		if ($request->request->getBoolean('indienen')) {
			$messages = array_merge($messages, $declaratie->valideer());
			if (empty($messages) && $declaratie->getIngediend() === null) {
				$declaratie->setIngediend(date_create_immutable());
			}
		}

		if ($declaratie->magBeoordelen()) {
			if (in_array($declaratie->getStatus(), ['ingediend', 'goedgekeurd', 'uitbetaald'])) {
				$declaratie->setNummer($data->getAlnum('nummer'));
			}

			if ($declaratie->getStatus() !== 'concept' && $data->has('datum')) {
				$datum = date_create_immutable($data->get('datum'));
				if ($datum) {
					$declaratie->setIngediend($datum);
				}
			}
		}

		$entityManager->flush();
		return $this->ajaxResponse($messages, $declaratie);
	}

	/**
	 * @param Declaratie $declaratie
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @return Response
	 * @Route("/declaratie/status/{declaratie}", name="declaratie_status", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function setStatus(Declaratie $declaratie, Request $request, EntityManagerInterface $entityManager) {
		$status = $request->request->getAlpha('status');
		$vanNaar = $declaratie->getStatus() . '-' . $status;

		if (($declaratie->getStatus() === 'uitbetaald' || $status === 'uitbetaald') && !$declaratie->magUitbetalen()) {
			return $this->ajaxResponse(['Geen rechten om uitbetaling aan te passen'], $declaratie);
		} elseif (!$declaratie->magBeoordelen()) {
			return $this->ajaxResponse(['Geen rechten om declaratie te beoordelen'], $declaratie);
		}

		switch ($vanNaar) {
			case 'ingediend-concept':
				// Terug naar concept
				$declaratie->setIngediend(null);
				$declaratie->setNummer(null);
				break;
			case 'ingediend-goedgekeurd':
			case 'ingediend-afgekeurd':
				// Goedkeuren / afkeuren
				$declaratie->setBeoordeeld(date_create_immutable());
				$declaratie->setBeoordelaar($this->getProfiel());
				$declaratie->setGoedgekeurd($status === 'goedgekeurd');
				if ($declaratie->isGoedgekeurd() && !empty($request->request->getAlnum('nummer'))) {
					$declaratie->setNummer($request->request->getAlnum('nummer'));
				} else {
					$declaratie->setNummer(null);
				}
				break;
			case 'goedgekeurd-ingediend':
			case 'afgekeurd-ingediend':
				// Terug naar ingediend
				$declaratie->setBeoordeeld(null);
				$declaratie->setBeoordelaar(null);
				$declaratie->setGoedgekeurd(false);
				break;
			case 'goedgekeurd-uitbetaald':
				// Uitbetalen
				$declaratie->setUitbetaald(date_create_immutable());
				break;
			case 'uitbetaald-goedgekeurd':
				// Uitbetaald ongedaan maken
				$declaratie->setUitbetaald(null);
				break;
		}

		$entityManager->flush();
		return $this->ajaxResponse([], $declaratie);
	}

	/**
	 * @param Declaratie $declaratie
	 * @param DeclaratieRepository $declaratieRepository
	 * @return Response
	 * @Route("/declaratie/verwijderen/{declaratie}", name="declaratie_verwijderen", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function declaratieVerwijderen(Declaratie $declaratie, DeclaratieRepository $declaratieRepository) {

		if (!$declaratie->magBewerken()) {
			throw $this->createAccessDeniedException();
		}

		$redirect = $declaratie->getIndiener()->uid === $this->getProfiel()->uid ? '/declaratie/mijn' : '/declaratie/wachtrij';
		$declaratieRepository->verwijderen($declaratie);

		return $this->json([
			'redirect' => $redirect
		]);
	}
}
