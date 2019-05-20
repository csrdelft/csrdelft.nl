<?php

namespace CsrDelft\view\renderer;

use CsrDelft\common\CsrException;
use CsrDelft\view\View;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 25/08/2018
 */
class TemplateView implements View {
	protected $template;

	public function __construct(string $template, array $variables = []) {
		$this->template = new BladeRenderer($template, $variables);
	}

	/**
	 * @throws \Exception
	 */
	public function view() {
		$this->template->display();
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	public function getHtml() {
		return $this->template->render();
	}

	/**
	 * @throws \Exception
	 */
	public function getTitel() {
		throw new CsrException("getTitel: Not supported");
	}

	/**
	 * @throws \Exception
	 */
	public function getBody() {
		throw new CsrException("getBody: Not supported");
	}

	/**
	 * @throws \Exception
	 */
	public function getModel() {
		throw new CsrException("getModel: Not supported");
	}

	/**
	 * @throws \Exception
	 */
	public function getBreadcrumbs() {
		throw new CsrException("getBreadcrumbs: Not supported");
	}
}
