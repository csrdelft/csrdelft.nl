<?php
/**
 * @var $streeplijst CsrDelft\entity\Streeplijst
 */
?>
@extends("plain")
@section('titel', 'Streeplijstgenerator')

@section("content")
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th scope="col"></th>
			@foreach($streeplijst->getInhoud() as $inhoud)
				<th scope="col">{{trim($inhoud)}}</th>
			@endforeach
		</tr>
		</thead>
		<tbody>

		@foreach($streeplijst->getLeden() as $lid)
			<tr>
				<th scope="row">  {{trim($lid)}}</th>
				@foreach($streeplijst->getInhoud() as $inhoud)
					<td></td>
				@endforeach
			</tr>
		@endforeach

		</tbody>
	</table>


@endsection
