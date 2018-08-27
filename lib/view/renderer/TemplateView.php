<?php

namespace CsrDelft\view\renderer;

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
	 * @throws \Exception
	 */
	public function getTitel() {
		throw new \Exception("Not supported");
	}

	/**
	 * @throws \Exception
	 */
	public function getBody() {
		throw new \Exception("Not supported");
	}

	/**
	 * @throws \Exception
	 */
	public function getModel() {
		throw new \Exception("Not supported");
	}

	/**
	 * @throws \Exception
	 */
	public function getBreadcrumbs() {
		throw new \Exception("Not supported");
	}
}
