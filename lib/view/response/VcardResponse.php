<?php

namespace CsrDelft\view\response;

use Symfony\Component\HttpFoundation\Response;

class VcardResponse extends Response
{
	public function __construct(string $content, $status = 200)
	{
		parent::__construct(crlf_endings($content), $status, [
			'content-type' => 'text/x-vcard',
		]);
		$this->setCharset('UTF-8');
	}
}
