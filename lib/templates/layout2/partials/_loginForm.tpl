{if !LoginModel::mag('P_LOGGED_IN') }
	{$loginform->view()}
	{if LoginModel::instance()->hasError()}
		<p class="error">{LoginModel::instance()->getError()}</p>
	{else}
		<ul>
			<li><a href="#" class="login-submit" onclick="document.getElementById('loginform').submit();">Inloggen</a> &raquo;</li>
			<li><a href="/accountaanvragen">Account aanvragen</a> &raquo;</li>
		</ul>
	{/if}
{/if}