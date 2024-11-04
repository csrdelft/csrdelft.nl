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
	 * @param string $name
	 */
	public function __construct(
		private CsrfToken $token,
		private $name = 'X-CSRF-VALUE'
	) {
	}

	public function __toString(): string
	{
		return (string) $this->getHtml();
	}

	public function getTitel()
	{
		return null;
	}

	public function getBreadcrumbs()
	{
		return null;
	}

	/**
	 * @return CsrfToken
	 */
	public function getModel()
	{
		return $this->token;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return ReflectionUtil::short_class(static::class);
	}

	/**
	 * @return string
	 */
	public function getHtml()
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

	/**
	 * @return string
	 *
	 * @psalm-return ''
	 */
	public function getJavascript()
	{
		return '';
	}
}
