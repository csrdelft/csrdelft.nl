@extends('layout')

@section('titel', 'Statistieken')

@section('content')
	Opties:<br>
	- <a href="?ip=192.168.1.33">/tools/stats?ip=192.168.1.33</a><br>
	- <a href="?uid=x101">/tools/stats?uid=x101</a>
	<table class="table">
		<tr>
			<td>tijd</td>
			<td>Naam</td>
			<td>hostnaam</td>
			<td>url</td>
			<td>useragent</td>
			<td>referer</td>
		</tr>
		@foreach($log as $logRegel)
			<tr>
				<td>
					{{date('D H:i', strtotime($logRegel->moment))}}
				</td>
				<td>
					<a href="?uid={{$logRegel->uid}}">+</a>
					@php(print \CsrDelft\model\ProfielModel::getLink($logRegel->uid, 'volledig'))
				</td>
				<td>
					<a href="?ip={{$logRegel->ip}}">+</a> {{gethostbyaddr($logRegel->ip)}}
					<span class="dikgedrukt">{{$logRegel->locatie}}</span>
				</td>
				@if(preg_match('/toevoegen/', $logRegel->url))
					<td style="background-color: yellow">
				@elseif(preg_match('/maak-stemming/', $logRegel->url))
					<td style="background-color: #C00">
				@elseif(preg_match('/zoeken/', $logRegel->url))
					<td style="background-color: #3F9">
				@else
					<td>
				@endif
						<a href="{{ CSR_ROOT . $logRegel->url }}" target="_blank">{{$logRegel->url}}</a>
					</td>
				<td>{{$logRegel->useragent}}</td>
				<td>
					@if($logRegel->referer != '')
						<a href="{{$logRegel->referer}}" target="_blank">{{$logRegel->getFormattedReferer()}}</a>
					@else
						-
					@endif
				</td>
			</tr>
		@endforeach
	</table>

@endsection
