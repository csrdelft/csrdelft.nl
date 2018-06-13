@extends('layouts.intern')

@section('head')
    @include('partial.stylesheet', ['sheets' => [asset('css/module/formulier.css')]])
@endsection

@section('content')
    @php
        $form->view()
    @endphp
@endsection