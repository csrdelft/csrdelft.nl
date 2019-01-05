<Peiling
	class="vue-context"
	:id="{{ $peiling->id }}"
	:titel="{{ json_encode($peiling->titel) }}"
	:beschrijving="{!! htmlspecialchars(json_encode(\CsrDelft\view\bbcode\CsrBB::parse($peiling->beschrijving))) !!}"
	:resultaat-zichtbaar="{{ json_encode($peiling->resultaat_zichtbaar) }}"
	:aantal-voorstellen="{{ $peiling->aantal_voorstellen }}"
	:aantal-keuzes="{{ $peiling->aantal_stemmen }}"
	:aantal-stemmen="{{ $peiling->getStemmenAantal() }}"
	:rechten-stemmen="'{{ $peiling->rechten_stemmen }}'"
	:mag-stemmen="{{json_encode($peiling->magStemmen())}}"
	:is-mod="{{ json_encode($peiling->isMod()) }}"
	:heeft-gestemd="{{ json_encode($peiling->heeftGestemd(\CsrDelft\model\security\LoginModel::getUid()))  }}"
	:opties="{!! htmlspecialchars(json_encode($opties)) !!}">
</Peiling>
