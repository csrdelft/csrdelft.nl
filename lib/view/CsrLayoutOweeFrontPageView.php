<?php

namespace CsrDelft\view;

/**
 * Class CsrLayoutOweeFrontPage.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class CsrLayoutOweeFrontPageView implements View
{

    public function view()
    {
        // nil.
    }

    public function getTitel()
    {
        return 'Vereniging van ChristenStudenten';
    }

    public function getBreadcrumbs()
    {
        // nil.
    }

    /**
     * Hiermee wordt gepoogt af te dwingen dat een view een model heeft om te tonen
     */
    public function getModel()
    {
        // nil.
    }
}
