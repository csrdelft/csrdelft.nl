@extends ('layouts.intern')

@section('title', 'Eetplan')
@section('breadcrumbs', Breadcrumbs::render('eetplan.noviet', $eetplan[0]->noviet->getNaam()))

@section('head')
    @include('partial.stylesheet', ['sheets' => [asset('css/module/eetplan.css')]])
@endsection

@section('content')
    <table class="eetplantabel">
        <tr>
            <th style="width: 150px;">Avond</th><th style="width: 200px">Huis</th>
        </tr>

        @foreach($eetplan as $index => $sessie)
            <tr class="@cycle('donker', 'licht')">
                <td>{{ $sessie->avond }}</td>
                <td><a href="/groepen/woonoorden/{{ $sessie->woonoord_id }}">{{ $sessie->woonoord()->naam }}</a></td>
            </tr>
        @endforeach
    </table>
@endsection