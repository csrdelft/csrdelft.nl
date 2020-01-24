<script type="text/javascript">
	var captchaLoaded = false;

	function checkVisible(elm) {
		var rect = elm.getBoundingClientRect();
		var viewHeight = Math.max(document.documentElement.clientHeight, window.innerHeight);
		return !(rect.bottom < 0 || rect.top - viewHeight >= 0);
	}

	var onloadCallback = function() {
		window.addEventListener('scroll', function () {

			if (checkVisible(document.getElementById('captcha')) && !captchaLoaded) {
				captchaLoaded = true
				grecaptcha.render('captcha', {
					'sitekey': '6Lc9TCITAAAAAGglcvgYvSwL-ci4A3Hkv8s1xRIX'
				});
			}
		})
	};
</script>
<form method="post" id="contact-form" action="/contactformulier/interesse">
  {!! getMelding() !!}
	@csrf
	<div class="field">
		<label for="naam">Naam</label>
		<input type="text" name="naam" id="naam" required/>
	</div>
	<input type="text" name="achternaam" class="verborgen"/>
	<div class="field">
		<label for="submit_by">Email</label>
		<input type="email" name="submit_by" id="submit_by" required/>
	</div>
	<div class="field">
		<label for="straat">Adres</label>
		<input type="text" name="straat" id="straat" required/>
	</div>
	<div class="field">
		<label for="postcode">Postcode</label>
		<input type="text" name="postcode" id="postcode" required/>
	</div>
	<div class="field">
		<label for="plaats">Woonplaats</label>
		<input type="text" name="plaats" id="plaats" required/>
	</div>
	<div class="field">
		<label for="telefoon">Telefoon</label>
		<input type="text" name="telefoon" id="telefoon"/>
	</div>
	<div class="field">
		<input type="checkbox" id="interesse1" name="interesse1"
					 value="Ik wil een informatiepakket op bovenstaand adres">
		<label for="interesse1">Ik wil een informatiepakket op bovenstaand adres</label>
	</div>
	<div class="field">
		<input type="checkbox" id="interesse2" name="interesse2"
					 value="Ik kom donderdagavond eten bij C.S.R.">
		<label for="interesse2">Ik kom donderdagavond eten bij C.S.R. (Vul hieronder datum en evt. dieet
			in)</label>
	</div>
	<div class="field">
		<input type="checkbox" id="interesse3" name="interesse3"
					 value="Ik wil graag meelopen met een C.S.R.'er">
		<label for="interesse3">Ik wil graag meelopen met een C.S.R.'er (Vul hieronder je studie,
			onderwijsinstelling en een voorkeurdatum in)</label>
	</div>
	<div class="field">
		<input type="checkbox" id="interesse4" name="interesse4"
					 value="Ik wil donderdag graag langskomen op een borrel">
		<label for="interesse4">Ik wil donderdag graag langskomen op een borrel (Vul hieronder datum
			in)</label>
	</div>
	<div class="field">
		<label for="opmerking">Opmerking</label>
		<textarea name="opmerking" id="opmerking" rows="4"></textarea>
	</div>
	<div class="field" id="captcha"></div>
	<div class="field">
		Met het verzenden van dit formulier ga je akkoord met de <a href="/download/Privacyverklaring%20C.S.R.%20Delft%20-%20Extern%20-%2025-05-2018.pdf">privacyverklaring</a> van C.S.R. Delft.
	</div>
	<ul class="actions">
		<li><button type="submit" name="submitButton">Verzenden</button></li>
	</ul>
</form>
<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
