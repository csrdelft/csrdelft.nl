@extends('layouts.intern')

@section('title', 'Account bewerken')

@section('head')
    @include('partial.stylesheet', ['sheets' => [asset('css/module/formulier.css')]])
@endsection

@section('breadcrumbs', Breadcrumbs::render('account.bewerken', $account))

@section('content')
    {!! $form !!}
@endsection