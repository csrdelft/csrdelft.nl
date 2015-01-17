{if !LoginModel::mag('P_LOGGED_IN') }
	{$loginform->view()}
	<ul>
		<li>
			<a href="#" class="login-submit" onclick="document.getElementById('loginform').submit();">Inloggen</a> &raquo;
			&nbsp; <a href="/accountaanvragen">Account aanvragen</a> &raquo;
		</li>
		<li><a href="/wachtwoord/vergeten">Wachtwoord vergeten?</a></li>
	</ul>
{/if}