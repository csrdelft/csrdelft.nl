{if !LoginModel::mag('P_LOGGED_IN') }
	{$loginform->view()}
	<ul>
		<li><a href="#" class="login-submit" onclick="document.getElementById('loginform').submit();">Inloggen</a> &raquo; &nbsp; <a href="/wachtwoord/vergeten">Wachtwoord vergeten?</a></li>
		<li>
			{if LoginModel::instance()->hasError()}
				<p class="error">{LoginModel::instance()->getError()}</p>
			{else}
				<a href="/accountaanvragen">Account aanvragen</a> &raquo;
			{/if}
		</li>
	</ul>
{/if}