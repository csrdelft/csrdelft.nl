<?php

namespace App\Http\Controllers;

use App\Eetplan\Contracts\EetplanContract;
use App\Eetplan\Data\EetplanBekendeHuizenResponse;
use App\Eetplan\Data\EetplanBekendenResponse;
use App\Eetplan\Data\EetplanHuisResponse;
use App\Eetplan\Data\EetplanHuizenTypeaheadResponse;
use App\Eetplan\Models\Eetplan;
use App\Eetplan\Models\EetplanBekenden;
use App\Eetplan\View\Formulieren\EetplanBekendeHuizenForm;
use App\Eetplan\View\Formulieren\EetplanBekendenForm;
use App\Eetplan\View\Formulieren\NieuwEetplanForm;
use App\Eetplan\View\Formulieren\VerwijderEetplanForm;
use App\Eetplan\View\Tables\EetplanBekendeHuizenTable;
use App\Eetplan\View\Tables\EetplanBekendenTable;
use App\Eetplan\View\Tables\EetplanHuizenTable;
use App\Models\Profiel;
use CsrDelft\model\entity\groepen\GroepStatus;
use CsrDelft\model\entity\groepen\Woonoord;
use CsrDelft\model\groepen\LichtingenModel;
use CsrDelft\model\groepen\WoonoordenModel;
use CsrDelft\view\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EetplanController extends Controller
{
    /**
     * @var string
     */
    private $lichting;

    /**
     * @var EetplanContract
     */
    private $eetplanService;

    public function __construct(EetplanContract $eetplanService)
    {
        parent::__construct();

        $this->lichting = substr((string)LichtingenModel::getJongsteLidjaar(), 2, 2);
        $this->eetplanService = $eetplanService;
    }

    public function overzicht()
    {
        return view('eetplan.overzicht', [
            'novieten' => Eetplan::getEetplan($this->lichting),
            'avonden' => Eetplan::getAvonden($this->lichting)
        ]);
    }

    public function noviet(Profiel $profiel)
    {
        return view('eetplan.noviet', [
            'eetplan' => Eetplan::getEetplanVoorNoviet($profiel),
        ]);
    }

    public function huis(int $id)
    {
        return view('eetplan.huis', [
            'eetplan' => Eetplan::getEetplanVoorHuis($id, $this->lichting)
        ]);
    }

    public function beheer()
    {
        return view('eetplan.beheer', [
            'bekendentable' => new EetplanBekendenTable(),
            'huizentable' => new EetplanHuizenTable(),
            'bekendehuizentable' => new EetplanBekendeHuizenTable(),
            'novieten' => Eetplan::getEetplan($this->lichting),
            'avonden' => Eetplan::getAvonden($this->lichting)
        ]);
    }

    //region Toevoegen & verwijderen
    public function action_nieuw()
    {
        $form = new NieuwEetplanForm();

        if ($form->validate()) {
            $alleEetplan = $this->eetplanService->genereer(
                $form->getValues()['avond'],
                Profiel::getNovieten($this->lichting)->all(),
                WoonoordenModel::instance()->find("eetplan = true")->fetchAll(),
                EetplanBekenden::getBekenden($this->lichting)->all(),
                Eetplan::getBezocht($this->lichting)->all(),
                true
            );

            foreach ($alleEetplan as $eetplan) {
                $eetplan->save();
            }

            return new JsonResponse("");
        } else {
            return $form;
        }
    }

    public function action_verwijderen()
    {
        $form = new VerwijderEetplanForm(Eetplan::getAvonden($this->lichting));

        if ($form->validate()) {
            $avond = $form->getValues()['avond'];
            DB::transaction(function () use ($avond) {
                $alleEetplan = Eetplan::getEetplanVoorAvond($avond);

                foreach ($alleEetplan as $eetplan) {
                    $eetplan->delete();
                }
            });

            return view('eetplan.table', [
                'novieten' => Eetplan::getEetplan($this->lichting),
                'avonden' => Eetplan::getAvonden($this->lichting)
            ]);
        } else {
            return $form;
        }
    }
    //endregion

    //region Huis beheer acties
    public function dt_huis()
    {
        $woonoorden = WoonoordenModel::instance()->find('status = ?', array(GroepStatus::HT))->fetchAll();

        return new EetplanHuisResponse($woonoorden);
    }

    public function action_toggle_huis(Request $request, string $status)
    {
        $selection = $request->get('DataTableSelection');//$refilter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
        $woonoorden = array();
        foreach ($selection as $woonoord) {
            /** @var Woonoord $woonoord */
            $woonoord = WoonoordenModel::instance()->retrieveByUUID($woonoord);
            $woonoord->eetplan = $status == 'aan';
            WoonoordenModel::instance()->update($woonoord);
            $woonoorden[] = $woonoord;
        }

        return new EetplanHuisResponse($woonoorden);
    }
    //endregion

    //region Bekenden beheer acties
    public function dt_bekenden()
    {
        return new EetplanBekendenResponse(EetplanBekenden::getBekenden($this->lichting));
    }

    public function form_bekenden_toevoegen()
    {
        return new EetplanBekendenForm(new EetplanBekenden());
    }

    public function action_bekenden_toevoegen(EetplanBekenden $eetplanBekenden)
    {
        $form = new EetplanBekendenForm($eetplanBekenden);
        if (!$form->validate()) {
            return $form;
        }

        $eetplanBekenden->fill($form->getValues());

        if ($eetplanBekenden->id > 0 && $eetplanBekenden->exists()) {
            session()->flash('Bekenden bestaan al');
            return $form;
        } else {
            $eetplanBekenden->save();

            return $this->dt_bekenden();
        }
    }
    //endregion

    //region Bekend huis beheer acties
    public function dt_bekendehuizen()
    {
        return new EetplanBekendeHuizenResponse(Eetplan::getBekendeHuizen($this->lichting));
    }

    public function form_bekendehuizen_toevoegen()
    {
        return new EetplanBekendeHuizenForm(new Eetplan());
    }

    public function action_bekendehuizen_toevoegen(Eetplan $bekendHuis)
    {
        $form = new EetplanBekendeHuizenForm($bekendHuis);
        if (!$form->validate()) {
            return $form;
        }

        $bekendHuis->fill($form->getValues());


        if ($bekendHuis->id > 0 && $bekendHuis->exists()) {
            session()->flash('Bekenden bestaan al');
            return $form;
        } else {
            $bekendHuis->save();

            return $this->dt_bekendehuizen();
        }
    }

    public function action_bekendehuizen_zoeken(Request $request)
    {
        $huisnaam = $request->get('q');
        $huisnaam = '%' . $huisnaam . '%';
        $woonoorden = WoonoordenModel::instance()->find('status = ? AND naam LIKE ?', array(GroepStatus::HT, $huisnaam))->fetchAll();
        return new EetplanHuizenTypeaheadResponse($woonoorden);
    }
    //endregion
}
