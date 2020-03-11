<?php
/**
 * @var \CsrDelft\entity\forum\ForumDraad $draad
 * @var \CsrDelft\entity\forum\ForumCategorie[] $categorien
 */
?>
<div id="modereren" class="card collapse forum-header">
	<div class="modal-header">
		<h5 class="modal-title">Draad modereren</h5>
		<button type="button" class="close" aria-label="Close" onclick="$('#modereren').slideUp()">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
	<div class="card-body">
		<div class="form-group d-flex justify-content-between">
			<a href="/forum/wijzigen/{{$draad->draad_id}}/plakkerig" class="btn btn-light post ReloadPage"
				 title="Verander plakkerigheid">
				@icon('note') maak @if($draad->plakkerig)<span class="dikgedrukt">niet</span> @endif plakkerig
			</a>
			<a href="/forum/wijzigen/{{$draad->draad_id}}/eerste_post_plakkerig"
				 class="btn btn-light post ReloadPage @if($draad->eerste_post_plakkerig) active @endif"
				 title="Verander plakkerigheid van eerste post">
				1e post plakkerig
			</a>
			<a href="/forum/wijzigen/{{$draad->draad_id}}/pagina_per_post"
				 class="btn btn-light post ReloadPage @if($draad->pagina_per_post) active @endif"
				 title="Verander 1 pagina per post">
				1 pagina per post
			</a>
			<a href="/forum/wijzigen/{{$draad->draad_id}}/verwijderd" class="btn btn-light post confirm ReloadPage"
				 title="Verander status verwijderd (incl. alle reacties)">
				@if($draad->verwijderd)
					@icon('arrow_undo') draad herstellen
				@else
					@icon('cross') draad verwijderen
				@endif
			</a>
			<a href="/forum/onderwerp/{{$draad->draad_id}}/prullenbak" class="btn btn-light"
				 title="Bekijk de reacties die zijn verwijderd">@icon('bin_closed') verwijderde reacties</a>
		</div>
		<form action="/forum/wijzigen/{{$draad->draad_id}}/forum_id" method="post">
			@csrf
			<div class="form-group row">
				<label for="verplaats-naar" class="col-md-3 col-form-label">Verplaats naar &nbsp;</label>
				<div class="col-md-6">
					<select id="verplaats-naar" class="form-control" name="forum_id">
						@foreach($categorien as $categorie)
							<optgroup label="{{$categorie->titel}}">
								@foreach($categorie->forum_delen as $newDeel)
									<option value="{{$newDeel->forum_id}}"
													@if($newDeel->forum_id === $draad->getForumDeel()->forum_id) selected="selected" @endif>{{$newDeel->titel}}</option>
								@endforeach
							</optgroup>
						@endforeach
					</select>
				</div>
				<div class="col-md-3">
					<input type="submit" value="Opslaan" class="btn btn-primary"/>
				</div>
			</div>
		</form>
		<form action="/forum/wijzigen/{{$draad->draad_id}}/titel" method="post">
			@csrf
			<div class="form-group row">
				<label for="titel" class="col-md-3 col-form-label">Titel aanpassen &nbsp;</label>
				<div class="col-md-6">
					<input id="titel" class="form-control" type="text" name="titel" value="{{$draad->titel}}"/>
				</div>
				<div class="col-md-3">
					<input type="submit" value="Opslaan" class="btn btn-primary"/>
				</div>
			</div>
		</form>
		@can(P_FORUM_BELANGRIJK)
			<form action="/forum/wijzigen/{{$draad->draad_id}}/belangrijk" method="post">
				@csrf
				<div class="form-group row">
					<label for="belangrijk" class="col-md-3 col-form-label">Belangrijk markeren &nbsp;</label>
					<div class="col-md-6">
						<select id="belangrijk" class="form-control" name="belangrijk">
							<option value="" @if(!$draad->belangrijk)selected="selected"@endif>Niet belangrijk</option>
							@foreach(\CsrDelft\repository\forum\ForumDradenRepository::$belangrijk_opties as $group => $list)
								<optgroup label="{{$group}}">
									@foreach($list as $value => $label)
										<option value="{{$value}}"
														@if($value === $draad->belangrijk)selected="selected" @endif>{{$label}}</option>
									@endforeach
								</optgroup>
							@endforeach
						</select>
					</div>
					<div class="col-md-3">
						<input type="submit" value="Opslaan" class="btn btn-primary"/>
					</div>
				</div>
			</form>
		@endcan
		@if($gedeeld_met_opties)
			<form action="/forum/wijzigen/{{$draad->draad_id}}/gedeeld_met" method="post">
				@csrf
				<div class="form-group row">
					<label for="gedeeld_met" class="col-md-3 col-form-label">Delen met &nbsp;</label>
					<div class="col-md-6">
						<select id="gedeeld_met" class="form-control" name="gedeeld_met">
							<option value=""></option>
							@foreach($gedeeld_met_opties as $gedeeld_deel)
								<option value="{{$gedeeld_deel->forum_id}}"
												@if($draad->gedeeld_met === $gedeeld_deel->forum_id) selected="selected" @endif>{{$gedeeld_deel->titel}}</option>
							@endforeach
						</select>
					</div>
					<div class="col-md-3">
						<input type="submit" value="Opslaan" class="btn btn-primary"/>
					</div>
				</div>
			</form>
		@endif
	</div>
</div>
