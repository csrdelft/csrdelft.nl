<div id="eetplan" class="row no-gutters">
	<div class="col-auto">
		<table class="table table-striped">
			<thead>
			<tr>
				<th scope="col">Novieten</th>
			</tr>
			</thead>
			<tbody>
			@foreach($eetplan['novieten'] as $noviet)
				<tr>
					<td><a href="/eetplan/noviet/{{$noviet['uid']}}">{{$noviet['naam']}}</a></td>
				</tr>
			@endforeach
			</tbody>
		</table>
	</div>
	<div class="col-auto">
		<table class="table table-striped">
			<thead>
			<tr>
				@foreach($eetplan['avonden'] as $avond)
					<th scope="col">{{$avond}}</th>
				@endforeach
			</tr>
			</thead>
			<tbody>
			@foreach($eetplan['novieten'] as $noviet)
				<tr>
					@foreach($noviet['avonden'] as $avond)
						<td><a href="/eetplan/huis/{{$avond['woonoord_id']}}">{{$avond['woonoord']}}</a></td>
					@endforeach
				</tr>
			@endforeach
			</tbody>
		</table>
	</div>
</div>
