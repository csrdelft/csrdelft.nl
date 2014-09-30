<nav id="cd-lateral-nav">

	<form name="lidzoeker" method="get" action="/communicatie/lijst.php">
		<input type="text" name="q" id="zoekveld" />
		<script type="text/javascript">
			$(document).ready(function () {
				$('#zoekveld').autocomplete({json_encode(array_keys($instantsearch))}, {
					clickFire: true,
					max: 20,
					matchContains: true,
					noRecord: ""
				});
				var instantsearch = {json_encode($instantsearch)};
				$('#zoekveld').click(function (event) {
					this.setSelectionRange(0, this.value.length);
				});
				$('#zoekveld').keyup(function (event) {
					if (event.keyCode === 13 && typeof instantsearch[this.value] !== 'undefined') {
						window.location.href = instantsearch[this.value];
					}
				});
				$(document).keydown(function (event) {
					// Geen instantsearch met modifiers
					if (bShiftPressed || bCtrlPressed || bAltPressed || bMetaPressed) {
						return;
					}
					// Geen instantsearch als we in een input-element of text-area zitten.
					var element = event.target.tagName.toUpperCase();
					if (element == 'INPUT' || element == 'TEXTAREA' || element == 'SELECT') {
						return;
					}
					if (event.keyCode > 64 && event.keyCode < 91) {
						$('#zoekveld').focus();
					}
				});
			});
		</script>
	</form>

	<ul class="cd-navigation">
		<li class="item-has-children">
			<a href="#0">Services</a>
			<ul class="sub-menu">
				<li><a href="#0">Brand</a></li>
				<li><a href="#0">Web Apps</a></li>
				<li><a href="#0">Mobile Apps</a></li>
			</ul>
		</li> <!-- item-has-children -->

		<li class="item-has-children">
			<a href="#0">Products</a>
			<ul class="sub-menu">
				<li><a href="#0">Product 1</a></li>
				<li><a href="#0">Product 2</a></li>
				<li><a href="#0">Product 3</a></li>
				<li><a href="#0">Product 4</a></li>
				<li><a href="#0">Product 5</a></li>
			</ul>
		</li> <!-- item-has-children -->

		<li class="item-has-children">
			<a href="#0">Stockists</a>
			<ul class="sub-menu">
				<li><a href="#0">London</a></li>
				<li><a href="#0">New York</a></li>
				<li><a href="#0">Milan</a></li>
				<li><a href="#0">Paris</a></li>
			</ul>
		</li> <!-- item-has-children -->
	</ul> <!-- cd-navigation -->

	<ul class="cd-navigation cd-single-item-wrapper">
		<li><a href="#0">Tour</a></li>
		<li><a href="#0">Login</a></li>
		<li><a href="#0">Register</a></li>
		<li><a href="#0">Pricing</a></li>
		<li><a href="#0">Support</a></li>
	</ul> <!-- cd-single-item-wrapper -->

	<ul class="cd-navigation cd-single-item-wrapper">
		<li><a href="/contact">Contact</a></li>
	</ul> <!-- cd-single-item-wrapper -->

	<!-- div class="cd-navigation socials">
		<a class="cd-twitter cd-img-replace" href="#0">Twitter</a>
		<a class="cd-github cd-img-replace" href="#0">Git Hub</a>
		<a class="cd-facebook cd-img-replace" href="#0">Facebook</a>
		<a class="cd-google cd-img-replace" href="#0">Google Plus</a>
	</div> <!-- socials -->
</nav>