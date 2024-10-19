<?php

namespace CsrDelft\view\formulier\knoppen;

use CsrDelft\common\Util\CryptoUtil;
use CsrDelft\common\Util\ReflectionUtil;
use CsrDelft\view\formulier\FormElement;
use CsrDelft\view\Icon;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 */
class FormulierKnop implements FormElement
{
	protected $id;
	public $data;
	public $css_classes = ['FormulierKnop'];

	public function __construct(
		public $url,
		public $action,
		public $label,
		public $title,
		public $icon
	) {
		$this->id = CryptoUtil::uniqid_safe('knop_');
		$this->css_classes[] = $this->getType();
		$this->css_classes[] = 'btn btn-primary';
	}

	public function getId()
	{
		return $this->id;
	}

	public function getModel()
	{
		return null;
	}

	public function getBreadcrumbs()
	{
		return null;
	}

	public function getTitel()
	{
		return $this->getType();
	}

	public function getType()
	{
		return ReflectionUtil::classNameZonderNamespace(static::class);
	}

	public function getHtml()
	{
		$this->css_classes[] = $this->action;
		$html =
			'<a id="' .
			$this->getId() .
			'" href="' .
			($this->url ?: '#') .
			'" class="' .
			implode(' ', $this->css_classes) .
			'" title="' .
			htmlspecialchars((string) $this->title) .
			'" tabindex="0"';
		if ($this->data !== null) {
			$html .= ' data="' . $this->data . '"';
		}
		$html .= '>';
		if ($this->icon) {
			$html .= Icon::getTag($this->icon, null, null, 'me-1');
		}
		$html .= $this->label;
		return $html . '</a> ';
	}

	public function __toString(): string
	{
		return (string) $this->getHtml();
	}

	public function getJavascript()
	{
		return <<<JS

/* {$this->getId()} */
JS;
	}
}
