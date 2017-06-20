<?php

namespace CsrDelft\controller;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\CsrLayoutOweeFrontPageView;
use CsrDelft\view\CsrLayoutOweePage;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\VoorpaginaView;

/**
 * Class VoorpaginaController.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class VoorpaginaController extends AclController
{
    public function __construct($query) {
        parent::__construct($query, null, ['GET']);
        $this->acl = [
            'thuis' => 'P_PUBLIC'
        ];
    }

    public function performAction(array $args = array())
    {
        $this->action = 'thuis';

        return parent::performAction($args);
    }

    public function thuis() {
        if (LoginModel::mag('P_LOGGED_IN')) {
            $this->view = new CsrLayoutPage(new VoorpaginaView(null));
        } else {
            $this->view = new CsrLayoutOweePage(new CsrLayoutOweeFrontPageView(), 'index');
        }
    }
}
