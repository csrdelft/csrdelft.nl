<div id="inner">
	<div class="row">
		<div class="columns large-2 small-2">
			<header class="blue box h2">
				<a href="/" alt="Thuis">
					<img src="{$CSR_PICS}/layout2/box-logo.png" alt="C.S.R. Delft">
				</a>
			</header>
		</div>
		<div class="columns large-2 small-2">
			<div class="columns large-2 hide-for-small">
				<a href="/csrindeowee/">
					<figure class="box">
						<img src="{$CSR_PICS}/owee/2014/owee-thema.jpg" class="rotate" alt="C.S.R. in de Owee">
					</figure>
				</a>
			</div>
			<div class="columns large-2">
				<figure class="box">
					<img src="{$CSR_PICS}/layout2/box-picture-3.jpg" class="rotate" alt="Foto">
				</figure>
			</div>
		</div>
		<div class="columns large-2 small-2">
			<div class="columns large-2">
				<div class="box flip fliphover">
					<div class="blue front">
						<h2 class="overlay">Lid worden?</h2>
						<img src="{$CSR_PICS}/layout2/box-icon-lid.png" alt="Lid worden">
					</div>
					<div class="blue back">
						<p>Ga je studeren in Delft? Word lid bij een vereniging!</p>
						<ul>
							<li><a href="/lidworden">Informatie over C.S.R.</a> &raquo;</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="columns large-2">
				<figure class="box">
					<img src="{$CSR_PICS}/layout2/box-picture-4.jpg" class="rotate" alt="Foto">
				</figure>
			</div>
		</div>
		<div class="columns large-2 small-2">
			<div class="columns large-2">
				<figure class="box">
					<img src="{$CSR_PICS}/layout2/box-picture-2.jpg" class="rotate" alt="Foto">
				</figure>
			</div>
			<div class="columns large-2">
				<div class="box flip fliphover">
					<div class="blue front">
						<h2 class="overlay">Bedrijven</h2>
						<img src="{$CSR_PICS}/layout2/box-icon-bedrijven.png" alt="Bedrijven">
					</div>
					<div class="blue back">
						<p>Trek de aandacht van ruim 270 technisch ge&ouml;rienteerde studenten!</p>
						<ul>
							<li><a href="/contact/sponsoring">Meer informatie en sponsormogelijkheden</a> &raquo;</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<div class="columns large-2 small-2">
			<div class="columns large-2">
				<div class="box flip fliphover">
					<div class="blue front">
						<h2 class="overlay">(Oud)leden</h2>
						<img src="{$CSR_PICS}/layout2/box-icon-oudleden.png" alt="(Oud)leden">
					</div>
					<div class="blue back login-form">{include file='csrdelft2/partials/_loginForm.tpl'}</div>
				</div>

			</div>
			<div class="columns large-2 hide-for-small">
				<div class="empty box"></div>
			</div>
		</div>
		<div class="columns large-2 small-2 hide-for-small">
			<figure class="box pad-top">
				<img src="{$CSR_PICS}/layout2/box-picture-5.jpg" class="rotate" alt="Foto">
			</figure>
		</div>

		<div class="columns large-4 hide-for-small">
			<div class="blue box w2 h2">
				<h1>Vereniging van christenstudenten</h1>
				<p>{Instellingen::get('stek', 'beschrijving')}</p>

				<ul>
					<li><a href="/forum/deel/2">Promoot jouw studentenactiviteit bij C.S.R.</a> &raquo;</li>
					<li><a href="/forum/deel/12">Kamers zoeken/aanbieden</a> &raquo;</li>
					<li><a href="/contact">Contactinformatie</a> &raquo;</li>
				</ul>
			</div>
		</div>
		<div class="columns small-2 show-for-small">
			<div class="blue box h4">
				<h1>Vereniging van christenstudenten</h1>
				<p>{Instellingen::get('stek', 'beschrijving')}</p>

				<ul>
					<li><a href="/forum/deel/2">Promoot jouw studentenactiviteit bij C.S.R.</a> &raquo;</li>
					<li><a href="/forum/deel/12">Kamers zoeken/aanbieden</a> &raquo;</li>
					<li><a href="/contact">Contactinformatie</a> &raquo;</li>
				</ul>
			</div>
		</div>
		<div class="columns large-2 small-2">
			<figure class="box">
				<img src="{$CSR_PICS}/layout2/box-picture-1.jpg" class="rotate" alt="Foto">
			</figure>
		</div>
		<div class="blue box pad-left link">
			<a href="/english" title="English">
				<h2 class="overlay">English</h2>
				<img src="{$CSR_PICS}/layout2/icon-flag-english.png" alt="English" style="width: 120px; height: 67px; position: absolute; top: 60px; right: 40px;">
			</a>
		</div>
		{* <div class="columns large-2 small-2" style="width: auto; height: auto;">
		<div class="box h2 link" style="width: auto; height: auto;">
		<a href="https://ddb.tudelft.nl/" style="width: auto; height: auto;">
		<img class="float-left" width="120" src="{$CSR_PICS}/ddb.gif" class="border">
		</a>
		</div>
		</div> *}
		{* <div class="columns large-2 small-2">
		<div class="box h2 link">
		<a href="/lidworden">
		<img src="{$CSR_PICS}/owee/owee-thema.jpg" class="border">
		<h2 class="overlay">OWee pagina!</h2>
		</a>
		</div>
		</div> *}
	</div>
	<div class="push"></div>
</div>
<div class="rommel">{include file='csrdelft2/partials/_advertenties.tpl'}</div>