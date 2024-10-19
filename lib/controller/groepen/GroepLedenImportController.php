<?php

namespace CsrDelft\controller\groepen;

use Symfony\Component\Routing\Attribute\Route;
use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\FlashType;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\GroepLedenImportDTO;
use CsrDelft\repository\GroepLidRepository;
use CsrDelft\repository\ProfielRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use ParseCsv\Csv;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class GroepLedenImportController extends AbstractController
{
	/**
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/groepimport', name: 'groepimport')]
	public function groepimport(): Response
	{
		return $this->render('groepen/groepimport.html.twig', []);
	}

	private function quickMelding($melding, $url = null): RedirectResponse
	{
		$this->addFlash(FlashType::WARNING, $melding);
		if (!$url) {
			$url = $this->generateUrl('groepimport');
		}
		return $this->redirect($url);
	}

	/**
	 * @Auth(P_LOGGED_IN)
	 * @param Request $request
	 * @param Session $session
	 * @return Response
	 */
	#[
		Route(
			path: '/groepimport/upload',
			name: 'groepimport_upload',
			methods: ['POST']
		)
	]
	public function upload(Request $request, Session $session): Response
	{
		// Kijk of bestand meegegeven is
		if (!$request->files->has('csv')) {
			return $this->quickMelding('Upload een CSV');
		}

		// Kijk of bestand CSV is
		/** @var UploadedFile $file */
		$file = $request->files->get('csv');
		if (!$file) {
			return $this->quickMelding('Geen bestand gekozen');
		}

		if (
			!in_array($file->getMimeType(), [
				'text/plain',
				'text/csv',
				'application/vnd.ms-excel',
			])
		) {
			return $this->quickMelding('Alleen een CSV is toegestaan');
		}

		// Parse CSV
		$csv = new Csv();
		if ($csv->auto($file->getPathname()) === false) {
			return $this->quickMelding('Fout bij inlezen van CSV');
		}
		$data = $csv->data;

		// Controleer of er regels zijn en eerste regel geldige keys heeft
		if (empty($data)) {
			return $this->quickMelding('Geen regels gevonden');
		}
		if (array_keys($data[0]) !== ['groepID', 'uid', 'opmerking']) {
			return $this->quickMelding('Ongeldige kolommen in de CSV');
		}

		// Sla data op in sessie
		$key = uniqid();
		$session->set("groepimport-$key", $data);

		// Redirect naar check pagina
		return $this->redirectToRoute('groepimport_controle', ['key' => $key]);
	}

	/**
	 * @Auth(P_LOGGED_IN)
	 * @param string $key
	 * @param Session $session
	 * @param ProfielRepository $profielRepository
	 * @param EntityManagerInterface $em
	 * @return Response
	 */
	#[Route(path: '/groepimport/controle/{key}', name: 'groepimport_controle')]
	public function controle(
		string $key,
		Session $session,
		ProfielRepository $profielRepository,
		EntityManagerInterface $em
	) {
		$groepRepository = $em->getRepository(Groep::class);

		// Haal data op
		$data = $session->get("groepimport-$key");
		if (empty($data)) {
			return $this->quickMelding(
				'Er ging iets fout bij het inladen van de CSV'
			);
		}

		// Ga regels langs
		$groeplidRegels = GroepLedenImportDTO::convert(
			$profielRepository,
			$groepRepository,
			$data
		);
		$aantalSucces = count(
			array_filter(
				$groeplidRegels,
				fn(GroepLedenImportDTO $dto) => $dto->succes
			)
		);
		$aantalGefaald = count($groeplidRegels) - $aantalSucces;

		// Overzicht tonen
		return $this->render('groepen/groepimport-overzicht.html.twig', [
			'key' => $key,
			'aantalSucces' => $aantalSucces,
			'aantalGefaald' => $aantalGefaald,
			'groeplidRegels' => $groeplidRegels,
		]);
	}

	/**
	 * @Auth(P_LOGGED_IN)
	 * @param string $key
	 * @param Session $session
	 * @param ProfielRepository $profielRepository
	 * @param GroepLidRepository $groepLidRepository
	 * @param Request $request
	 * @param EntityManagerInterface $em
	 * @return Response
	 * @throws \Doctrine\DBAL\Exception
	 */
	#[
		Route(
			path: '/groepimport/verwerk/{key}',
			name: 'groepimport_verwerk',
			methods: ['POST']
		)
	]
	public function verwerk(
		string $key,
		Session $session,
		ProfielRepository $profielRepository,
		GroepLidRepository $groepLidRepository,
		Request $request,
		EntityManagerInterface $em
	): Response {
		$groepRepository = $em->getRepository(Groep::class);

		// Haal data op
		$data = $session->get("groepimport-$key");
		if (empty($data)) {
			return $this->quickMelding(
				'Er ging iets fout bij het inladen van de CSV'
			);
		}

		// Zet lock
		if ($session->has("groepimport-$key-locked")) {
			return $this->quickMelding('Deze CSV wordt al verwerkt');
		} else {
			$session->set("groepimport-$key-locked", true);
		}

		if (
			!$request->request->has('gecheckt') ||
			!$request->request->has('foutenAkkoord')
		) {
			$session->remove("groepimport-$key-locked");
			return $this->quickMelding(
				'Geef akkoord voor verwerking',
				$this->generateUrl('groepimport_controle', ['key' => $key])
			);
		}

		// Ga regels langs
		$aantalSucces = 0;

		$em->getConnection()->beginTransaction();
		try {
			foreach ($data as $regel) {
				$regel = new GroepLedenImportDTO(
					$profielRepository,
					$groepRepository,
					$regel
				);
				$aangemaakt = $regel->aanmelden($groepLidRepository);
				if ($aangemaakt) {
					$aantalSucces++;
				}
			}

			$em->getConnection()->commit();
			$session->remove("groepimport-$key");
		} catch (Exception) {
			$em->getConnection()->rollBack();
		}

		$session->remove("groepimport-$key-lock");

		// Overzicht tonen
		return $this->render('groepen/groepimport-succes.html.twig', [
			'aantalSucces' => $aantalSucces,
		]);
	}

	/**
	 * @Auth(P_LOGGED_IN)
	 * @return Response
	 */
	#[Route(path: '/groepimport/template', name: 'groepimport_template')]
	public function downloadTemplate(): Response
	{
		$template = "groepID;uid;opmerking\r\n1234;x101;Leider";
		$response = new Response($template);
		$disposition = HeaderUtils::makeDisposition(
			HeaderUtils::DISPOSITION_ATTACHMENT,
			'groepimport.csv'
		);
		$response->headers->set('Content-Type', 'text/csv');
		$response->headers->set('Content-Disposition', $disposition);
		return $response;
	}
}
