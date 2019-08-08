<!DOCTYPE html>
<html lang="nl">
<head>
	@include('head')
</head>
<body>

<div class="container">
	<nav aria-label="breadcrumb">{!! csr_breadcrumbs(\CsrDelft\model\MenuModel::instance()->getBreadcrumbs($_SERVER['REQUEST_URI'])) !!}</nav>
	@php($content->view())
</div>
</body>
</html>
