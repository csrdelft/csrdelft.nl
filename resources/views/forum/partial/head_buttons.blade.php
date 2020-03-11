<?php
/**
 * @var \CsrDelft\entity\forum\ForumDeel $deel
 */
?>
@can(P_LOGGED_IN)
	@if($deel->magMeldingKrijgen())
		<div class="btn-group mr-2">
			<a href="/forum/deelmelding/{{$deel->forum_id}}/uit" class="btn btn-light post ReloadPage melding-nooit @if(!$deel->lidWilMeldingVoorDeel()) active @endif"
				 title="Geen meldingen voor forumdeel onvangen">@icon('email_delete', 'email_delete')</a>
			<a href="/forum/deelmelding/{{$deel->forum_id}}/aan" class="btn btn-light post ReloadPage melding-altijd @if($deel->lidWilMeldingVoorDeel()) active @endif"
				 title="Meldingen ontvangen voor nieuwe berichten in forumdeel">@icon('email_add', 'email_add')</a>
		</div>
	@endif
	<div class="btn-group mr-2">
		<a href="/forum/toonalles" class="btn btn-light post confirm ReloadPage"
			 title="Verborgen onderwerpen weer laten zien">@icon('eye') {{\CsrDelft\common\ContainerFacade::getContainer()->get(\CsrDelft\repository\forum\ForumDradenVerbergenRepository::class)->getAantalVerborgenVoorLid()}}</a>
	</div>
	@if(!isset($deel->forum_id) || (isset($deel->forum_id) && $deel->magModereren()))
		<div class="btn-group mr-2">
			<a href="/forum/wacht" class="btn btn-light"
				 title="Reacties die wachten op goedkeuring">@icon('hourglass') {{\CsrDelft\common\ContainerFacade::getContainer()->get(\CsrDelft\repository\forum\ForumPostsRepository::class)->getAantalWachtOpGoedkeuring()}}</a>
		</div>
	@endif
@endcan
