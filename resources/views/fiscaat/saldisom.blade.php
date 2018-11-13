<dl id="saldisom" class="row">
	<dt class="col-md-3">Voor moment</dt>
	<dd class="col-md-9">@php($saldisomform->view())</dd>
	<dt class="col-md-3">Iedereen in de database</dt>
	<dd class="col-md-9">{{ format_bedrag($saldisom) }}</dd>
	<dt class="col-md-3">Alleen leden en oudleden</dt>
	<dd class="col-md-9">{{ format_bedrag($saldisomleden) }}</dd>
</dl>

