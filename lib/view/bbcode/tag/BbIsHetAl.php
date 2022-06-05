<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;
use CsrDelft\repository\agenda\AgendaRepository;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\repository\WoordVanDeDagRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\IsHetAlView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class BbIsHetAl extends BbTag
{
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
	 * @var WoordVanDeDagRepository
	 */
	private $woordVanDeDagRepository;
	/**
	 * @var string
	 */
	private $value;

	public function __construct(
		RequestStack $requestStack,
		AgendaRepository $agendaRepository,
		LidInstellingenRepository $lidInstellingenRepository,
		WoordVanDeDagRepository $woordVanDeDagRepository
	) {
		$this->agendaRepository = $agendaRepository;
		$this->lidInstellingenRepository = $lidInstellingenRepository;
		$this->woordVanDeDagRepository = $woordVanDeDagRepository;
		$this->requestStack = $requestStack;
	}

	public static function getTagName()
	{
		return 'ishetal';
	}

	public function isAllowed()
	{
		return LoginService::mag(P_LOGGED_IN);
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = [])
	{
		$this->value = $this->readMainArgument($arguments);
		if ($this->value == '') {
			$this->value = lid_instelling('zijbalk', 'ishetal');
		}
	}

	public function render()
	{
		$html = '';
		$html .= '<div class="my-3 p-3 bg-white rounded shadow-sm">';
		$html .= (new IsHetAlView(
			$this->lidInstellingenRepository,
			$this->requestStack,
			$this->agendaRepository,
			$this->woordVanDeDagRepository,
			$this->value
		))->__toString();
		$html .= '</div>';
		return $html;
	}
}
