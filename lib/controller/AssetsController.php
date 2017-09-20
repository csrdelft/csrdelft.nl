<?php

namespace CsrDelft\controller;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\AssetsModel;
use CsrDelft\view\CssResponse;
use CsrDelft\view\JavascriptResponse;

/**
 * Class AssetsController.
 *
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 20170514 Initial creation.
 *
 * @property AssetsModel $model
 */
class AssetsController extends AclController {
	public function __construct($query) {
		parent::__construct($query, new AssetsModel(MINIFY), ['GET']);
		$this->acl = [
			'scripts' => 'P_PUBLIC',
			'styles' => 'P_PUBLIC'
		];
	}

	public function performAction(array $args = array()) {
		$this->action = $this->getParam(1);
		// GetParam(2) is timehash voor cache.

		if ($this->hasParam(3) && $this->hasParam(4) && $this->hasParam(5)) {
			return parent::performAction($this->getParams(3));
		} else {
			$this->exit_http(404);
		}
	}

	public function scripts($hash, $layout, $module) {
		$module = str_replace('.js', '', $module);
		$item = $this->model->getItem($hash, $layout, $module, 'js');

		if (DEBUG) {
			$item->clear();
		}

		if ($item->isHit()) {
			$js = $item->get();
		} else {
			$js = $this->model->createJavascript($item);
			$this->model->save($item->set($js));
		}


		$this->view = new JavascriptResponse($js);
	}

	public function styles($hash, $layout, $module) {
		try {
			$module = str_replace('.css', '', $module);
			$item = $this->model->getItem($hash, $layout, $module, 'css');

			if (DEBUG) {
				$item->clear();
			}

			if ($item->isHit()) {
				$css = $item->get();
			} else {
				$css = $this->model->createCss($item);
				$this->model->save($item->set($css));
			}

			$this->view = new CssResponse($css);
		} catch (\Exception $exception) {
			$message = $exception->getMessage();
			$message = preg_replace("/\n/", "\\A ", $message);
			$this->view = new CssResponse(<<<CSS
body::before {
	content: '$message';
	white-space: pre;
	display: block;
	position: absolute;
	background: #ffa9a7;
	border: 1px solid black;
	margin: 30px;
	z-index: 9999;
}
CSS
			);
		}
	}
}
