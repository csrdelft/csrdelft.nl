<?php


namespace CsrDelft\view\response;


use Symfony\Component\HttpFoundation\Response;

class IcalResponse extends Response
{
	public function __construct(string $content, $status = 200)
	{
		parent::__construct(crlf_endings($content), $status, ['content-type' => 'text/calendar']);
		$this->setCharset('UTF-8');
	}
}
