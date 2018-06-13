<?php

namespace App\Http\Controllers;

use App\Auth\Mag;
use App\Models\CmsPagina;
use function CsrDelft\getDateTime;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\cms\CmsPaginaForm;
use CsrDelft\view\Zijbalk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CmsPaginaController extends Controller
{
    use Mag;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('mag:P_ADMIN', ['except' => ['route']]);
    }

    public function route(CmsPagina $pagina)
    {
        if (!$pagina->exists) $pagina = CmsPagina::findOrFail('thuis');

        $this->mag($pagina->rechten_bekijken);

        $html = CsrBB::parseHtml(htmlspecialchars_decode($pagina->inhoud), $pagina->inline_html);

        if (!Auth::check()) {
            return view('pagina.extern', ['titel' => $pagina->titel, 'pagina' => $html]);
        }

        return view('pagina.weergeven', [
            'naam' => $pagina->naam,
            'titel' => $pagina->titel,
            'pagina' => $html,
            'rechten_bewerken' => $pagina->rechten_bewerken,
            'zijbalk' => Zijbalk::addStandaardZijbalk([])
        ]);
    }

    public function bewerken(CmsPagina $pagina)
    {
        $form = new CmsPaginaForm($pagina);

        return view('pagina.bewerken', [
            'naam' => $pagina->naam,
            'titel' => $pagina->titel,
            'form' => $form,
        ]);
    }

    public function opslaan(Request $request, CmsPagina $pagina)
    {
        $pagina->laatst_gewijzigd = getDateTime();
        $pagina->fill($request->all());

        $form = new CmsPaginaForm($pagina);

        if ($form->validate()) {
            session()->flash('saved');

            $pagina->save();
            return redirect('/' . $pagina->naam);
        }

        return $this->bewerken($pagina);
    }

    public function verwijderen(CmsPagina $pagina)
    {
        $pagina->delete();

        return redirect(route('home'));
    }
}
