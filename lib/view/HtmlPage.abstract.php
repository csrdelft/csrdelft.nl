<?php

namespace CsrDelft\view;
use CsrDelft\model\LidInstellingenModel;

/**
 * HtmlPage.abstract.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een HTML pagina met stylesheets en scripts.
 *
 */
abstract class HtmlPage implements View {

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
	private $stylesheets = array();

	public function __construct(View $body, $titel) {
		$this->body = $body;
		$this->titel = $titel;

		$this->stylesheets = $this->loadDefaultStylesheets();
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
     * Add compressed css en js to page for module.
     *
     * @param string $module
     */
    public function addCompressedResources($module) {
        $this->stylesheets[] = 'module/' . $module;
    }

	public function getStylesheets() {
		return array_map(function ($module) { return "/css/{$module}.css"; }, array_unique($this->stylesheets));
	}

	private function loadDefaultStylesheets() {
        $modules = array();
        $modules[] = 'module/formulier';
        $modules[] = 'module/datatable';

        //voeg modules toe afhankelijk van instelling
        $modules[] = 'opmaak/' . LidInstellingenModel::get('layout', 'opmaak');

        if (LidInstellingenModel::get('layout', 'toegankelijk') == 'bredere letters') {
            $modules[] = 'bredeletters';
        }
        if (LidInstellingenModel::get('layout', 'fx') == 'sneeuw') {
            $modules[] = 'effect/snow';
        } elseif (LidInstellingenModel::get('layout', 'fx') == 'space') {
            $modules[] = 'effect/space';
        }

        if (LidInstellingenModel::get('layout', 'fx') == 'wolken') {
            $modules[] = 'effect/clouds';
        }

        if (LidInstellingenModel::get('layout', 'minion') == 'ja') {
            $modules[] = 'effect/minion';
        }
        if (LidInstellingenModel::get('layout', 'fx') == 'onontdekt') {
            $modules[] = 'effect/onontdekt';
        } elseif (LidInstellingenModel::get('layout', 'fx') == 'civisaldo') {
            $modules[] = 'effect/civisaldo';
        }

        return $modules;
    }

	public function __toString() {
        ob_start();
        $this->view();
        return ob_get_clean();
	}

}
