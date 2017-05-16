<?php
/**
 * The ${NAME} file.
 */

namespace CsrDelft\view\maalcie\forms;

use CsrDelft\model\entity\maalcie\CorveeTaak;
use CsrDelft\model\groepen\LichtingenModel;
use CsrDelft\model\InstellingenModel;
use CsrDelft\model\maalcie\CorveeRepetitiesModel;
use CsrDelft\view\formulier\elementen\FormElement;
use CsrDelft\view\SmartyTemplateView;

class SuggestieLijst extends SmartyTemplateView implements FormElement
{

    private $taak;
    private $voorkeurbaar;
    private $voorkeur;
    private $recent;

    public function __construct(
        array $suggesties,
        CorveeTaak $taak
    ) {
        parent::__construct($suggesties);
        $this->taak = $taak;

        $crid = $taak->crv_repetitie_id;
        if ($crid !== null) {
            $this->voorkeurbaar = CorveeRepetitiesModel::instance()->getRepetitie($crid)->voorkeurbaar;
        }

        if ($taak->getCorveeFunctie()->kwalificatie_benodigd) {
            $this->voorkeur = InstellingenModel::get('corvee', 'suggesties_voorkeur_kwali_filter');
            $this->recent = InstellingenModel::get('corvee', 'suggesties_recent_kwali_filter');
        } else {
            $this->voorkeur = InstellingenModel::get('corvee', 'suggesties_voorkeur_filter');
            $this->recent = InstellingenModel::get('corvee', 'suggesties_recent_filter');
        }
    }

    public function getHtml()
    {
        $this->smarty->assign('suggesties', $this->model);
        $this->smarty->assign('jongsteLichting', LichtingenModel::getJongsteLidjaar());
        $this->smarty->assign('voorkeur', $this->voorkeur);
        $this->smarty->assign('recent', $this->recent);
        if (isset($this->voorkeurbaar)) {
            $this->smarty->assign('voorkeurbaar', $this->voorkeurbaar);
        }
        $this->smarty->assign('kwalificatie_benodigd', $this->taak->getCorveeFunctie()->kwalificatie_benodigd);

        return $this->smarty->fetch('maalcie/corveetaak/suggesties_lijst.tpl');
    }

    public function view()
    {
        echo $this->getHtml();
    }

    public function getTitel()
    {
        return $this->getType();
    }

    public function getType()
    {
        return get_class($this);
    }

    public function getJavascript()
    {
        $js = <<<JS

/* {$this->getTitel()} */
taken_color_suggesties();

JS;
        if (isset($this->voorkeurbaar) and $this->voorkeur) {
            $js .= "taken_toggle_suggestie('geenvoorkeur');";
        }
        if ($this->recent) {
            $js .= "taken_toggle_suggestie('recent');";
        }
        return $js;
    }

}
