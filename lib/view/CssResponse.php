<?php

namespace CsrDelft\view;

/**
 * Class CssResponse.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class CssResponse implements View {
	private $model;
	private $cacheTime;

	/**
	 * CssResponse constructor.
	 *
	 * @param string $model
	 * @param int $cacheTime Tijd om te cachen in de browser in seconden.
	 */
	public function __construct($model, $cacheTime = 31536000) {
		$this->model = $model;
		$this->cacheTime = $cacheTime;
	}

	public function view() {
		header('Content-Type: text/css');
		header('Cache-Control: public, max-age=' . $this->cacheTime);

		echo $this->getModel();
	}

	public function getTitel() {
		// nil.
	}

	public function getBreadcrumbs() {
		// nil.
	}

	/**
	 * @return string
	 */
	public function getModel() {
		return $this->model;
	}
}
