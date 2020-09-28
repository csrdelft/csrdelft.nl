<!DOCTYPE html>
<html lang="nl">
<head>
	@include('head')
	@stylesheet('common.css')
	@stylesheet('bar.css')
	@script('bar.js')
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta property="X-BARSYSTEEM-CSRF" content="{{ $CsrfToken }}" />
	<title>Barsysteem C.S.R.</title>
</head>
<body>
<BarSysteem class="vue-context"></BarSysteem>
</body>


