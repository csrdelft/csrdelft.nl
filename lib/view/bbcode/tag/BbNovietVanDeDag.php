<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\security\LoginService;
use Twig\Environment;

class BbNovietVanDeDag extends BbTag {
	private ProfielRepository $profielRepository;
	private Environment $twig;

	public function __construct(ProfielRepository $profielRepository, Environment $twig) {
		$this->profielRepository = $profielRepository;
		$this->twig = $twig;
	}

	public static function getTagName() {
		return 'novietvandedag';
	}

	public function isAllowed() {
		return LoginService::mag(P_LOGGED_IN);
	}

	public function parse($arguments = []) {

	}

	public function render() {
		/** @noinspection PhpUnhandledExceptionInspection */
		return $this->twig->render('profiel/noviet_van_de_dag.html.twig', [
			'naam' => 'Test'
		]);
	}

	public function renderLight() {

	}
}
