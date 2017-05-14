<?php
/**
 * The JavascriptAssetsView file.
 */

namespace CsrDelft\view;

/**
 * Class JavascriptAssetsView.
 *
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 20170514 Initial creation.
 */
class JavascriptResponse implements View
{
    private $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function view()
    {
        header('Content-Type: application/javascript');
        echo $this->getModel();
    }

    public function getTitel()
    {
        // TODO: Implement getTitel() method.
    }

    public function getBreadcrumbs()
    {
        // TODO: Implement getBreadcrumbs() method.
    }

    /**
     * Hiermee wordt gepoogt af te dwingen dat een view een model heeft om te tonen
     */
    public function getModel()
    {
        return $this->model;
    }
}
