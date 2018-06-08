<?php

namespace App\Http\Controllers;

use App\Models\CmsPagina;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\Zijbalk;
use Illuminate\Support\Facades\Auth;

class ThuisController extends Controller
{
    //
	public function index()
    {
		if (Auth::check()) {
		    $pagina = (new CmsPagina)->findOrFail('thuis');

            $html = CsrBB::parseHtml(htmlspecialchars_decode($pagina->inhoud), $pagina->inline_html);

            return view('pagina.weergeven', [
                'naam' => $pagina->naam,
                'titel' => $pagina->titel,
                'pagina' => $html,
                'rechten_bewerken' => $pagina->rechten_bewerken,
                'zijbalk' => Zijbalk::addStandaardZijbalk([])
            ]);
		}

		return view('extern.index');
	}
}
