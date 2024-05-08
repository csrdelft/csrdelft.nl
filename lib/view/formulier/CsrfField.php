<?php

namespace CsrDelft\view\formulier;

use CsrDelft\common\Util\ReflectionUtil;
use CsrDelft\view\ToHtmlResponse;
use CsrDelft\view\View;
use Symfony\Component\Security\Csrf\CsrfToken;

class CsrfField implements View, FormElement
{
	use ToHtmlResponse;
	/**
	 * @var CsrfToken
	 */
	private $token;
	/**
	 * @var string
	 */
	private $name;

	public function __construct(CsrfToken $token, $name = 'X-CSRF-VALUE')
	{
		$this->token = $token;
		$this->name = $name;
	}

	public function __toString()
	{
		return $this->getHtml();
	}

	public function getTitel(): null
	{
		return null;
	}

	public function getBreadcrumbs(): null
	{
		return null;
	}

	public function getModel(): CsrfToken
	{
		return $this->token;
	}

	public function getType(): string
	{
		return ReflectionUtil::short_class(static::class);
	}

	public function getHtml(): string
	{
		if ($this->token === null) {
			return '';
		}

		// Note that explicit HTML instead of making use of HiddenField because HiddenField will automatically take posted values
		return '<input type="hidden" name="X-CSRF-ID" value="' .
			htmlentities($this->token->getId()) .
			'"  />' .
			'<input type="hidden" name="' .
			$this->name .
			'" value="' .
			htmlentities($this->token->getValue()) .
			'"  />';
	}

	public function getJavascript(): string
	{
		return '';
	}
}
