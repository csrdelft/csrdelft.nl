@extends ('layouts.intern')

@section('title', 'Eetplan')
@section('breadcrumbs', Breadcrumbs::render('eetplan.huis', $eetplan[0]->woonoord()->naam))

@section('head')
    @include('partial.stylesheet', ['sheets' => [asset('css/module/eetplan.css')]])
@endsection

@section('content')
    <table class="eetplantabel">
        <tr>
            <th style="width: 150px">Avond</th>
            <th style="width: 200px">&Uuml;bersjaarsch</th>
            <th>Mobiel</th>
            <th>E-mail</th>
            <th>Allergie</th>
        </tr>
        @php
            $oudeDatum = '';
            $row = 1;
            $kleuren = ['licht', 'donker'];
        @endphp
        @foreach($eetplan as $sessie)
            @php($sessie->avond == $oudeDatum ?: $row++) {{-- Alleen ophogen als avond veranderd --}}

            <tr class="{{ $kleuren[$row%2] }}">
                @if($sessie->avond == $oudeDatum)
                    <td>&nbsp;</td>
                @else
                    <td>{{ $sessie->avond }}</td>
                @endif
                <td>{!! $sessie->noviet->getLink('civitas') !!}</td>
                <td>{{ $sessie->noviet->mobiel }}</td>
                <td>{{ $sessie->noviet->email }}</td>
                <td>{{ $sessie->noviet->eetwens }}</td>
            </tr>

            @php($oudeDatum = $sessie->avond)
        @endforeach
    </table>
@endsection