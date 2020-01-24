
@if($deel->isOpenbaar())
	<div class="meldingen">
		<div id="public-melding" class="alert alert-danger">
			<strong>Openbaar forum</strong>
			Voor iedereen leesbaar, doorzoekbaar door zoekmachines.<br/>
			Zet [prive] en [/prive] om uw persoonlijke contactgegevens in het bericht.
		</div>
		@guest
			<div class="alert alert-info">
				Hier kunt u een bericht toevoegen aan het forum. Het zal echter niet direct zichtbaar worden, maar
				&eacute;&eacute;rst door de PubCie worden goedgekeurd.
				Het vermelden van <em>uw e-mailadres</em> is verplicht.
			</div>
		@endguest
	</div>
@endif

<div id="forumPosten" class="forum-posten">
	<div class="auteur">
		<div class="postpijl">
			<a class="postanchor"></a>
			<a class="postlink">&rarr;</a>
		</div>
		<div class="naam">
			{{\CsrDelft\repository\ProfielRepository::getNaam(CsrDelft\model\security\LoginModel::getUid(), 'user')}}
		</div>

		@auth
			<div
				class="forumpasfoto">{!! \CsrDelft\repository\ProfielRepository::getLink(CsrDelft\model\security\LoginModel::getUid(), 'pasfoto') !!}</div>
		@endauth
	</div>

	<div class="bericht0 flex-grow-1">

		<form id="forumForm" class="Formulier"
					action="/forum/posten/{{$deel->forum_id}}@if(isset($draad))/{{$draad->draad_id}}@endif" method="post">
			@csrf
			@guest
				<input type="text" name="email" class="FormElement TextField forumEmail form-control" placeholder="E-mailadres"/>
				<input type="text" name="firstname" value="" class="FormElement TextField verborgen"/>
				{{-- spam trap, must be kept empty! --}}
			@endguest
			@if($draad === null)
				<input type="text" id="nieuweTitel" name="titel" class="FormElement TextField form-control" tabindex="1"
							 placeholder="Onderwerp titel" value="{{$post_form_titel}}" origvalue="{{$post_form_titel}}"/>
			@endif
			<div id="preview_forumBericht" class="bbcodePreview forumBericht"></div>
			<textarea name="forumBericht" id="forumBericht" class="FormElement BBCodeField forumBericht" data-bbpreview="forumBericht" tabindex="2"
								rows="12"
								origvalue="{{$post_form_tekst}}">{{$post_form_tekst}}</textarea>
			<div class="butn">
				<input type="submit" name="submit" value="Opslaan" id="forumOpslaan" class="btn btn-primary"/>
				<input type="button" value="Voorbeeld" id="forumVoorbeeld" class="btn btn-secondary" data-bbpreview-btn="forumBericht"
							 />
				@auth
					<input type="button" value="Concept opslaan" id="forumConcept" class="btn btn-secondary"
								 onclick="window.forum.saveConceptForumBericht();"
								 data-url="/forum/concept/{{$deel->forum_id}}@if(isset($draad))/{{$draad->draad_id}}@endif"/>
					<div class="float-right">
						<a href="/fotoalbum/uploaden/{{CsrDelft\model\groepen\LichtingenModel::getHuidigeJaargang()}}/Posters" target="_blank">Poster opladen</a>
						<a href="/groepen/activiteiten/nieuw" class="post popup">Ketzer maken</a>
						<a href="/wiki/cie:diensten:forum" target="_blank">Opmaakhulp</a>
					</div>
				@endauth

			</div>

		</form>
	</div>

</div>
