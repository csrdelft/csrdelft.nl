<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\Lib\Bb\BbTag;
use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\view\Icon;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [neuzen]2o13[/neuzen]
 */
class BbNeuzen extends BbTag
{
	public static function getTagName()
	{
		return 'neuzen';
	}

	public function render(): string
	{
		$content = $this->getContent();
		if (InstellingUtil::lid_instelling('layout', 'neuzen') != 'nee') {
			$neus = Icon::getTag('circle', null, 'Neus 2013', 'neus2013');
			$content = str_replace('o', $neus, $content);
		}

		return '<span data-neuzen>' . $content . '</span>';
	}

	public function parse($arguments = []): void
	{
		$this->readContent([], false);
	}
}
