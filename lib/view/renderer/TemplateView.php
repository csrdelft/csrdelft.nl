<?php

namespace CsrDelft\view\renderer;

use CsrDelft\common\CsrException;
use CsrDelft\view\ToHtmlResponse;
use CsrDelft\view\ToResponse;
use CsrDelft\view\View;
use Exception;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 25/08/2018
 */
class TemplateView implements View, ToResponse {
	use ToHtmlResponse;
	protected $template;

	public function __construct(string $template, array $variables = []) {
		$this->template = new BladeRenderer($template, $variables);
	}

	/**
	 * @return BladeRenderer
	 */
	public function getRenderer() {
		return $this->template;
	}

	/**
	 * @throws Exception
	 */
	public function view() {
		$this->template->display();
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public function getHtml() {
		return $this->template->render();
	}

	public function __toString() {
		return $this->getHtml();
	}

	/**
	 * @throws Exception
	 */
	public function getTitel() {
		throw new CsrException("getTitel: Not supported");
	}

	/**
	 * @throws Exception
	 */
	public function getBody() {
		throw new CsrException("getBody: Not supported");
	}

	/**
	 * @throws Exception
	 */
	public function getModel() {
		throw new CsrException("getModel: Not supported");
	}

	/**
	 * @throws Exception
	 */
	public function getBreadcrumbs() {
		throw new CsrException("getBreadcrumbs: Not supported");
	}
}
