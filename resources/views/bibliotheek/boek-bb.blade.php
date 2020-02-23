<?php
/**
 * @var $boek \CsrDelft\entity\bibliotheek\Boek
 */
?>
<a class="bb-block bb-boek" id="boek_bb-{{$boek->id}}" href="{{$boek->getUrl()}}"
	 title="Boek: {{$boek->titel}}">
	@icon("book")
	<span title="{{$boek->getStatus()}} boek" class="boekindicator {{$boek->getStatus()}}">•</span>
	<span class="titel">{{$boek->titel}}</span>
	<span class="auteur">{{$boek->auteur}}</span>
</a>
