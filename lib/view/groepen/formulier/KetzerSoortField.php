<?php

namespace CsrDelft\view\groepen\formulier;

use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\groepen\ActiviteitSoort;
use CsrDelft\model\entity\security\AccessAction;
use CsrDelft\model\groepen\ActiviteitenModel;

class KetzerSoortField extends GroepSoortField
{

    public $columns = 2;

    public function __construct(
        $name,
        $value,
        $description,
        AbstractGroep $groep
    ) {
        parent::__construct($name, $value, $description, $groep);

        $this->options = array();
        foreach ($this->activiteit->getOptions() as $soort => $label) {
            $this->options['ActiviteitenModel_' . $soort] = $label;
        }
        $this->options['KetzersModel'] = 'Aanschafketzer';
        //$this->options['WerkgroepenModel'] = WerkgroepenModel::ORM;
        //$this->options['RechtenGroepenModel'] = 'Groep (overig)';
    }

    /**
     * Super ugly
     * @return boolean
     */
    public function validate()
    {
        $class = explode('_', $this->value, 2);
        $soort = null;
        switch ($class[0]) {

            case 'ActiviteitenModel':
                $soort = $class[1];
            // fall through

            case 'KetzersModel':
                $model = $class[0]::instance(); // require once
                $orm = $model::ORM;
                if (!$orm::magAlgemeen(AccessAction::Aanmaken, $soort)) {
                    if ($model instanceof ActiviteitenModel) {
                        $naam = ActiviteitSoort::getDescription($soort);
                    } else {
                        $naam = $model->getNaam();
                    }
                    $this->error = 'U mag geen ' . $naam . ' aanmaken';
                }
                break;

            default:
                $this->error = 'Onbekende optie gekozen';
        }
        return $this->error === '';
    }

}
