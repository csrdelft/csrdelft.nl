<?php

namespace CsrDelft\view\renderer;

use CsrDelft\model\DragObjectModel;
use CsrDelft\model\LidInstellingenModel;
use CsrDelft\view\menu\MainMenuView;
use CsrDelft\view\View;
use CsrDelft\view\Zijbalk;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 25/08/2018
 */
class TemplateView implements View {

	/**
	 * <BODY>
	 * @var View
	 */
	protected $body;
	/**
	 * <TITLE>
	 * @var string
	 */
	protected $titel;
	/**
	 * <CSS>
	 * @var array
	 */
	private $stylesheets = [];
	/**
	 * <SCRIPT>
	 * @var array
	 */
	private $scripts = [];
	protected $template;

	public function __construct(string $template, array $variables = [], array $zijbalk = []) {
		$this->body = $template;

		$this->template = new BladeRenderer($template, $variables);

		$this->template->assign('mainmenu', new MainMenuView());
		if ($zijbalk !== false) {
			if (!is_array($zijbalk)) {
				$zijbalk = array();
			}
			$zijbalk = Zijbalk::addStandaardZijbalk($zijbalk);
			if (LidInstellingenModel::get('zijbalk', 'scrollen') != 'met pagina mee') {
				$this->template->assign('scrollfix', DragObjectModel::getCoords('zijbalk', 0, 0)['top']);
			}
		}

		$this->template->assign('zijbalk', $zijbalk);

		if (LidInstellingenModel::get('layout', 'minion') == 'ja') {
			$this->template->assign('minioncoords', DragObjectModel::getCoords('minion', 40, 40));
		}
	}

	public function getTitel() {
		return $this->titel;
	}

	public function getBody() {
		return $this->body;
	}

	public function getModel() {
		return null;
	}

	/**
	 * Zorg dat de HTML pagina een stylesheet inlaadt. Er zijn twee verianten:
	 *
	 * - lokaal:
	 * een timestamp van de creatie van het bestand wordt toegoevoegd,
	 * zodat de browsercache het bestand vernieuwt.
	 *
	 * - extern:
	 * Buiten de huidige server, gewoon een url dus.
	 */
	public function addStylesheet($sheet, $remote = false) {
		if (!$remote) {
			$sheet .= '?' . filemtime(HTDOCS_PATH . $sheet);
		}
		$this->stylesheets[md5($sheet)] = $sheet;
	}

	public function addResource($module) {
		$sheet = sprintf('/dist/css/module/%s.css', $module);
		$this->addStylesheet($sheet, false);
	}

	public function getStylesheets() {
		return $this->stylesheets;
	}

	/**
	 * Zorg dat de HTML pagina een script inlaadt. Er zijn twee verianten:
	 *
	 * - lokaal:
	 * een timestamp van de creatie van het bestand wordt toegoevoegd,
	 * zodat de browsercache het bestand vernieuwt.
	 *
	 * - extern:
	 * Buiten de huidige server, gewoon een url dus.
	 */
	public function addScript($script, $remote = false) {
		if (!$remote) {
			$script .= '?' . filemtime(HTDOCS_PATH . $script);
		}
		$this->scripts[md5($script)] = $script;
	}

	public function getScripts() {
		return $this->scripts;
	}

	/**
	 * @throws \Exception
	 */
	public function view() {
		$this->template->display();
	}

	public function getBreadcrumbs() {
		return null;
	}
}
