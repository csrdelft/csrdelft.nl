<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;
use CsrDelft\repository\agenda\AgendaRepository;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\IsHetAlView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class BbIsHetAl extends BbTag {
	/**
	 * @var RequestStack
	 */
	private $requestStack;
	/**
	 * @var AgendaRepository
	 */
	private $agendaRepository;
	/**
	 * @var LidInstellingenRepository
	 */
	private $lidInstellingenRepository;
	/**
	 * @var SessionInterface
	 */
	private $session;

	public function __construct(SessionInterface $session, AgendaRepository $agendaRepository, LidInstellingenRepository $lidInstellingenRepository) {
		$this->agendaRepository = $agendaRepository;
		$this->lidInstellingenRepository = $lidInstellingenRepository;
		$this->session = $session;
	}

	public static function getTagName() {
		return 'ishetal';
	}

	public function isAllowed() {
		return LoginService::mag(P_LOGGED_IN);
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = []) {
		$this->readMainArgument($arguments);
		if ($this->content == '') {
			$this->content = lid_instelling('zijbalk', 'ishetal');
		}
	}

	public function render() {
		ob_start();
		echo '<div class="my-3 p-3 bg-white rounded shadow-sm">';
		(new IsHetAlView($this->lidInstellingenRepository, $this->session, $this->agendaRepository, $this->content))->view();
		echo '</div>';
		return ob_get_clean();
	}
}
