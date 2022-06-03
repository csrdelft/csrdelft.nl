<?php

namespace CsrDelft\view\bbcode\tag\groep;

use CsrDelft\bb\BbTag;
use CsrDelft\common\CsrException;
use CsrDelft\repository\groepen\VerticalenRepository;
use CsrDelft\service\security\LoginService;

/**
 * Geeft een link naar de verticale.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [verticale]A[/verticale]
 * @example [verticale=A]
 */
class BbVerticale extends BbTag {
	/**
	 * @var VerticalenRepository
	 */
	private $verticalenRepository;
	/**
	 * @var string
	 */
	private $letter;

	public function getLetter() {
		return $this->letter;
	}

	public function __construct(VerticalenRepository $verticalenRepository) {
		$this->verticalenRepository = $verticalenRepository;
	}

	public static function getTagName() {
		return 'verticale';
	}

	public function isAllowed() {
		return LoginService::mag(P_LOGGED_IN);
	}

	public function render() {
		try {
			$verticale = $this->verticalenRepository->get($this->letter);
			return '<a href="/verticalen#' . $verticale->letter . '">' . $verticale->naam . '</a>';
		} catch (CsrException $e) {
			return 'Verticale met letter=' . htmlspecialchars($this->letter) . ' bestaat niet. <a href="/verticalen">Zoeken</a>';
		}
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = []) {
		$this->letter = $this->readMainArgument($arguments);
	}
}
