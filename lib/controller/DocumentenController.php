<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\FlashType;
use CsrDelft\common\Util\FileUtil;
use CsrDelft\common\Util\PathUtil;
use CsrDelft\entity\documenten\Document;
use CsrDelft\entity\documenten\DocumentCategorie;
use CsrDelft\model\entity\Bestand;
use CsrDelft\repository\documenten\DocumentCategorieRepository;
use CsrDelft\repository\documenten\DocumentRepository;
use CsrDelft\view\documenten\DocumentBewerkenForm;
use CsrDelft\view\documenten\DocumentCategorieForm;
use CsrDelft\view\documenten\DocumentToevoegenForm;
use CsrDelft\view\Icon;
use CsrDelft\view\PlainView;
use Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class DocumentenController extends AbstractController
{
	public function __construct(
		private readonly DocumentRepository $documentRepository,
		private readonly DocumentCategorieRepository $documentCategorieRepository
	) {
	}

	/**
	 * Recente documenten uit alle categorieën tonen
	 * @Auth(P_DOCS_READ)
	 */
	#[Route(path: '/documenten', methods: ['GET'])]
	public function recenttonen(): Response
	{
		return $this->render('documenten/documenten.html.twig', [
			'categorien' => $this->documentCategorieRepository->findAll(),
		]);
	}

	/**
	 * @param Document $document
	 * @return JsonResponse|PlainView
	 * @Auth(P_DOCS_MOD)
	 */
	#[Route(path: '/documenten/verwijderen/{id}', methods: ['POST'])]
	public function verwijderen(Document $document)
	{
		$id = $document->id;
		if ($document->magVerwijderen()) {
			$this->documentRepository->remove($document);
		} else {
			$this->addFlash(FlashType::ERROR, 'Mag document niet verwijderen');
			return new JsonResponse(false);
		}

		return new PlainView(
			sprintf('<tr class="remove" id="document-%s"></tr>', $id)
		);
	}

	/**
	 * @param Document $document
	 * @return BinaryFileResponse|RedirectResponse
	 * @Auth(P_DOCS_READ)
	 */
	#[Route(path: '/documenten/bekijken/{id}/{bestandsnaam}', methods: ['GET'])]
	public function bekijken(Document $document)
	{
		if (!$document->magBekijken()) {
			throw $this->createAccessDeniedException();
		}

		//We do not allow serving html files because they can be used for XSS.
		//We do not allow serving javascript files because they can increase the impact of XSS by registering a service worker.
		if (
			$document->mimetype == 'text/html' ||
			$document->mimetype == 'text/javascript' ||
			!FileUtil::checkMimetype($document->filename, $document->mimetype)
		) {
			$this->addFlash(
				FlashType::ERROR,
				'Dit type bestand kan niet worden getoond'
			);
			return $this->redirectToRoute('csrdelft_documenten_recenttonen');
		}

		if ($document->hasFile()) {
			return new BinaryFileResponse($document->getFullPath());
		} else {
			$this->addFlash(FlashType::ERROR, 'Document heeft geen bestand.');
			return $this->redirectToRoute('csrdelft_documenten_recenttonen');
		}
	}

	/**
	 * @param Document $document
	 * @return BinaryFileResponse|RedirectResponse
	 * @Auth(P_DOCS_READ)
	 */
	#[Route(path: '/documenten/download/{id}/{bestandsnaam}', methods: ['GET'])]
	public function download(Document $document)
	{
		if (!$document->magBekijken()) {
			throw $this->createAccessDeniedException();
		}

		if ($document->hasFile()) {
			$response = new BinaryFileResponse($document->getFullPath());
			$response->setContentDisposition(
				ResponseHeaderBag::DISPOSITION_ATTACHMENT,
				$document->filename
			);
			return $response;
		} else {
			$this->addFlash(FlashType::ERROR, 'Document heeft geen bestand.');
			return $this->redirectToRoute('csrdelft_documenten_recenttonen');
		}
	}

	/**
	 * @param DocumentCategorie $categorie
	 * @return Response
	 * @Auth(P_DOCS_READ)
	 */
	#[
		Route(
			path: '/documenten/categorie/{id}',
			methods: ['GET'],
			requirements: ['id' => '\d+']
		)
	]
	public function categorie(DocumentCategorie $categorie): Response
	{
		if (!$categorie->magBekijken()) {
			throw $this->createAccessDeniedException(
				'Mag deze categorie niet bekijken'
			);
		} else {
			return $this->render('documenten/categorie.html.twig', [
				'categorie' => $categorie,
			]);
		}
	}

	/**
	 * @param Request $request
	 * @param DocumentCategorie|null $categorie
	 * @return JsonResponse|Response
	 * @Auth(P_DOCS_MOD)
	 */
	#[
		Route(path: '/documenten/categorie/{id}/bewerken', methods: ['GET', 'POST'])
	]
	public function categorieBewerken(
		Request $request,
		DocumentCategorie $categorie
	) {
		$form = $this->createFormulier(DocumentCategorieForm::class, $categorie, [
			'action' => $this->generateUrl('csrdelft_documenten_categoriebewerken', [
				'id' => $categorie->id,
			]),
		]);

		$form->handleRequest($request);

		if ($form->isPosted() && $form->validate()) {
			$this->getDoctrine()
				->getManager()
				->flush();
			return new JsonResponse(true);
		} else {
			// Voorkom opslaan
			$this->getDoctrine()
				->getManager()
				->clear();
			return new Response($form->createModalView());
		}
	}

	/**
	 * @param Request $request
	 * @Auth(P_DOCS_MOD)
	 * @return JsonResponse|Response
	 */
	#[Route(path: '/documenten/categorie/nieuw', methods: ['GET', 'POST'])]
	public function categorieAanmaken(Request $request)
	{
		$categorie = new DocumentCategorie();
		$form = $this->createFormulier(DocumentCategorieForm::class, $categorie, [
			'action' => $this->generateUrl('csrdelft_documenten_categorieaanmaken'),
		]);

		$form->handleRequest($request);

		if ($form->isPosted() && $form->validate()) {
			$this->getDoctrine()
				->getManager()
				->persist($categorie);
			$this->getDoctrine()
				->getManager()
				->flush();
			return new JsonResponse(true);
		} else {
			// Voorkom opslaan
			$this->getDoctrine()
				->getManager()
				->clear();
			return new Response($form->createModalView());
		}
	}

	/**
	 * @param DocumentCategorie $categorie
	 * @Auth(P_DOCS_MOD)
	 * @return JsonResponse
	 */
	#[Route(path: '/documenten/categorie/{id}/verwijderen', methods: ['POST'])]
	public function categorieVerwijderen(
		DocumentCategorie $categorie
	): JsonResponse {
		$this->getDoctrine()
			->getManager()
			->remove($categorie);
		$this->getDoctrine()
			->getManager()
			->flush();

		return new JsonResponse(
			$this->generateUrl('csrdelft_documenten_recenttonen')
		);
	}

	/**
	 * @param Request $request
	 * @param Document $document
	 * @return Response
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/documenten/bewerken/{id}', methods: ['GET', 'POST'])]
	public function bewerken(Request $request, Document $document): Response
	{
		if (!$document->magBewerken()) {
			throw $this->createAccessDeniedException();
		}
		$form = $this->createFormulier(DocumentBewerkenForm::class, $document, [
			'action' => $this->generateUrl('csrdelft_documenten_bewerken', [
				'id' => $document->id,
			]),
		]);

		$form->handleRequest($request);

		if ($form->isPosted() && $form->validate()) {
			$this->documentRepository->save($document);

			return $this->redirectToRoute('csrdelft_documenten_categorie', [
				'id' => $document->categorie->id,
			]);
		} else {
			return $this->render('default.html.twig', [
				'titel' => 'Document bewerken',
				'content' => $form->createView(),
			]);
		}
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @throws Exception
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/documenten/toevoegen', methods: ['GET', 'POST'])]
	public function toevoegen(Request $request): Response
	{
		$document = new Document();

		$catId = $request->query->getInt('catID');
		if ($catId) {
			$document->categorie = $this->getDoctrine()
				->getManager()
				->getReference(DocumentCategorie::class, $catId);
		}

		$form = $this->createFormulier(DocumentToevoegenForm::class, $document, [
			'action' => $this->generateUrl('csrdelft_documenten_toevoegen'),
		]);

		$form->handleRequest($request);

		if ($form->isPosted() && $form->validate()) {
			$document->eigenaar = $this->getUid();
			$document->eigenaar_profiel = $this->getProfiel();
			$document->toegevoegd = date_create_immutable();

			/** @var Bestand $bestand */
			$bestand = $form->getField('uploader')->getModel();

			$document->filename = PathUtil::direncode($bestand->filename);
			$document->mimetype = $bestand->mimetype;
			$document->filesize = $bestand->filesize;

			$this->documentRepository->save($document);

			try {
				if ($document->hasFile()) {
					$document->deleteFile();
				}

				$form
					->getField('uploader')
					->opslaan($document->getPath(), $document->getFullFileName());
			} catch (Exception $exception) {
				$this->documentRepository->remove($document);
				throw $exception;
			}

			return $this->redirectToRoute('csrdelft_documenten_categorie', [
				'id' => $document->categorie->id,
			]);
		} else {
			return $this->render('default.html.twig', [
				'titel' => 'Document toevoegen',
				'content' => $form->createView(),
			]);
		}
	}

	/**
	 * @param Request $request
	 * @param null $zoekterm
	 * @return JsonResponse
	 * @Auth(P_DOCS_READ)
	 */
	#[Route(path: '/documenten/zoeken', methods: ['GET', 'POST'])]
	public function zoeken(Request $request, $zoekterm = null): JsonResponse
	{
		if (!$zoekterm && !$request->query->has('q')) {
			throw $this->createAccessDeniedException();
		}
		if (!$zoekterm) {
			$zoekterm = $request->query->get('q');
		}

		$limit = $request->query->getInt('limit', 5);

		$result = [];
		foreach ($this->documentRepository->zoek($zoekterm, $limit) as $doc) {
			if ($doc->magBekijken()) {
				$result[] = [
					'url' => '/documenten/bekijken/' . $doc->id . '/' . $doc->filename,
					'label' => $doc->categorie->naam,
					'value' => $doc->naam,
					'icon' => Icon::getTag('document'),
					'id' => $doc->id,
				];
			}
		}
		return new JsonResponse($result);
	}
}
