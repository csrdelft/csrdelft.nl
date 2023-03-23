<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\Lib\Bb\BbTag;
use CsrDelft\common\Util\FileUtil;
use CsrDelft\entity\documenten\Document;
use CsrDelft\repository\documenten\DocumentRepository;
use CsrDelft\view\bbcode\BbHelper;
use Twig\Environment;

/**
 * Geeft een blokje met een documentnaam, link, bestandsgrootte en formaat.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [document]1234[/document]
 * @example [document=1234]
 */
class BbDocument extends BbTag
{
	/**
	 * @var Document
	 */
	private $document;
	/**
	 * @var DocumentRepository
	 */
	private $documentRepository;
	/**
	 * @var Environment
	 */
	private $twig;
	/**
	 * @var string
	 */
	public $id;

	public function __construct(
		DocumentRepository $documentRepository,
		Environment $twig
	) {
		$this->documentRepository = $documentRepository;
		$this->twig = $twig;
	}

	public static function getTagName()
	{
		return 'document';
	}

	public function isAllowed(): bool
	{
		return $this->document == false || $this->document->magBekijken();
	}

	public function renderPreview(): string
	{
		return ' 📄 ';
	}

	public function renderLight(): string
	{
		if ($this->document) {
			$beschrijving =
				$this->document->getFriendlyMimetype() .
				' (' .
				FileUtil::format_filesize((int) $this->document->filesize) .
				')';
			return BbHelper::lightLinkBlock(
				'document',
				$this->document->getDownloadUrl(),
				$this->document->naam,
				$beschrijving
			);
		} else {
			return '<div class="bb-document">[document] Ongeldig document (id:' .
				$this->id .
				')</div>';
		}
	}

	public function render(): string
	{
		if ($this->document) {
			return $this->twig->render('documenten/document_bb.html.twig', [
				'document' => $this->document,
			]);
		} else {
			return '<div class="bb-document">[document] Ongeldig document (id:' .
				$this->id .
				')</div>';
		}
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = []): void
	{
		$this->id = $this->readMainArgument($arguments);
		$this->document = $this->documentRepository->get($this->id);
	}
}
