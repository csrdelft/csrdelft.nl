<?php
/**
 * The CssResponse file.
 */

namespace CsrDelft\view;

/**
 * Class CssResponse.
 *
 * @author Gerben Oolbekkink <gerben@bunq.com>
 * @since 20170514 Initial creation.
 */
class CssResponse implements View
{
    public function __construct($model)
    {
        $this->model = $model;
    }

    public function view()
    {
        header('Content-Type: text/css');
        echo $this->model;
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
