<div id="modereren">
	<table>
		<tbody>
		<tr>
			<td>
				<a href="/forum/wijzigen/{{$draad->draad_id}}/plakkerig" class="btn btn-light post ReloadPage" title="Verander plakkerigheid">
					@icon('note') maak @if($draad->plakkerig)<span class="dikgedrukt">niet</span> @endif plakkerig
				</a>
				<br /><br />
				<a href="/forum/wijzigen/{{$draad->draad_id}}/eerste_post_plakkerig" class="btn btn-light post ReloadPage form-check form-check-inline" title="Verander plakkerigheid van eerste post">
					<input id="forummod-plakkerig" class="form-check-input" type="checkbox" @if($draad->eerste_post_plakkerig)checked="checked" @endif/>
					<label for="forummod-plakkerig" class="form-check-label">1e post plakkerig</label>
				</a>
				<br /><br />
				<a href="/forum/wijzigen/{{$draad->draad_id}}/pagina_per_post" class="btn btn-light post ReloadPage form-check form-check-inline" title="Verander 1 pagina per post">
					<input id="forummod-paginaperpost" class="form-check-input" type="checkbox" @if($draad->pagina_per_post)checked="checked" @endif/>
					<label for="forummod-paginaperpost" class="form-check-label">1 pagina per post</label>
				</a>
			</td>
			<td>
				<a href="/forum/wijzigen/{{$draad->draad_id}}/verwijderd" class="btn btn-light post confirm ReloadPage" title="Verander status verwijderd (incl. alle reacties)">
					@if($draad->verwijderd)
						@icon('arrow_undo') draad herstellen
					@else
						@icon('cross') draad verwijderen
					@endif
				</a>
				&nbsp;
				<a href="/forum/onderwerp/{{$draad->draad_id}}/prullenbak" class="btn btn-light"
					 title="Bekijk de reacties die zijn verwijderd">@icon('bin_closed') verwijderde reacties</a>
				<br/><br/>
				<form action="/forum/wijzigen/{{$draad->draad_id}}/forum_id" method="post">
					@csrf
					<label>Verplaats naar &nbsp;</label>
					<select name="forum_id">
						@foreach($categorien as $categorie)
							<optgroup label="{{$categorie->titel}}">
								@foreach($categorie->getForumDelen() as $newDeel)
									<option value="{{$newDeel->forum_id}}"
													@if($newDeel->forum_id === $draad->getForumDeel()->forum_id) selected="selected" @endif>{{$newDeel->titel}}</option>
								@endforeach
							</optgroup>
						@endforeach
					</select>
					<input type="submit" value="Opslaan" class="btn btn-primary" />
				</form>
				<br />
				<form action="/forum/wijzigen/{{$draad->draad_id}}/titel" method="post">
					@csrf
					<label for="titel">Titel aanpassen &nbsp;</label>
					<input id="titel" type="text" name="titel" value="{{$draad->titel}}" />
					<input type="submit" value="Opslaan" class="btn btn-primary" />
				</form>
				@can('P_FORUM_BELANGRIJK')
				<br />
				<form action="/forum/wijzigen/{{$draad->draad_id}}/belangrijk" method="post">
					@csrf
					<label for="belangrijk">Belangrijk markeren &nbsp;</label>
					<select id="belangrijk" name="belangrijk">
						<option value="" @if(!$draad->belangrijk)selected="selected"@endif>Niet belangrijk</option>
						@foreach(\CsrDelft\model\forum\ForumDradenModel::$belangrijk_opties as $group => $list)
						<optgroup label="{{$group}}">
							@foreach($list as $value => $label)
							<option value="{{$value}}" @if($value === $draad->belangrijk)selected="selected" @endif>{{$label}}</option>
								@endforeach
						</optgroup>
							@endforeach
					</select>
					<input type="submit" value="Opslaan" class="btn btn-primary" />
				</form>
				@endcan
				@if($gedeeld_met_opties)
				<br />
				<form action="/forum/wijzigen/{{$draad->draad_id}}/gedeeld_met" method="post">
					@csrf
					<label for="gedeeld_met">Delen met &nbsp;</label>
					<select id="gedeeld_met" name="gedeeld_met">
						<option value=""></option>
						@foreach($gedeeld_met_opties as $gedeeld_deel)
						<option value="{{$gedeeld_deel->forum_id}}" @if($draad->gedeeld_met === $gedeeld_deel->forum_id) selected="selected" @endif>{{$gedeeld_deel->titel}}</option>
							@endforeach
					</select>
					<input type="submit" value="Opslaan" class="btn btn-primary" />
				</form>
					@endif
			</td>
			<td>
					<span id="modsluiten" onclick="$('#togglemodknop').toggle();
							$('#modereren').slideUp();
							$('#forumtabel a.forummodknop').fadeOut();" title="Moderatie-functies verbergen">×</span>
			</td>
		</tr>
		</tbody>
	</table>
</div>
