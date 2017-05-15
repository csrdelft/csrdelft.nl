<?php
/**
 * The AssetsController file.
 */

namespace CsrDelft\controller;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\AssetsModel;
use CsrDelft\view\CssResponse;
use Stash\Driver\FileSystem;
use Stash\Pool;
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
    private $cachePool;

    public function __construct($query) {
        parent::__construct($query, new AssetsModel(false, true), ['GET']);
        $this->acl = [
            'js' => 'P_PUBLIC',
            'css' => 'P_PUBLIC'
        ];

        $driver = new FileSystem(['path' => DATA_PATH . 'assets/']);
        $this->cachePool = new Pool($driver);
    }

    public function performAction(array $args = array())
    {
        $this->action = $this->getParam(2);
        return parent::performAction($this->getParams(3));
    }

    public function js($layout, $module) {

        $module = str_replace('.js', '', $module);

        $item = $this->cachePool->getItem('js/' . $layout . '/' . $module);

        //$this->model->checkCache($item);

        if ($item->isHit()) {
            $js = $item->get();
        } else {
            $js = $this->model->createJavascript($item);
            //$this->cachePool->save($item->set($js));
        }

        $this->view = new JavascriptResponse($js);
    }

    public function css($layout, $module)
    {
        $module = str_replace('.css', '', $module);

        $item = $this->cachePool->getItem(sprintf('css/%s/%s', $layout, $module));

        if ($item->isHit()) {
            $css = $item->get();
        } else {
            $css = $this->model->createCss($item);
            //$this->cachePool->save($item->set($css));
        }

        $this->view = new CssResponse($css);
    }
}
