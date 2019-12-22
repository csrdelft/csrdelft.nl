<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;
use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\documenten\Document;
use CsrDelft\repository\documenten\DocumentRepository;
use CsrDelft\view\bbcode\BbHelper;

/**
 * Geeft een blokje met een documentnaam, link, bestandsgrootte en formaat.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [document]1234[/document]
 * @example [document=1234]
 */
class BbDocument extends BbTag {
	/**
	 * @var Document
	 */
	private $document;
	public static function getTagName() {
		return 'document';
	}

	public function isAllowed()
	{
		return $this->document == false || $this->document->magBekijken();
	}

	public function renderLight() {
		if ($this->document) {
			$beschrijving = $this->document->getFriendlyMimetype() . ' (' . format_filesize((int)$this->document->filesize) . ')';
			return BbHelper::lightLinkBlock('document', $this->document->getDownloadUrl(), $this->document->naam, $beschrijving);
		} else {
			return '<div class="bb-document">[document] Ongeldig document (id:' . $this->content . ')</div>';
		}
	}

	public function render() {
		if ($this->document) {
			return view('documenten.document_bb', ['document' => $this->document])->getHtml();
		} else {
			return '<div class="bb-document">[document] Ongeldig document (id:' . $this->content . ')</div>';
		}
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = [])
	{
		$this->readMainArgument($arguments);
		$documentRepository = ContainerFacade::getContainer()->get(DocumentRepository::class);
		$this->document = $documentRepository->get($this->content);
	}
}
