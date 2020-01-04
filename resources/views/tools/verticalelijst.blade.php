@extends('layout')

@section('titel', 'Verticale emaillijsten')

@section('content')
	<h1>Verticale emaillijsten</h1>
	<p>Gebruik deze lijstjes om de maillijsten opnieuw in te stellen.</p>

	<table class="table">
		<tr>
			@foreach($verticalen as $letter => $verticale)
				<th scope="col"><h3>Verticale {{CsrDelft\model\groepen\VerticalenModel::instance()->get($letter)->naam }}</h3></th>
			@endforeach
		</tr>
		<tr>
			@foreach($verticalen as $letter => $verticale)
				<td>
<pre onclick="let range = document.createRange(); range.selectNode(this); window.getSelection().addRange(range)">
@foreach($verticale as $profiel)
{{$profiel->uid}}@csrdelft.nl
@endforeach
</pre>
				</td>
			@endforeach
		</tr>
	</table>
@endsection
