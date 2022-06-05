<?php

namespace CsrDelft\view\formulier\elementen;
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
	protected $comment;

	public function __construct($comment)
	{
		$this->comment = $comment;
	}

	public function getModel()
	{
		return $this->comment;
	}

	public function getBreadcrumbs()
	{
		return null;
	}

	public function getHtml()
	{
		return $this->comment;
	}

	public function __toString()
	{
		return '<div>' . $this->getHtml() . '</div>';
	}

	public function getJavascript()
	{
		return '';
	}

	public function getTitel()
	{
		return $this->getType();
	}

	public function getType()
	{
		return classNameZonderNamespace(get_class($this));
	}
}
