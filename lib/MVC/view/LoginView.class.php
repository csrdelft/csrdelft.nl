<?php

/**
 * LoginView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van het login formulier.
 */
class LoginForm extends Formulier {

	public function __construct() {
		parent::__construct($model, $formId, $action);

		<<<HTML
{if isset(LoginModel::instance()->getError())}
	<span class="waarschuwing">{LoginModel::instance()->getError()}</span>
{/if}
<form action="/login.php" method="post">
	<fieldset>
		<input type="hidden" name="url" value="{if array_key_exists('pauper', $smarty.session)}/pauper{else}{Instellingen::get('stek', 'request')}{/if}" />
		<input type="text" name="user" value="naam" onfocus="if (this.value === 'naam')
					this.value = '';" />
		<input type="password" name="pass" value="wachtwoord" />
		<input type="checkbox" name="checkip" class="checkbox" value="true" id="login-checkip" />
		<label for="login-checkip">Koppel IP</label>
		<input type="submit" class="submit" name="submit" value="Inloggen" />
	</fieldset>
</form>
		
		<span style="position: fixed; right: 300px; width: 300px; border: 1px solid red; padding: 2px; background-color: white;">Het spijt ons heel erg, maar met de gegeven
				inloggegevens is het niet mogelijk in te loggen. Zou het
				eventueel mogelijk zijn dat u, geheel per ongeluk, een fout heeft
				gemaakt met invoeren? In dat geval bieden wij u onze nederige
				excuses aan en vragen wij u het nog eens te proberen.</span>
HTML;
	}

}
