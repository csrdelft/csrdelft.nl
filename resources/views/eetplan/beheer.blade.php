@extends ('layouts.intern')

@section('title', 'Eetplanbeheer')
@section('breadcrumbs', Breadcrumbs::render('eetplan.beheer'))

@section('head')
    @include('partial.stylesheet', ['sheets' => [asset('css/module/eetplan.css'), asset('css/module/datatable.css'), asset('css/module/formulier.css')]])
@endsection

@section('content')
    {!! $huizentable !!}
    {!! $bekendentable !!}
    {!! $bekendehuizentable !!}

    <a href="{{ route('eetplan.nieuw') }}" class="btn post popup">Nieuw Eetplan</a>
    <a href="{{ route('eetplan.verwijderen') }}" class="btn post popup">Eetplan verwijderen</a>

    @include('eetplan.table')
@endsection