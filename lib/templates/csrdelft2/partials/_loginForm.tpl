{if !LoginModel::mag('P_LOGGED_IN') }
	{$loginform->view()}
	<ul>
		<li><a href="#" class="login-submit" onclick="document.getElementById('loginform').submit();">Inloggen</a> &raquo;</li>
		<li><a href="/accountaanvragen">Account aanvragen</a> &raquo;</li>
	</ul>
	<p class="error">{LoginModel::instance()->getError()}</p>
{/if}