@if($headers)
From: PubCie <pubcie@csrdelft.nl>
To: leden@csrdelft.nl
Organization: C.S.R. Delft
MIME-Version: 1.0
Content-Type: text/html; charset=utf-8
User-Agent: telnet localhost 25
X-Complaints-To: pubcie@csrdelft.nl
Approved: {{env('CSRMAIL_PASSWORD')}}
Subject: C.S.R.-courant {{strftime("%e %B %Y")}}
@endif
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
<head>
	<title>C.S.R.-courant</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<meta name="author" content="PubCie der C.S.R. Delft"/>
	<meta name="robots" content="index, follow"/>
	<style type="text/css">
		<!--
		body {
			font-face: verdana, arial, sans-serif;
			font-size: 12px;
			margin: 0 0 20px 0;
			padding: 0;
		}

		table {
			border: 0;
			margin: 0;
			padding: 0;
			width: 100%
		}

		td {
			vertical-align: top;
			font-size: 11px;
			font-face: verdana, arial, sans-serif;
		}

		.Zijbalk {
			width: 150px;
			margin: 0;
			padding: 0;
			vertical-align: top;

			background-repeat: repeat-y;
		}

		.hoofdKolom {
			margin: 0;
			padding: 23px 20px 0 0;
			vertical-align: top;
		}

		img {
			border: 0;
		}

		h4 {
			background-color: #CAD6FF;
			font-size: 15px;
			margin: 10px 0 0 0;
			padding: 5px 5px 5px 10px;
			color: black;
		}

		div.p {
			background-color: #FAFAFF;
			margin: 0 0 0 0;
			padding: 10px 5px 5px 10px;
			color: #020883;
			font-size: 11px;
			font-face: verdana, arial, sans-serif;
			line-height: 1.4em;
		}

		.inhoud {
			border: 0;
			width: 100%;
			background-color: #FAFAFF;
			margin: 0 0 15px 0;
			padding: 0;
		}

		.inhoudKolom {
			margin: 0 0 10px 0;
			padding: 5px 5px 5px 10px;
			font-size: 11px;
			vertical-align: top;
			width: 33%;
		}

		.inhoudKop {
			font-weight: bold;
			font-size: 11px;
		}

		ul {
			margin: 0 0 0 10px;
			padding: 0 0 0 5px;

		}

		li {
			margin: 0 0 0 00px;
			color: #020883;
			font-size: 11px;
		}

		div.citaatContainer {
			margin: 5px 5px 5px 20px;
		}

		-->
	</style>
</head>
<body>
<table>
	<tr>
		<td class="Zijbalk" valign="top">
			<img src="{{CSR_ROOT}}/plaetjes/courant/logo.jpg" width="150px" height="197px" alt="Logo van C.S.R."/>
			<img src="{{CSR_ROOT}}/plaetjes/courant/balk.gif" width="150px" height="100%"/>
		</td>
		<td class="hoofdKolom">
			<h4><font size="-3" face="verdana">Inhoud</font></h4>
			<table class="inhoud">
				<tr>
					@foreach($catNames as $categorie => $catName)
						@if($categorie != 'voorwoord' && $categorie != 'sponsor')
							<td class="inhoudKolom" valign="top">
								<font face="verdana" size="-1">
									<div class="inhoudKop"><b>{{$catName}}</b></div>
									<ul>
										@foreach($berichten as $bericht)
											@if($bericht->cat == $categorie)
												<li><a href="#{{$bericht->id}}"
															 style="text-decoration: none;">{!! bbcode($bericht->titel, "mail") !!}</a></li>
											@endif
										@endforeach
									</ul>
								</font>
							</td>
						@endif
					@endforeach
				</tr>
			</table>
			<font face="verdana" size="-1">
				@foreach($berichten as $bericht)
					<h4><a id="{{$bericht->id}}"></a>{!! bbcode($bericht->titel, "mail") !!}</h4>
					<div class="p">{!! bbcode($bericht->bericht, "mail") !!}</div>
				@endforeach
			</font>
		</td>
	</tr>
</table>
</body>
</html>
