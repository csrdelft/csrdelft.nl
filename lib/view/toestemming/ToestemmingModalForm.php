<?php

namespace CsrDelft\view\toestemming;

use CsrDelft\model\entity\LidToestemming;
use CsrDelft\model\LidToestemmingModel;
use CsrDelft\view\CsrSmarty;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/04/2018
 */
class ToestemmingModalForm extends ModalForm {
    /**
     * @throws \SmartyException
     */
    public function __construct() {
        parent::__construct(new LidToestemming(), '/toestemming', 'Toestemming geven');

        $smarty = CsrSmarty::instance();
        $model = LidToestemmingModel::instance();
        $fields = [];

        $akkoord = null;

        foreach (LidToestemmingModel::instance()->getInstellingen() as $module => $instellingen) {
            foreach ($instellingen as $id) {
                if ($model->getValue($module, $id) == 'ja' && $akkoord == null) {
                    $akkoord = 'ja';
                } elseif ($model->getValue($module, $id) == 'nee') {
                    $akkoord = 'nee';
                }

                $smarty->assign('module', $module);
                $smarty->assign('id', $id);
                $smarty->assign('type', $model->getType($module, $id));
                $smarty->assign('opties', $model->getTypeOptions($module, $id));
                $smarty->assign('label', $model->getDescription($module, $id));
                $smarty->assign('waarde', $model->getValue($module, $id));
                $smarty->assign('default', $model->getDefault($module, $id));
                $fields[] = $smarty->fetch('toestemming/toestemming.tpl');
            }
        }

        $smarty->assign('akkoord', $akkoord);
        $smarty->assign('fields', $fields);
        $this->addFields([new HtmlComment($smarty->fetch('toestemming/toestemming_head.tpl'))]);

        $this->addFields([new FormDefaultKnoppen(null, false)]);
    }

    public function validate() {
        if (!parent::validate()) {
            return false;
        }

        $toestemmingJa = filter_input(INPUT_POST, 'toestemming-ja', FILTER_VALIDATE_BOOLEAN);
        $toestemmingNee = filter_input(INPUT_POST, 'toestemming-nee', FILTER_VALIDATE_BOOLEAN);

        if ($toestemmingJa || $toestemmingNee) {
            return true;
        }

        setMelding('Maak een keuze', -1);
        return false;
    }
}