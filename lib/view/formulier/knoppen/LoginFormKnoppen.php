<?php


namespace CsrDelft\view\formulier\knoppen;


class LoginFormKnoppen extends FormKnoppen
{
	public function getHtml()
	{
		return <<<HTML
<ul class="login-buttons">
	<li>
		<input type="submit" value="Inloggen &raquo;" />
		&nbsp;
		<a href="/accountaanvragen">Account aanvragen &raquo;</a>
	</li>
	<li><a href="/wachtwoord/vergeten">Wachtwoord vergeten?</a></li>
</ul>
HTML;
	}
}
