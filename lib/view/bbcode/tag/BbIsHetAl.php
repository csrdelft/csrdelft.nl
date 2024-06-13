<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;
use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\repository\agenda\AgendaRepository;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\repository\WoordVanDeDagRepository;
use CsrDelft\service\AgendaService;
use CsrDelft\view\IsHetAlView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class BbIsHetAl extends BbTag
{
	/**
	 * @var RequestStack
	 */
	private $requestStack;
	/**
	 * @var AgendaService
	 */
	private $agendaService;
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
	/**
	 * @var Security
	 */
	private $security;

	public function __construct(
		RequestStack $requestStack,
		Security $security,
		AgendaService $agendaService,
		LidInstellingenRepository $lidInstellingenRepository,
		WoordVanDeDagRepository $woordVanDeDagRepository
	) {
		$this->agendaService = $agendaService;
		$this->lidInstellingenRepository = $lidInstellingenRepository;
		$this->woordVanDeDagRepository = $woordVanDeDagRepository;
		$this->requestStack = $requestStack;
		$this->security = $security;
	}

	public static function getTagName()
	{
		return 'ishetal';
	}

	public function isAllowed()
	{
		return $this->security->isGranted('ROLE_LOGGED_IN');
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = [])
	{
		$this->value = $this->readMainArgument($arguments);
		if ($this->value == '') {
			$this->value = InstellingUtil::lid_instelling('zijbalk', 'ishetal');
		}
	}

	public function render()
	{
		$html = '';
		$html .= '<div class="my-3 p-3 bg-white rounded shadow-sm">';
		$html .= (new IsHetAlView(
			$this->lidInstellingenRepository,
			$this->requestStack,
			$this->agendaService,
			$this->woordVanDeDagRepository,
			$this->value
		))->__toString();
		$html .= '</div>';
		return $html;
	}
}
