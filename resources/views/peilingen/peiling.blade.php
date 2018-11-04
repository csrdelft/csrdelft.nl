<Peiling
	class="vue-context"
	:id="{{ $peiling->id }}"
	:titel="'{{ $peiling->titel }}'"
	:beschrijving="'{{ $peiling->beschrijving }}'"
	:resultaatZichtbaar="{{ json_encode($peiling->resultaat_zichtbaar) }}"
	:aantalVoorstellen="{{ $peiling->aantal_voorstellen }}"
	:aantalKeuzes="{{ $peiling->aantal_stemmen }}"
	:aantalStemmen="{{ $peiling->getStemmenAantal() }}"
	:rechtenStemmen="'{{ $peiling->rechten_stemmen }}'"
	:isMod="{{ json_encode($peiling->isMod()) }}"
  :heeftGestemd="{{ json_encode($peiling->heeftGestemd(\CsrDelft\model\security\LoginModel::getUid())) }}"
  :opties='{!! json_encode($peiling->getOpties()) !!}'>
</Peiling>
