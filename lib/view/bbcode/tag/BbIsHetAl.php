<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;
use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\repository\agenda\AgendaRepository;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\repository\WoordVanDeDagRepository;
use CsrDelft\view\IsHetAlView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class BbIsHetAl extends BbTag
{
	/**
	 * @var string
	 */
	private $value;

	/**
	 * @return string
	 *
	 * @psalm-return 'ishetal'
	 */
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
	 *
	 * @return void
	 */
	public function parse($arguments = [])
	{
		$this->value = $this->readMainArgument($arguments);
		if ($this->value == '') {
			$this->value = InstellingUtil::lid_instelling('zijbalk', 'ishetal');
		}
	}

	/**
	 * @return string
	 */
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
