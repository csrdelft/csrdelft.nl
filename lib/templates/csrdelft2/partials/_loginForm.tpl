{if !LoginSession::mag('P_LOGGED_IN') }
	<form action="/login.php" method="post">
		<fieldset>
			<input type="hidden" name="url" value="/" />
			<input class="text" type="text" name="user" placeholder="Bijnaam of lidnummer" />
			<input class="text" type="password" name="pass" placeholder="Wachtwoord" />
			<input class="submit" type="submit" name="login" value="Inloggen" />
		</fieldset>{if isset($smarty.session.auth_error)}
		<p class="error">{$smarty.session.auth_error}</p>{/if}
	</form>
	<ul>
		<li><a href="#" class="login-submit">Inloggen</a> &raquo;</li>
		<li><a href="/accountaanvragen">Account aanvragen</a> &raquo;</li>
	</ul>
{/if}