<a class="bb-block bb-boek" id="boek_bb-{{$boek->getID()}}" href="{{$boek->getUrl()}}"
	 title="Boek: {{$boek->getTitel()}}">
	@icon("book")
	<span title="{{$boek->getStatus()}} boek" class="boekindicator {{$boek->getStatus()}}">•</span>
	<span class="titel">{{$boek->getTitel()}}</span>
	<span class="auteur">{{$boek->getAuteur()}}</span>
</a>
