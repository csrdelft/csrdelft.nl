{*
	lidinstelling.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
	<label class="instelling" for="inst_{$module}_{$id}">
		{if $reset}
			<img src="{$CSR_PICS}/famfamfam/arrow_rotate_anticlockwise.png" title="U gaat nu deze instelling voor iedereen resetten naar de standaard waarde. (Die moet in de code worden aangepast.)" onclick="if (confirm(this.title + '\n\nWeet u het zeker?')) {
				location.href = '/instellingen/reset/{$module}/{$id}/' + $('#inst_{$module}_{$id}').val();
			}" class="vooriedereen" />
		{/if}
		{$label}
	</label>
	{if $type === T::Enumeration}
		<select type="select" id="inst_{$module}_{$id}" name="{$module}_{$id}" class="FormElement SelectField" origvalue="{$waarde}">
			{foreach from=$opties item=optie}
				<option value="{$optie}"{if $optie === $waarde} selected="selected"{/if}>{ucfirst($optie)}</option>
			{/foreach}
		</select>
	{else}
		<input type="text" id="inst_{$module}_{$id}" name="{$module}_{$id}" class="FormElement {if $type === T::String}InputField{elseif $type === T::Integer}IntField{/if}" value="{$waarde}" origvalue="{$waarde}"{if $type === T::String} maxlength="{$opties[1]}"{/if} />
	{/if}
	&nbsp;({ucfirst($default)})
	<br /><br />
{/strip}