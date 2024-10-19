<?php

namespace CsrDelft\view\response;

use CsrDelft\common\Util\TextUtil;
use Symfony\Component\HttpFoundation\Response;

class IcalResponse extends Response
{
	public function __construct()
	{
		parent::__construct(null, 200, ['content-type' => 'text/calendar']);
		$this->setCharset('UTF-8');
	}

	public function setContent(?string $content): static
	{
		$this->content = '';

		if ($content != null) {
			foreach (explode("\n", $content) as $line) {
				$this->content .= wordwrap(trim($line), 59, "\n ", true) . "\n";
			}

			$this->content = TextUtil::crlf_endings($this->content);
		}
		return $this;
	}
}
