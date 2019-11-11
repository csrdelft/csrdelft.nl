<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrToegangException;
use CsrDelft\model\documenten\DocumentCategorieModel;
use CsrDelft\model\documenten\DocumentModel;
use CsrDelft\model\entity\documenten\Document;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\documenten\DocumentBewerkenForm;
use CsrDelft\view\documenten\DocumentToevoegenForm;
use CsrDelft\view\Icon;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\PlainView;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class DocumentenController extends AbstractController {
	/** @var DocumentModel */
	private $documentModel;
	/** @var DocumentCategorieModel */
	private $documentCategorieModel;

	public function __construct() {
		$this->documentModel = DocumentModel::instance();
		$this->documentCategorieModel = DocumentCategorieModel::instance();
	}

	/**
	 * Recente documenten uit alle categorieën tonen
	 */
	public function recenttonen() {
		return view('documenten.documenten', [
			'categorieen' => $this->documentCategorieModel->find(),
			'model' => $this->documentCategorieModel
		]);
	}

	public function verwijderen($id) {
		$document = $this->documentModel->get($id);

		if ($document === false) {
			setMelding('Document bestaat niet!', -1);
			return $this->redirectToRoute('documenten');
		} elseif ($document->magVerwijderen()) {
			$this->documentModel->delete($document);
		} else {
			setMelding('Mag document niet verwijderen', -1);
			return new JsonResponse(false);
		}

		return new PlainView(sprintf('<tr class="remove" id="document-%s"></tr>', $document->id));
	}

	public function bekijken($id) {
		$document = $this->documentModel->get($id);

		if (!$document->magBekijken()) {
			throw new CsrToegangException();
		}

		//We do not allow serving html files because they can be used for XSS.
		//We do not allow serving javascript files because they can increase the impact of XSS by registering a service worker.
		if ($document->mimetype == "text/html" || $document->mimetype == "text/javascript" || !checkMimetype($document->filename, $document->mimetype)) {
			setMelding('Dit type bestand kan niet worden getoond', -1);
			return $this->redirectToRoute('documenten');
		}

		if ($document->hasFile()) {
			return new BinaryFileResponse($document->getFullPath());
		} else {
			setMelding('Document heeft geen bestand.', -1);
			return $this->redirectToRoute('documenten');
		}
	}

	public function download($id) {
		$document = $this->documentModel->get($id);

		if (!$document->magBekijken()) {
			throw new CsrToegangException();
		}
		if ($document->hasFile()) {
			$response = new BinaryFileResponse($document->getFullPath());
			$response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $document->filename);
			return $response;
		} else {
			setMelding('Document heeft geen bestand.', -1);
			return $this->redirectToRoute('documenten');
		}
	}

	public function categorie($id) {
		$categorie = $this->documentModel->getCategorieModel()->get($id);
		if ($categorie === false) {
			setMelding('Categorie bestaat niet!', -1);
			return $this->redirectToRoute('documenten');
		} elseif (!$categorie->magBekijken()) {
			throw new CsrToegangException('Mag deze categorie niet bekijken');
		} else {
			return view('documenten.categorie', [
				'documenten' => $this->documentModel->getCategorieModel()->getRecent($categorie, 0),
				'categorie' => $categorie,
			]);
		}
	}

	public function bewerken($id) {
		$document = $this->documentModel->get($id);

		if ($document === false) {
			setMelding('Document niet gevonden', 2);
			return $this->redirectToRoute('documenten');
		}

		$form = new DocumentBewerkenForm($document);
		if ($form->isPosted() && $form->validate()) {
			$this->documentModel->update($document);

			return $this->redirectToRoute('documenten-categorie', ['id' => $document->categorie_id]);
		} else {
			return view('default', [
				'titel' => 'Document bewerken',
				'content' => $form,
			]);
		}

	}

	public function toevoegen() {
		$form = new DocumentToevoegenForm();

		if ($form->isPosted() && $form->validate()) {
			/** @var Document $document */
			$document = $form->getModel();

			$document->eigenaar = LoginModel::getUid();
			$document->toegevoegd = getDateTime();

			$bestand = $form->getUploader()->getModel();

			$document->filename = $bestand->filename;
			$document->mimetype = $bestand->mimetype;
			$document->filesize = $bestand->filesize;

			$document->id = $this->documentModel->create($document);

			if ($document->hasFile()) {
				$document->deleteFile();
			}

			$form->getUploader()->opslaan($document->getPath(), $document->getFullFileName());

			return $this->redirectToRoute('documenten-categorie', ['id' => $document->categorie_id]);
		} else {
			return view('default', [
				'titel' => 'Document toevoegen',
				'content' => $form,
			]);
		}
	}

	public function zoeken(Request $request, $zoekterm = null) {
		if (!$zoekterm && !$request->query->has('q')) {
			throw new CsrToegangException();
		}
		if (!$zoekterm) {
			$zoekterm = $request->query->get('q');
		}

		$limit = $request->query->getInt('limit', 5);

		$result = array();
		foreach ($this->documentModel->zoek($zoekterm, $limit) as $doc) {
			if ($doc->magBekijken()) {
				$result[] = array(
					'url' => '/documenten/bekijken/' . $doc->id . '/' . $doc->filename,
					'label' => $this->documentModel->getCategorieModel()->find('id = ?', [$doc->categorie_id])->fetch()->naam,
					'value' => $doc->naam,
					'icon' => Icon::getTag('document'),
					'id' => $doc->id
				);
			}
		}
		return new JsonResponse($result);
	}
}
