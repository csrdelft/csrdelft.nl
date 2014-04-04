{if isset($smarty.session.auth_error)}
	<span class="waarschuwing">{$smarty.session.auth_error}</span>
{/if}
<form action="/login.php" method="post">
	<fieldset>
		<input type="hidden" name="url" value="{if array_key_exists('pauper', $smarty.session)}/pauper{else}{$smarty.server.REQUEST_URI}{/if}" />
		<input type="text" name="user" value="naam" onfocus="if (this.value === 'naam')
					this.value = '';" />
		<input type="password" name="pass" value="wachtwoord" />
		<input type="checkbox" name="checkip" class="checkbox" value="true" id="login-checkip" />
		<label for="login-checkip">Koppel IP</label>
		<input type="submit" class="submit" name="submit" value="Inloggen" />
	</fieldset>
</form>