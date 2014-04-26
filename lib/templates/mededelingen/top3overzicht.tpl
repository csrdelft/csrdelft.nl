<ul class="horizontal nobullets">
	<li>
		<a href="{$mededelingen_root}" title="Mededelingenketzer">Mededelingenketzer</a>
	</li>
	<li class="active">
		<a href="{$mededelingen_root}/top3overzicht" title="Top 3 Overzicht">Top 3 Overzicht</a>
	</li>
	<li>
		<a href="{$mededelingen_root}/prullenbak" title="Prullenbak">Prullenbak</a>
	</li>
</ul>
<hr />
<h1>Mededelingen Top 3 Overzicht</h1>
Deze pagina laat zien hoe de Top 3 wordt weergegeven bij niet-leden, oudleden en leden.
<div id="top3overzicht">
	<div class="top3container">
		<h2>Niet-leden</h2>
		{'[mededelingen=top3nietleden]'|ubb}
	</div>
	<div class="top3container">
		<h2>Oudleden</h2>
		{'[mededelingen=top3oudleden]'|ubb}
	</div>
	<div class="top3container">
		<h2>Normale leden</h2>
		{'[mededelingen=top3leden]'|ubb}
	</div>
</div>