<?php

namespace CsrDelft\view\bbcode;

use CsrDelft\common\ContainerFacade;

class BbUtil
{
	public static function parse($bbcode)
	{
		$parser = ContainerFacade::getContainer()->get(CsrBB::class);
		return $parser->getHtml($bbcode);
	}

	public static function parseHtml($bbcode, $inline = false)
	{
		$parser = ContainerFacade::getContainer()->get(CsrBB::class);
		$parser->allow_html = true;
		$parser->standard_html = $inline;
		return $parser->getHtml($bbcode);
	}
}
