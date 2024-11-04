<?php

namespace CsrDelft\view\formulier\elementen;
use CsrDelft\common\Util\ReflectionUtil;
use CsrDelft\view\formulier\FormElement;

/**
 * HtmlComment.class.php
 *
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 *
 * Commentaardingen voor formulieren
 */
class HtmlComment implements FormElement
{
	public function __construct(protected $comment)
	{
	}

	public function getModel()
	{
		return $this->comment;
	}

	public function getHtml()
	{
		return $this->comment;
	}

	public function __toString(): string
	{
		return '<div>' . $this->getHtml() . '</div>';
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

	/**
	 * @return string
	 */
	public function getTitel()
	{
		return $this->getType();
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return ReflectionUtil::classNameZonderNamespace(static::class);
	}
}
