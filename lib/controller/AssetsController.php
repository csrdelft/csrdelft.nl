<?php
/**
 * The AssetsController file.
 */

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
class AssetsController extends AclController
{
    public function __construct($query) {
        parent::__construct($query, new AssetsModel(MINIFY), ['GET']);
        $this->acl = [
            'scripts' => 'P_PUBLIC',
            'styles' => 'P_PUBLIC'
        ];
    }

    public function performAction(array $args = array()) {
        $this->action = $this->getParam(1);
        // GetParam(2) is hash voor cache.
        return parent::performAction($this->getParams(3));
    }

    public function scripts($layout, $module) {
        $module = str_replace('.js', '', $module);
        $item = $this->model->getItem($layout, $module, 'js');

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

    public function styles($layout, $module) {
        $module = str_replace('.css', '', $module);
        $item = $this->model->getItem($layout, $module, 'css');

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
    }
}
