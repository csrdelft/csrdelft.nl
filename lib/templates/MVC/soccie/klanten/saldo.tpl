<table class="saldo-big">
	<tr>
		<td>&euro;</td>
		<td>
			<div class="saldo-getal{if $saldo < 0} staatrood{/if}">
				{$saldo|number_format:2:",":"."}
			</div>
		</td>
	</tr>
</table>