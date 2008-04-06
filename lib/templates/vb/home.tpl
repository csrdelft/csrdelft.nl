<h1>Vormingsbank C.S.R.Delft</h1>
<table width = "100%">
	<tr>
		<td width = "50%">
			<h2>Snel zoeken</h2>
			{$search}
			<br/>
			<h2>Over de vormingsbank</h2>
			Blaah goed verhaal
		</td>
		<td>
			Hoofd thema's
			<table>
				{section name=sec1 loop=$themes}
				<tr><td>
					<a href="index.php?actie=subject&id={$themes[sec1]->id}">
						{$themes[sec1]->name}
					</a>
				</td></tr>
				{/section}
			</table>
			<!-- use allowedit, not allowadd, to add a main theme -->
			{if $allowedit}
				<a href="index.php?actie=subject&id=0">Hoofdthema's beheren</a>
			{/if}
		</td>
	</tr>
</table>
				