{if !LoginModel::mag('P_LOGGED_IN') }
	{$loginform->view()}
	<p class="error">{LoginModel::instance()->getError()}</p>
	<ul>
		<li><a href="#" class="login-submit">Inloggen</a> &raquo;</li>
		<li><a href="/accountaanvragen">Account aanvragen</a> &raquo;</li>
	</ul>
{/if}