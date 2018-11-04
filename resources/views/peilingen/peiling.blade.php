<Peiling
	class="vue-context"
	:id="{{ $peiling->id }}"
	:titel="'{{ $peiling->titel }}'"
	:beschrijving="{!! htmlspecialchars(json_encode(\CsrDelft\view\bbcode\CsrBB::parse($peiling->beschrijving))) !!}"
	:resultaat-zichtbaar="{{ json_encode($peiling->resultaat_zichtbaar) }}"
	:aantal-voorstellen="{{ $peiling->aantal_voorstellen }}"
	:aantal-keuzes="{{ $peiling->aantal_stemmen }}"
	:aantal-stemmen="{{ $peiling->getStemmenAantal() }}"
	:rechten-stemmen="'{{ $peiling->rechten_stemmen }}'"
	:is-mod="{{ json_encode($peiling->isMod()) }}"
	:heeft-gestemd="{{ json_encode($peiling->heeftGestemd(\CsrDelft\model\security\LoginModel::getUid()))  }}"
	:opties="{!! htmlspecialchars(json_encode($opties)) !!}">
</Peiling>
