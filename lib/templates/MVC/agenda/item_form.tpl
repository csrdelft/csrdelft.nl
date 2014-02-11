<div id="popup-content">
	<h1>Agenda-item {$actie}</h1>
	{if $actie == 'toevoegen'}
		<p>Vul de onderstaande velden in om een item toe te voegen aan de agenda.</p>
	{elseif $actie == 'bewerken'}
		<p>Bewerk de onderstaande velden om een item te wijzigen in de agenda.</p>
	{/if}
	{$form->view()}
</div>