<div id="wijzigstatus" >
	<div id="profielregel">
		<div class="naam">
			{getMelding()}
			<h1>Voorkeuren Opgeven</h1>
			<div class="lidgegevens">
				<label for="">Naam:</label>{$profiel->getUid()|csrnaam:'full'}<br />
			</div>
		</div>
	</div>

	<p>
	Hier kunt u per commissie opgeven of u daar interesse in heeft!
	</p>
	{$profiel->getFormulier()->view()}
</div>
