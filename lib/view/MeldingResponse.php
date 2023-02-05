<?php

namespace CsrDelft\view;

use CsrDelft\common\Util\MeldingUtil;
use Symfony\Component\HttpFoundation\Response;

class MeldingResponse implements ToResponse, View
{
	public function getTitel()
	{
		return '';
	}

	public function getBreadcrumbs()
	{
		return '';
	}

	/**
	 * Hiermee wordt gepoogt af te dwingen dat een view een model heeft om te tonen
	 */
	public function getModel()
	{
		return null;
	}

	public function toResponse(): Response
	{
		return new Response(MeldingUtil::getMelding());
	}

	public function __toString()
	{
		return MeldingUtil::getMelding();
	}
}
