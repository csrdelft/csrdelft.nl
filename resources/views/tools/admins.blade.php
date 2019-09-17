@extends('layout')

@section('titel', 'Admins')

@section('content')
<h1>Admins</h1>
<p>
	Op deze pagina vind je een overzicht met alle leden die meer rechten op de stek hebben dan leden. In de broncode
	van de stek is alles te vinden over welke rechten waar gebruikt worden. Zie hier voor <a
		href="https://github.com/csrdelft/csrdelft.nl">github.com/csrdelft/csrdelft.nl</a>.
</p>
<dl>
	<dt>R_BASF</dt>
	<dd>Mag het fotoalbum modereren, documenten modereren en de bieb modereren.</dd>
	<dt>R_FISCAAT</dt>
	<dd>Mag saldi van leden zien en producten aanmaken in het civisaldo systeem.</dd>
	<dt>R_MAALCIE</dt>
	<dd>Mag alles wat R_FISCAAT mag en maaltijden modereren.</dd>
	<dt>R_FORUM_MOD</dt>
	<dd>Mag het forum modereren.</dd>
	<dt>R_VLIEGER</dt>
	<dd>Mag alles wat R_MAALCIE mag en alles wat R_BASF mag.</dd>
	<dt>R_BESTUUR</dt>
	<dd>Mag alles wat R_MAALCIE mag, alles wat R_BASF mag en het forum modereren, de agenda modereren, de courant
		beheren, peilingen beheren en in forum belangrijk posten.
	</dd>
	<dt>R_PUBCIE</dt>
	<dd>Mag alles. Oftewel alles wat R_BESTUUR mag, forum delen maken, pagina's maken, menu beheren, eetplan beheren
		en de courant versturen.
	</dd>
</dl>
<table class="table">
	<thead>
	<tr>
		<th>UID</th>
		<th>Naam</th>
		<th>Rechten</th>
	</tr>
	</thead>
	<tbody>
	@foreach($accounts as $account)
		<tr>
			<td>{{$account->uid}}</td>
			<td>{!! $account->getProfiel()->getLink() !!}</td>
			<td>{{$account->perm_role}}</td>
		</tr>
	@endforeach
	</tbody>
</table>
@endsection
