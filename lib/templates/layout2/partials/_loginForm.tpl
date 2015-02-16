{if !LoginModel::mag('P_LOGGED_IN') }
	{$loginform->view()}
{/if}