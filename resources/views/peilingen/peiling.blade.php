<Peiling
	class="vue-context"
	:id="{{ $peiling->id }}"
	:titel="'{{ $peiling->titel }}'"
	:beschrijving="'{{ $peiling->beschrijving }}'"
	:resultaat_zichtbaar="{{ json_encode($peiling->resultaat_zichtbaar) }}"
	:aantal_voorstellen="{{ $peiling->aantal_voorstellen }}"
	:aantal_keuzes="{{ $peiling->aantal_stemmen }}"
	:aantal_stemmen="{{ $peiling->getStemmenAantal() }}"
	:rechten_stemmen="'{{ $peiling->rechten_stemmen }}'"
	:is_mod="{{ json_encode($peiling->isMod()) }}"
  :heeft_gestemd="{{ json_encode($peiling->heeftGestemd(\CsrDelft\model\security\LoginModel::getUid())) }}"
  :opties='{!! json_encode($peiling->getOpties()) !!}'>
</Peiling>
