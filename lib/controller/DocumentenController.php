<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\entity\documenten\Document;
use CsrDelft\entity\documenten\DocumentCategorie;
use CsrDelft\repository\documenten\DocumentCategorieRepository;
use CsrDelft\repository\documenten\DocumentRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\documenten\DocumentBewerkenForm;
use CsrDelft\view\documenten\DocumentToevoegenForm;
use CsrDelft\view\Icon;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\PlainView;
use CsrDelft\view\renderer\TemplateView;
use Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class DocumentenController extends AbstractController {
	/** @var DocumentRepository */
	private $documentRepository;
	/** @var DocumentCategorieRepository */
	private $documentCategorieRepository;

	public function __construct(DocumentRepository $documentRepository, DocumentCategorieRepository $documentCategorieRepository) {
		$this->documentRepository = $documentRepository;
		$this->documentCategorieRepository = $documentCategorieRepository;
	}

	/**
	 * Recente documenten uit alle categorieën tonen
	 * @Route("/documenten", methods={"GET"})
	 * @Auth(P_DOCS_READ)
	 */
	public function recenttonen() {
		return view('documenten.documenten', ['categorieen' => $this->documentCategorieRepository->findAll()]);
	}

	/**
	 * @param Document $document
	 * @return JsonResponse|PlainView|RedirectResponse
	 * @Route("/doucmenten/verwijderen/{id}", methods={"POST"})
	 * @Auth(P_DOCS_MOD)
	 */
	public function verwijderen(Document $document) {
		if ($document->magVerwijderen()) {
			$this->documentRepository->remove($document);
		} else {
			setMelding('Mag document niet verwijderen', -1);
			return new JsonResponse(false);
		}

		return new PlainView(sprintf('<tr class="remove" id="document-%s"></tr>', $document->id));
	}

	/**
	 * @param Document $document
	 * @return BinaryFileResponse|RedirectResponse
	 * @Route("/documenten/bekijken/{id}/{bestandsnaam}", methods={"GET"})
	 * @Auth(P_DOCS_READ)
	 */
	public function bekijken(Document $document) {
		if (!$document->magBekijken()) {
			throw $this->createAccessDeniedException();
		}

		//We do not allow serving html files because they can be used for XSS.
		//We do not allow serving javascript files because they can increase the impact of XSS by registering a service worker.
		if ($document->mimetype == "text/html" || $document->mimetype == "text/javascript" || !checkMimetype($document->filename, $document->mimetype)) {
			setMelding('Dit type bestand kan niet worden getoond', -1);
			return $this->redirectToRoute('csrdelft_documenten_recenttonen');
		}

		if ($document->hasFile()) {
			return new BinaryFileResponse($document->getFullPath());
		} else {
			setMelding('Document heeft geen bestand.', -1);
			return $this->redirectToRoute('csrdelft_documenten_recenttonen');
		}
	}

	/**
	 * @param Document $document
	 * @return BinaryFileResponse|RedirectResponse
	 * @Route("/documenten/download/{id}/{bestandsnaam}", methods={"GET"})
	 * @Auth(P_DOCS_READ)
	 */
	public function download(Document $document) {
		if (!$document->magBekijken()) {
			throw $this->createAccessDeniedException();
		}

		if ($document->hasFile()) {
			$response = new BinaryFileResponse($document->getFullPath());
			$response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $document->filename);
			return $response;
		} else {
			setMelding('Document heeft geen bestand.', -1);
			return $this->redirectToRoute('csrdelft_documenten_recenttonen');
		}
	}

	/**
	 * @param DocumentCategorie $categorie
	 * @return TemplateView|RedirectResponse
	 * @Route("/documenten/categorie/{id}", methods={"GET"})
	 * @Auth(P_DOCS_READ)
	 */
	public function categorie(DocumentCategorie $categorie) {
		if (!$categorie->magBekijken()) {
			throw $this->createAccessDeniedException('Mag deze categorie niet bekijken');
		} else {
			return view('documenten.categorie', ['categorie' => $categorie]);
		}
	}

	/**
	 * @param Document $document
	 * @return TemplateView|RedirectResponse
	 * @Route("/documenten/bewerken/{id}", methods={"GET","POST"})
	 * @Auth(P_DOCS_MOD)
	 */
	public function bewerken(Document $document) {
		$form = new DocumentBewerkenForm($document, $this->documentCategorieRepository->getCategorieNamen());

		if ($form->isPosted() && $form->validate()) {
			$this->documentRepository->save($document);

			return $this->redirectToRoute('csrdelft_documenten_categorie', ['id' => $document->categorie->id]);
		} else {
			return view('default', [
				'titel' => 'Document bewerken',
				'content' => $form,
			]);
		}

	}

	/**
	 * @return TemplateView|RedirectResponse
	 * @Route("/documenten/toevoegen", methods={"GET","POST"})
	 * @Auth(P_DOCS_MOD)
	 */
	public function toevoegen() {
		$form = new DocumentToevoegenForm($this->documentCategorieRepository->getCategorieNamen());

		if ($form->isPosted() && $form->validate()) {
			/** @var Document $document */
			$document = $form->getModel();

			$document->eigenaar = LoginService::getUid();
			$document->eigenaar_profiel = LoginService::getProfiel();
			$document->toegevoegd = date_create_immutable();

			$bestand = $form->getUploader()->getModel();

			$document->filename = filter_filename($bestand->filename);
			$document->mimetype = $bestand->mimetype;
			$document->filesize = $bestand->filesize;



			$this->documentRepository->save($document);

			try {
			if ($document->hasFile()) {
				$document->deleteFile();
			}

			$form->getUploader()->opslaan($document->getPath(), $document->getFullFileName());
			}
			catch (Exception $exception) {
				$this->documentRepository->remove($document);
				throw $exception;
			}


			return $this->redirectToRoute('csrdelft_documenten_categorie', ['id' => $document->categorie->id]);
		} else {
			return view('default', [
				'titel' => 'Document toevoegen',
				'content' => $form,
			]);
		}
	}

	/**
	 * @param Request $request
	 * @param null $zoekterm
	 * @return JsonResponse
	 * @Route("/documenten/zoeken", methods={"GET","POST"})
	 * @Auth(P_DOCS_READ)
	 */
	public function zoeken(Request $request, $zoekterm = null) {
		if (!$zoekterm && !$request->query->has('q')) {
			throw $this->createAccessDeniedException();
		}
		if (!$zoekterm) {
			$zoekterm = $request->query->get('q');
		}

		$limit = $request->query->getInt('limit', 5);

		$result = array();
		foreach ($this->documentRepository->zoek($zoekterm, $limit) as $doc) {
			if ($doc->magBekijken()) {
				$result[] = array(
					'url' => '/documenten/bekijken/' . $doc->id . '/' . $doc->filename,
					'label' => $doc->categorie->naam,
					'value' => $doc->naam,
					'icon' => Icon::getTag('document'),
					'id' => $doc->id
				);
			}
		}
		return new JsonResponse($result);
	}
}
