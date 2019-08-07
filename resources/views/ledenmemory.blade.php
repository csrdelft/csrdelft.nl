<!doctype html>
<html>
<head>
	<title>{{$titel}}</title>
	@stylesheet('ledenmemory.css')
	@script('ledenmemory.js')
</head>
<body data-groep="{{$groep->getUUID()}}">
<table>
	<tbody>
	<tr>
		<td class="pasfotos">
			@if (!$cheat)
				@php(shuffle($leden))
			@endif
			@foreach($leden as $lid)
				<div uid="{{$lid->uid}}" class="flip memorycard pasfoto {{$learnmode?'flipped':''}}">
					<div class="blue front">
						@if($cheat)
							{{$lid->uid}}
						@endif
					</div>
					<div class="blue back">
						<img src="{{$lid->getPasFotoPath(true)}}" title="{{$learnmode?$lid->getNaam('volledig'):''}}"/>
					</div>
				</div>
			@endforeach
		</td>
		<td class="namen">
			@if (!$cheat AND !$learnmode)
				@php(shuffle($leden))
			@endif
			@foreach ($leden as $lid)
				<div uid="{{$lid->uid}}" class="flip memorycard naam {{$learnmode?'flipped':''}}">
					<div class="blue front">
						@if($cheat)
							{{$lid->uid}}
						@endif
					</div>
					<div class="blue back">
						<h3 title="{{$lid->getNaam('volledig')}}">{{$lid->getNaam('civitas')}}</h3>
					</div>
				</div>
			@endforeach
		</td>
	</tr>
	</tbody>
</table>
</body>
</html>
