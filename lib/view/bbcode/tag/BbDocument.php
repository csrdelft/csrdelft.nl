<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;
use CsrDelft\model\documenten\DocumentModel;
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

	public function getTagName() {
		return 'document';
	}

	public function parseLight($arguments = []) {
		if (isset($arguments['document'])) {
			$id = $arguments['document'];
		} else {
			$id = $this->getContent();
		}

		$document = DocumentModel::instance()->get($id);

		if ($document) {
			$beschrijving = $document->getFriendlyMimetype() . ' (' . format_filesize((int)$document->filesize) . ')';
			return BbHelper::lightLinkBlock('document', $document->getDownloadUrl(), $document->naam, $beschrijving);
		} else {
			return '<div class="bb-document">[document] Ongeldig document (id:' . $id . ')</div>';
		}
	}

	public function parse($arguments = []) {
		if (isset($arguments['document'])) {
			$id = $arguments['document'];
		} else {
			$id = $this->getContent();
		}

		$document = DocumentModel::instance()->get($id);

		if ($document) {
			return view('documenten.document_bb', ['document' => $document])->getHtml();
		} else {
			return '<div class="bb-document">[document] Ongeldig document (id:' . $id . ')</div>';
		}
	}
}
