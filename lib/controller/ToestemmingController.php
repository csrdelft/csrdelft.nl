<?php

namespace CsrDelft\controller;
use CsrDelft\controller\framework\AclController;
use CsrDelft\model\CmsPaginaModel;
use CsrDelft\model\LidToestemmingModel;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\toestemming\ToestemmingModalForm;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/04/2018
 *
 * @property LidToestemmingModel $model
 */
class ToestemmingController extends AclController {
    public function __construct($query) {
        parent::__construct($query, LidToestemmingModel::instance());

        $this->acl = [
            'overzicht' => 'P_LOGGED_IN',
            'annuleren' => 'P_LOGGED_IN',
        ];
    }

    /**
     * @param array $args
     * @return mixed
     * @throws \CsrDelft\common\CsrException
     */
    public function performAction(array $args = array()) {
        if ($this->hasParam(2)) {
            $this->action = $this->getParam(2);
        } else {
            $this->action = 'overzicht';
        }
        return parent::performAction($args);
    }

    /**
     * @throws \Exception
     */
    public function POST_overzicht()
    {
        $form = new ToestemmingModalForm();

        if ($form->validate()) {

            $this->model->save();
            setMelding('Toestemming opgeslagen', 1);
            $this->view = new CmsPaginaView(CmsPaginaModel::get('thuis'));
        } else {
            $this->view =  $form;
        }
    }

    /**
     * @throws \SmartyException
     */
    public function GET_overzicht()
    {
        $this->view = new CsrLayoutPage(new CmsPaginaView(CmsPaginaModel::get('thuis')), [], new ToestemmingModalForm());
    }

    public function POST_annuleren()
    {
        $_SESSION['stop_nag'] = time();

        $this->view = new CmsPaginaView(CmsPaginaModel::get('thuis'));
    }
}