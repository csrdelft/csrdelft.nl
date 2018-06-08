@extends('layouts.intern')

@section('content')
    @mag($rechten_bewerken)
    <a href="{{ route('cmspagina.bewerken', [$naam]) }}" class="btn float-right" title="Bewerk pagina 2018-02-02 14:39:57"><span class="ico pencil"></span></a>
    @endmag

    {!! $pagina !!}
@endsection